<?php
namespace Api\Messaging;

use Api\CAssistant;
use Api\CPerson;
use Api\Mt;
use Api\Session\CSession;
use Api\Users\CUser;
use Api\Users\CurrentUser;
use Ffw\Database\Sqlite\sqliteDB;
use Ffw\Status\CStatus;
use Api\AppDB;
use MongoDB\Driver\Exception\ExecutionTimeoutException;
use Ffw\Messaging\CEmail;

class CMessage {

    const ID_OFFSET = 219500;
    const FLAG_INBOUND  = 1 << 1;
    const FLAG_DRAFT    = 1 << 2;

    const FLAG_IMPORTANT   = 1 << 10;
    const FLAG_SOCIAL   = 1 << 11;
    const FLAG_PROMOTION   = 1 << 12;

    const FLAG_EMAIL    =    1 << 20;
    const FLAG_INTERNAL =    1 << 21;

    var $id;
    var $peers;
    var $message;
    var $subject;
    var $date;
    var $types;
    var $categories;
    var $flags;

    static $moduleDataPath;

    function __construct($subject, $message, $peers, $flags=0, $date=null) {
        $this->subject = $subject;
        $this->message = $message;
        $this->flags = $flags;
        $this->peers = $peers;
        $this->date = $date == null ? gmdate("Y-m-d H:i:s", time()) : gmdate("Y-m-d H:i:s", strtotime($date));
    }

    static function __setup() {
        self::$moduleDataPath = CAssistant::$moduleDataPath . '/Messages';
    }

    static function init() {
        if (!file_exists(self::$moduleDataPath)) {
            mkdir(self::$moduleDataPath, 0777, true);
        }
    }

