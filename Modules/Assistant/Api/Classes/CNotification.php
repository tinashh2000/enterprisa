<?php

namespace Api\Assistant;

use Api\CAssistant;
use Api\Messaging\CMessage;
use Api\Users\CurrentUser;
use Ffw\Database\Sqlite\sqliteDB;
use Ffw\Status\CStatus;
use Api\Users\CUser;
use Api\Session\CSession;
use Api\AppDB;
class CNotification
{
    const ID_OFFSET = 219500;

    static $moduleDataPath;

    var $date;
    var $msg;
    var $sender;
    var $flags;

    function __construct($date, $msg, $sender, $flags)
    {
        $this->date = $date;
        $this->msg = $msg;
        $this->sender = $sender;
        $this->flags = $flags;
    }

    static function __setup()
    {
        self::$moduleDataPath = CAssistant::$moduleDataPath . '/Messages';
    }

    static function init()
    {
        if (!file_exists(self::$moduleDataPath)) {
            mkdir(self::$moduleDataPath, 0777, true);
        }
    }

    static function getStats() {
        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);

            $dt = CAssistant::getLastView($uDB, "lastNotificationView");

            $q = "SELECT COUNT(*) AS count, MAX(`date`) AS latestDate FROM notifications WHERE `date` > '$dt'";
            if ($res = $uDB->query($q)) {
                $item = $res->fetchArray(SQLITE3_ASSOC);
                return $item;
            }
        }
        return false;
    }

    static function initUser($sqlite)
    {
        $sqlite->exec('CREATE TABLE IF NOT EXISTS notifications (id INTEGER PRIMARY KEY ASC, subject TEXT, context TEXT, message TEXT, sender VARCHAR(64), link TEXT, flags INTEGER, `date` TEXT )');
        $dt = gmdate("Y-m-d H:i:s", strtotime("now"));
        $sqlite->exec("INSERT INTO notifications (context, subject, message, sender, link, flags, `date`) VALUES ('System', 'Welcome', 'Welcome to Enterprisa Cloud', 'Administrator', 'Home', " . CAssistant::FLAG_UNREAD . ", '$dt')");
        $sqlite->exec("INSERT INTO stats(description, stat, flags, `date`) VALUES ('lastNotificationView', '{\"numNotifications\": 0}', 0, '1900-01-01 00:00:00')");
    }

    static function toUserWithPermission($permission, $subject, $message, $from = "", $link = "", $except=null, $context='System') {
        if ($userList = CUser::getUsernamesWithPermission($permission)) {

            if (is_string($except))
                $except = explode(",", $except);

            if ($except != null) {
                $ul = explode(",", $userList);
                foreach ($except as $e) {
                    $u = array_search($e, $ul);
                    if ($u !== FALSE)
                        unset($ul[$u]);
                }
                $userList = implode(",",$ul);
            }

            return self::sendNotificationX($userList, $subject, $message, $from, $link, $context);
        }
        echo "Not found";
        return false;
    }

    static function sendNotificationX($recipients, $subject, $message, $from = "", $link = "", $context = 'System')
    {
        $recipientx = explode(",", $recipients);
        foreach ($recipientx as $to) {
            $userDB = CAssistant::getFilename($to, true);
            if (file_exists($userDB)) {
                $uDB = new sqliteDB($userDB);
                $dt = gmdate("Y-m-d H:i:s", strtotime("now"));

                sqliteDB::escapeStringX($context);
                sqliteDB::escapeStringX($message);
                sqliteDB::escapeStringX($subject);
                sqliteDB::escapeStringX($from);
                sqliteDB::escapeStringX($link);

                $uDB->query("INSERT INTO notifications (context, subject, message, sender, link, flags, `date`) VALUES (\"$context\", \"$subject\", '$message', \"$from\", \"$link\", " . CAssistant::FLAG_UNREAD . ", '$dt');");

            }
        }
        return true;
    }

    static function fetch($start = 0, $limit = 7, $after="1980-01-01 00:00:00", $context = null)
    {
        $dt = gmdate("Y-m-d H:i:s", strtotime("now"));
        $userDB = CAssistant::getFilename(CurrentUser::getUsername(), true);
        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);

            $cond = "`date` > '$after' ";
            if ($context != null)
                $cond .= " AND context='$context'";

            $cond .= " ORDER BY `date` DESC";
            if ($res = $uDB->query("SELECT * FROM notifications  WHERE $cond LIMIT $start, $limit")) {

                if ((CStatus::get() & CStatus::FLAG_JSON) == 0) {
                    $uDB->close();
                    return array($uDB, $res);
                }

                echo '{"status":"OK", "notifications":[';
                $cm = "";
                while ($item = $uDB->fetchAssoc($res)) {
                    echo $cm . json_encode($item);
                    $cm = ",";
                }
                echo "]}";
                CAssistant::setLastView($uDB, "lastNotificationView", $dt);
            }
            $uDB->close();
            return true;
        } else {
            CStatus::jsonError("User entry exception. Contact your administrator");
        }
        return false;
    }

    static function unReadNotifications()
    {
        $user = CurrentUser::getUsername();
        $userDB = CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            if ($res = $uDB->query("SELECT COUNT() as unreadNotifications FROM notifications WHERE (flags & " . CAssistant::FLAG_UNREAD . ") > 0")) {
                if ($item = $uDB->fetchArray($res)) {
                    return $item[0];
                }
            }
        }
        return 0;
    }

    static function get($id)
    {
        $user = getCurrentEmail();
        $userDB = Mt::$dbRootPath . "/{$user}.db";
        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            echo '{"status":"OK", "notification":[';
            if ($res = $uDB->query("SELECT * FROM notifications WHERE id='$id' LIMIT 1")) {
                $cm = "";
                while ($item = $uDB->fetchAssoc($res)) {
                    echo $cm . json_encode($item);
                    $cm = ",";
                }
            }
            echo "]}";
            return true;
        } else {
            CStatus::jsonError("User entry exception. Contact your administrator");
        }

        return false;
    }

}

CNotification::__setup();