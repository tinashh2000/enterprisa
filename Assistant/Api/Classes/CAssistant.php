<?php

namespace Api;

use Api\AppConfig;
use Api\Mt;
use Api\Users\CurrentUser;
use Api\Users\CUser;
use Accounta\Assets\CAsset;
use Accounta\Audit\CAudit;
use Accounta\Banks\CBank;
use Accounta\Accounts\CAccount;
use Accounta\Budget\CBudget;
use Ffw\Crypt\CCrypt9;
use Ffw\Database\Sqlite\sqliteDB;
use Modules\CModule;
use Api\Messaging\CMessage;
use Api\Assistant\CTodo;
use Api\Assistant\CNotification;
use Ffw\Status\CStatus;
use Api\CPerson;
use Api\Session\CSession;
use Api\CEvent;
use Api\CTask;
use const Assistant\modulePrivilegesBase;

require_once ("Assistant.php");

class CAssistant {

    const EVENTS_PERMISSIONSBASE = modulePrivilegesBase;
    const EVENTS_ALL = self::EVENTS_PERMISSIONSBASE + CPrivilege::ALL;
    const EVENTS_READ = self::EVENTS_PERMISSIONSBASE + CPrivilege::READ;
    const EVENTS_WRITE = self::EVENTS_PERMISSIONSBASE + CPrivilege::WRITE;
    const EVENTS_CREATE = self::EVENTS_PERMISSIONSBASE + CPrivilege::CREATE;
    const EVENTS_DELETE = self::EVENTS_PERMISSIONSBASE + CPrivilege::DELETE;
    const EVENTS_BASIC = self::EVENTS_READ;


    const FLAG_UNREAD  = 1 << 31;

    public static $moduleName;
    public static $modulePrefix;
    public static $moduleDataPath;

    static function setEmailSettings($settings) {
        $settingsJson = CCrypt9::scrambleText(json_encode($settings));

        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $dt = gmdate("Y-m-d H:i:s", strtotime("now"));
            $uDB->query("UPDATE settings SET description = 'emailSettings', data = '$settingsJson', flags=0, `date`='$dt' WHERE id IN (SELECT id FROM settings WHERE description='emailSettings' LIMIT 1)");
        }
    }

    static function getEmailSettings() {

        $user = CurrentUser::getUsername();
        $userDB =  CAssistant::getFilename($user, true);

        if (file_exists($userDB)) {
            try {
                $uDB = new sqliteDB($userDB);
                $dt = gmdate("Y-m-d H:i:s", strtotime("now"));
                if ($res = $uDB->query("SELECT * FROM settings WHERE description='emailSettings' LIMIT 1")) {
                    return $res->fetchArray(SQLITE3_ASSOC);
                }
                return false;
            }  catch(Exception $e) {
                return false;
            }
        }
    }

    static function getLastView($uDB, $w) {
        if (!CSession::get($w)) {
            $res = $uDB->query("SELECT * FROM stats WHERE description='$w' LIMIT 1");
            $item = $res->fetchArray();
            CSession::set("$w", $item['date']);
        }

        return CSession::get("$w");
    }

    static function setLastView($uDB, $w, $dt) {
        $uDB->query("UPDATE stats SET `date` = '$dt' WHERE id IN (SELECT id FROM stats WHERE description='$w' LIMIT 1)");
        CSession::set("$w", $dt);
    }

    static function initUserX($filename) {
        $sqlDB = new sqliteDB($filename);
        self::initUser($sqlDB);
        $sqlDB->close();
        return;
    }

    static function initUser($sqlite) {
        $sqlite->exec('CREATE TABLE IF NOT EXISTS stats (id INTEGER PRIMARY KEY ASC, description TEXT, stat TEXT, flags INTEGER, `date` TEXT )');
        $sqlite->exec('CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY ASC, description TEXT, data TEXT, flags INTEGER, `date` TEXT )');
        CMessage::initUser($sqlite);
        CTodo::initUser($sqlite);
        CNotification::initUser($sqlite);
    }

    static function getFilename($username, $init=false) {
        $fil =  self::$moduleDataPath . "/{$username}.db";
        if ($fil != null && !file_exists($fil)) {
            CStatus::pushSettings(0);
            if (!$us = CUser::get($username)) {
                CStatus::popSettings();
                return null;
            }
            self::initUserX($fil);
            CStatus::popSettings();
        }
        return $fil;
    }

    static function moduleDataPath($module) {
        return self::$moduleDataPath . "/$module";
    }

    static function tableName($module) {
        return self::$modulePrefix . "$module";
    }

    static function __setup() {
        self::$moduleName = "Assistant";
        self::$modulePrefix = Mt::tableName("Assistant_");
        self::$moduleDataPath = Mt::dataPath("Assistant");
    }

    static function getStats() {
        $notCount = CNotification::getStats();
        $msgCount = CMessage::getStats();
        echo '{"status":"OK", 
            "latestMessageDate":"' . $msgCount['latestDate'] . '", 
            "numMessages":"' . $msgCount['numPeers'] . '",  
            "latestNotificationsDate":"' . $notCount['latestDate'] . '", 
            "numNotifications":"' . $notCount['count'] . '"}';
    }

    static function getUpdates() {
        $lastMessages = CSession::get("lastMessagesUpdate");
        $lastNotifications = CSession::get("lastNotificationsUpdate");

        $open = false;

        $m = CMessage::fetchHeadersUni(0, 10, 'inbox', $lastMessages);
        $sql = $m[0];
        $res = $m[1];
        $lnk = $m[2];
        $cm = "";
        $numMessages = 0; //count($res);
        if ($item = $sql->fetchAssoc($res)) {
            $open = true;
            echo '{"status":"OK", "count":"'. "{$lnk[0]},{$lnk[1]}" .'", "messages":[';

            do {
                echo $cm;
                $item["message"] = substr($item["message"], 0, 64);
                echo json_encode($item);
                $cm = ",";
                $numMessages++;
            } while ($item = $sql->fetchAssoc($res));
            echo '],"numMessages":"' . $numMessages . '"';
        }

        $m = CNotification::fetch(0,10, $lastNotifications);
        $sql = $m[0];
        $res = $m[1];
        $cm = "";
        $numNotifications = 0; //$sql->numRows($res);

        if ($item = $sql->fetchAssoc($res)) {
            if (!$open)
                echo '{"status":"OK"';

            $open = true;
            echo ',"notifications":[';

            do {
                echo $cm;
                echo json_encode($item);
                $cm = ",";
                $numNotifications++;
            } while ($item = $sql->fetchAssoc($res));

            echo '],"numNotifications":"' . $numNotifications . '"';
        }

        if ($open)
            echo "}";
//        echo $numMessages . ", " . $numNotifications;
        CSession::set("lastMessagesUpdate", gmdate("Y-m-d H:i:s", strtotime("now")));
        CSession::set("lastNotificationsUpdate", gmdate("Y-m-d H:i:s", strtotime("now")));
    }

    static function init($reset=false) {
        if (!file_exists(self::$moduleDataPath)) {
            mkdir(self::$moduleDataPath, 0777, true);
        }

		CMessage::init($reset);
		CNotification::init($reset);
		CEvent::init($reset);
		CTask::init($reset);
//        CPerson::init($reset);
    }
}

CAssistant::__setup();