    static function getStats() {
        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $dt = CAssistant::getLastView($uDB, "lastMessageView");
            $q = "SELECT COUNT(*) AS numMessages, COUNT(DISTINCT peer) AS numPeers, MAX(`date`) AS latestDate FROM inbox WHERE (flags & '" . CAssistant::FLAG_UNREAD . "') > 0 AND `date` > '$dt'";
            if ($res = $uDB->query($q)) {
                $item = $res->fetchArray(SQLITE3_ASSOC);
                return $item;
            }
        }
        return false;
    }

    static function initUser($sqlite) {
        $sqlite->exec('CREATE TABLE IF NOT EXISTS inbox (id INTEGER PRIMARY KEY ASC, subject TEXT, message TEXT, meta TEXT, peer VARCHAR(64), flags INTEGER, `date` TEXT )');
        $sqlite->exec('CREATE TABLE IF NOT EXISTS sentbox (id INTEGER PRIMARY KEY ASC, subject TEXT, message TEXT, meta TEXT, peer VARCHAR(64), flags INTEGER, `date` TEXT )');
        $dt = gmdate("Y-m-d H:i:s", strtotime("now"));
        $sqlite->exec("INSERT INTO inbox (subject, message, peer, flags, `date`) VALUES ('Welcome', 'Welcome', 'System', " . CAssistant::FLAG_UNREAD . ", '$dt')");
        $sqlite->exec("INSERT INTO stats(description, stat, flags, `date`) VALUES ('lastMessageTime', '', 0, '$dt')");
        $sqlite->exec("INSERT INTO settings(description, data, flags, `date`) VALUES ('emailSettings', '', 0, '$dt')");
    }

    function send() {
        return CMessage::sendMessageX(CurrentUser::getUsername()); //, $this->peers, $this->subject, $this->message);
    }

    function sendEmailByEntity($entity, $email) {

    }

    function sendInternal($recipient) {
        $receiverDb = CAssistant::getFilename($recipient, true);
        if ($receiverDb == null && !file_exists($receiverDb)) {
            return false;
        }

        try {
            $rDB = new sqliteDB($receiverDb);
            if ($rDB->query("INSERT INTO inbox (subject, message, peer, flags, `date`) VALUES ('$this->subject', '$this->message', '$this->from', " . CAssistant::FLAG_UNREAD . ", '$this->date')")) {
                $rDB->close();
//                echo $receiverDb . "<br>";
                return true;
            }
        } catch (Exception $e) {
        }

        $rDB->close();
        return false;
    }

    function sendMessageX($from) {
        $unsent = "";
        $senderDb = CAssistant::getFilename($from, true);
        $numRecipients = 0;
        if ($senderDb == null) {
            return CStatus::jsonError("Invalid sender");
        }

        if (!file_exists($senderDb)) {
            CStatus::jsonError("User entry exception. Contact your administrator");
            return false;
        }

        CStatus::pushSettings(0);
        $recipients = explode(",", $this->peers);
        $this->peers = implode(",", $recipients);
        $subject = $this->subject;
        $message = $this->message;
        $dt = gmdate("Y-m-d H:i:s", strtotime("now"));
        $emailQueue = array();
        $sDB = new sqliteDB($senderDb);
        foreach ($recipients as $recipient) {
            if ($recipient == "") continue;
            $rDB = null;
            try {
                $isEmail = filter_var($recipient, FILTER_VALIDATE_EMAIL);
                if ($this->flags & self::FLAG_EMAIL || $isEmail) {    //If we should also send an email or if recipient was an email address
                    if ($user = CUser::get($recipient)) {
                        if (array_search( $user['email'], $emailQueue)  === FALSE) {
                            array_push($emailQueue, $user['email']);
                            $recipient = $user['username'];
                        }
                    } else {
                        if ($this->flags & self::FLAG_EMAIL && $isEmail && array_search( $user['email'], $emailQueue)  === FALSE) //No user profile, and  we should send email and email not in queue
                            array_push($emailQueue, $recipient);
                        continue;
                    }
                }
                if (($this->flags & self::FLAG_INTERNAL) == 0) continue;
                if (!self::sendInternal($recipient))
                    $unsent .= ",$recipient";
                else
                    $numRecipients++;

            } catch (Exception $e) {
                $unsent .= ",$recipient";
            }
        }
        try {
            $sDB->query("INSERT INTO sentbox (subject, message, peer, flags, `date`) VALUES ('$subject', '$message', '{$this->peers}', 0, '$dt');");
        } catch (Exception $e) {

        }

        if ($this->categories != "" || $this->types != "") {
            if ($res = CPerson::getByEntities($this->categories, $this->types, "id, name, email")) {
                while ($item = AppDB::fetchAssoc($res)) {
                    if (array_search($item['email'], $emailQueue) !== FALSE) continue;
                    if ($this->flags & self::FLAG_EMAIL) {
                        array_push($emailQueue, $item['email']);
                    }

                    if ($this->flags & self::FLAG_INTERNAL && ($user = CUser::getByPersonId($item['id']))) {
                        if ($this->sendInternal($user['username']))
                            $numRecipients++;
                        else
                            $unsent .= ",$recipient";

                        $this->peers .= $user['username'] .",";
                    }
                }
            }
        }

        if ($this->flags & self::FLAG_EMAIL) {
            $emailError = "";
            if ($fromUser = CUser::get($from)) {
                require_once(Mt::$appDir . "/Api/Ffw/Messaging/CEmail.php");
                $emailsSent = CEmail::sendEmail($fromUser, $emailQueue, $subject, $message);
            }
            else
                $emailError = "Emails sending failed";
        }
        $sDB->close();
        CStatus::popSettings();

        if ($unsent != "") {
            return CStatus::jsonSuccess("{$numRecipients} message(s) sent. Some messages were not sent.");
        }
        return CStatus::jsonSuccess("{$numRecipients} message(s) sent");
    }

    static function fetch($start=0, $limit=25, $box = 'inbox') {

        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $cbox = $box =='inbox' ? "inbox" : "sentbox";
            $msgCount = "";
            if ($res = $uDB->query("SELECT COUNT(*) as numMessages, COUNT(DISTINCT peer) as numPeers FROM $cbox")) {
                if ($item = $res->fetchArray(SQLITE3_NUM)) {
                    $msgCount = '"count" : "'.$item[0].'", "peers" : "'.$item[1].'",';
                }
            }

            $cm = "";
            echo '{"status":"OK", '.$msgCount.' "messages":[';
            if ($res = $uDB->query("SELECT * FROM $cbox ORDER BY `date` DESC LIMIT $start, $limit")) {
                while ($item = $uDB->fetchAssoc($res)) {
                    echo $cm . json_encode($item);
                    $cm=",";
                }
            }
            echo "]}";
            return true;
        } else {
            echo '{"status" : "OK", "messages":[]}';
        }

        return false;
    }

    static function getUserMessages($peerUser, $start=0, $limit=20) {
        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $msgCount = "";
            if ($res = $uDB->query(
                "SELECT id, 'in' AS box, subject, message, peer, flags, `date` FROM inbox WHERE peer='$peerUser'" .
                    "UNION " .
                    "SELECT  id, 'sent' AS box, subject, message, peer, flags, `date` FROM sentbox WHERE peer='$peerUser' ORDER BY `date` DESC LIMIT $start, $limit")) {
                echo '{"status":"OK", "messages":[';
                $cm = "";
                while ($item = $res->fetchArray(SQLITE3_ASSOC)) {
                    echo $cm;
                    echo json_encode($item);
                    $cm=",";
                }
                echo "]}";
            }
        }
    }

    static function fetchUni($start=0, $limit=25, $headers = true, $after='1980-01-01 00:00:00') {
        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);
        $dt = gmdate("Y-m-d H:i:s", strtotime("now"));

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $msgCount = "";

            if ($res = $uDB->query("SELECT COUNT(*) as numMessages, COUNT(DISTINCT peer) as numPeers FROM inbox WHERE (flags & '".CAssistant::FLAG_UNREAD."') > 0")) {
                if ($item = $res->fetchArray(SQLITE3_NUM)) {
                    $msgCount = '"count" : "'.$item[0].'", "peers" : "'.$item[1].'",';
                }
            }

            $msgK = ($headers) ? "substr(message,1,64) as message" : "message";

            if ($res = $uDB->query("
                    SELECT * FROM (
                        SELECT  id, 'in' AS box, subject, $msgK, peer, flags, `date` FROM inbox UNION 
                        SELECT  id, 'sent' AS box, subject, $msgK, peer, flags, `date` FROM sentbox ORDER BY `date` DESC) WHERE `date` > '$after'
                    GROUP BY `peer` ORDER BY `date` DESC LIMIT $start, $limit")) {

                CAssistant::setLastView($uDB, "lastMessageView", $dt);

                if ((CStatus::get() & CStatus::FLAG_JSON) == 0) {
                    return array($uDB, $res, $item);
                }

                echo '{"status":"OK", '.$msgCount.' "messages":[';
                $cm = "";
                while ($item = $res->fetchArray(SQLITE3_ASSOC)) {
                    echo $cm;
                    echo json_encode($item);
                    $cm=",";
                }
                echo "]}";
            }
            return true;
        } else {
            CStatus::jsonError("User entry exception. Contact your administrator");
        }
        return false;
    }



    static function fetchHeaders($start=0, $limit=25, $box = 'inbox', $after='1980-01-01 00:00:00') {
        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $cbox = $box =='inbox' ? "inbox" : "sentbox";
            $peer = $box == 'inbox' ? "peer" : "peer";
            $msgCount = "";

            if ($res = $uDB->query("SELECT subject, message, $peer, flags, `date` FROM $cbox WHERE `date` > '$after' ORDER BY `date` LIMIT $start, $limit")) {

                if ((CStatus::get() & CStatus::FLAG_JSON) == 0) {
                    return array($uDB, $res);
                }

                if ($res = $uDB->query("SELECT COUNT(*) as numMessages, COUNT(DISTINCT $peer) as numPeers FROM $cbox")) {
                    if ($item = $res->fetchArray(SQLITE3_NUM)) {
                        $msgCount = '"count" : "'.$item[0].'", "peers" : "'.$item[1].'",';
                    }
                }

                echo '{"status":"OK", '.$msgCount.' "messages":[';
                $cm = "";
                while ($item = $res->fetchArray()) {
                    echo $cm;
                    $item["msg"] = substr($item["msg"], 0, 64);
                    echo json_encode($item);
                    $cm=",";
                }
                echo "]}";
            }
            return true;
        } else {
            CStatus::jsonError("User entry exception. Contact your administrator");
        }
        return false;
    }

    static function count($box) {
        AppDB::ffwRealEscapeStringX($id);
        AppDB::ffwRealEscapeStringX($box);
        $user = CurrentUser::getUsername();
        $userDB = CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $cbox = $box =='inbox' ? "inbox" : "sentbox";
            $peer = $box =='inbox' ? "peer" : "peer";

            if ($res = $uDB->query("SELECT COUNT(*) as numMessages, COUNT(DISTINCT $peer) as numPeers FROM $cbox")) {
                if ($item = $uDB->fetchAssoc($res)) {
                }
            }
        }
    }

    static function unReadCount($box) {
        AppDB::ffwRealEscapeStringX($id);
        AppDB::ffwRealEscapeStringX($box);
        $user = CurrentUser::getUsername();
        $userDB = CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $cbox = $box =='inbox' ? "inbox" : "sentbox";
            $peer = $box =='inbox' ? "peer" : "peer";

            if ($res = $uDB->query("SELECT COUNT(*) as numMessages, COUNT(DISTINCT $peer) as numPeers FROM $cbox WHERE (flags & " . CAssistant::FLAG_UNREAD . ") > 0" )) {
                if ($item = $uDB->fetchArray($res)) {
                    echo '{"status":"OK", "unreadMessages":"' + $item[0] + '", "unreadPeers": "' + $item[0] + '" }';
                }
            }
        }
    }

    static function unReadPeers($box) {
        AppDB::ffwRealEscapeStringX($id);
        AppDB::ffwRealEscapeStringX($box);
        $user = CurrentUser::getUsername();
        $userDB = CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $cbox = $box =='inbox' ? "inbox" : "sentbox";
            $peer = $box =='inbox' ? "peer" : "peer";

            if ($res = $uDB->query("SELECT COUNT(DISTINCT $peer) as numPeers FROM $cbox WHERE (flags & " . CAssistant::FLAG_UNREAD . ") > 0" )) {
                if ($item = $uDB->fetchArray($res)) {
                    return $item[0];
                }
            }
        }
        return 0;
    }

    static function unReadMessages($box) {
        AppDB::ffwRealEscapeStringX($id);
        AppDB::ffwRealEscapeStringX($box);
        $user = CurrentUser::getUsername();
        $userDB = CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $cbox = $box =='inbox' ? "inbox" : "sentbox";
            $peer = $box =='inbox' ? "peer" : "peer";

            if ($res = $uDB->query("SELECT COUNT(DISTINCT $peer) as numPeers FROM $cbox WHERE (flags & " . CAssistant::FLAG_UNREAD . ") > 0" )) {
                if ($item = $uDB->fetchArray($res)) {
                    return $item[0];
                }
            }
        }
        return 0;
    }

    static function get($id, $box) {

        AppDB::ffwRealEscapeStringX($id);
        AppDB::ffwRealEscapeStringX($box);
        $user = CurrentUser::getUsername();
        $userDB = CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $cbox = $box =='inbox' ? "inbox" : "sentbox";
            if ($res = $uDB->query("SELECT * FROM $cbox WHERE id='$id' LIMIT 1")) {
                if ($item = $uDB->fetchAssoc($res)) {
                    $output = '{"status":"OK", "message":';
                    $peer = $item["peer"];

                    if (($item['flags'] & CAssistant::FLAG_UNREAD) > 0) {
                        $uDB->query("UPDATE $cbox SET flags = (flags & ~" . CAssistant::FLAG_UNREAD . ") WHERE id='$id'");
                    }

                    CStatus::pushSettings(0);
                    $userProfile = CUser::get($peer);
                    CStatus::popSettings();
                    $item["fullName"] = $userProfile != null && isset($userProfile["name"]) ? $userProfile["name"] : "";
                    $output .= json_encode($item);
                    $output .= "}";
                    echo $output;
                    return true;
                }
            }
        }
//        echo $user;
//        echo $userDB;

        echo '{"status" : "OK", "message":[]}';
        return false;
    }
}

CMessage::__setup();