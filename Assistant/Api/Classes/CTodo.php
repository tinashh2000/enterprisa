<?php
namespace Api\Assistant;

use Api\CAssistant;
use Api\Mt;
use Api\Users\CUser;
use Api\Users\CurrentUser;
use Ffw\Database\Sqlite\sqliteDB;

class CTodo {

    const STATUS_PENDING = 1000;
    const STATUS_PLANNING = 1001;
    const STATUS_PREPARING = 1002;
    const STATUS_INPROGRESS = 1003;
    const STATUS_TESTING = 1004;
    const STATUS_FINALIZING = 1005;
    const STATUS_COMPLETED = 1006;
    const STATUS_HALTED = 1007;
    const STATUS_ABANDONED = 1008;
    const STATUS_DISCONTINUED = 1009;

    const STATUS_LOWEST = 1000;
    const STATUS_HIGHEST = 1009;

    var $creationDate;
    var $description;
    var $endDate;
    var $flags;
    var $id;
    var $lastUpdateDate;
    var $module;
    var $name;
    var $position;
    var $progress;
    var $startDate;
    var $creator;

    static $moduleDataPath;

    function __construct($name, $description, $progress, $startDate, $endDate, $module="") {
        $this->name = $name;
        $this->description = $description;
        $this->progress = $progress;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->module = $module;
        $this->progress = 0;
        $this->flags = 0;
        $this->position=PHP_INT_MAX;
        $this->creationDate = $this->lastUpdateDate = gmdate("Y-m-d H:i:s", time());
    }

    static function __setup() {
        self::$moduleDataPath = CAssistant::$moduleDataPath . '/Todo';
    }

    static function init() {
        if (!file_exists(self::$moduleDataPath)) {
            mkdir(self::$moduleDataPath, 0777, true);
        }
    }

    static function initUser($sqlite) {
        $sqlite->exec('CREATE TABLE IF NOT EXISTS todo (id INTEGER PRIMARY KEY ASC, name TEXT, module TEXT, description TEXT, position INTEGER, flags INTEGER, deadLine TEXT, dateTime TEXT )');
    }

    static function fetch($start=0, $limit=25, $module = "") {

        $user = CurrentUser::getEmail();

        $userDB =  self::getFilename($user, true);
        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            echo '{"status":"OK", "todo":[';
            if ($res = $uDB->query("SELECT * FROM todo " . ($module != "" ?" WHERE module='$module' " : "") . " ORDER BY dateTime DESC LIMIT $start, $limit")) {
                $cm = "";
                while ($item = $uDB->fetchAssoc($res)) {
                    echo $cm . json_encode($item);
                    $cm=",";
                }
            }
            echo "]}";
            return true;
        } else {
            echo '{"status" : "OK", "todo":[]}';
        }

        return false;
    }

    static function get($id, $module="") {
        $user = CurrentUser::getEmail();
        $userDB =  self::getFilename($user, true);

        if (file_exists($userDB)) {
            $uDB = new sqliteDB($userDB);
            $output = '{"status":"OK", "message":';
            if ($res = $uDB->query("SELECT * FROM todo WHERE id='$id' LIMIT 1")) {
                if ($item = $uDB->fetchAssoc($res)) {
                    $peer = isset($item["sender"]) ? $item["sender"] : $item["recipient"];
                    $userProfile = CUser::get($peer);
                    $item["fullName"] = $userProfile != null && isset($userProfile["name"]) ? $userProfile["name"] : "";
                    $output .= json_encode($item);
                }
            }
            $output .= "}";
            echo $output;
            return true;
        } else {
            echo '{"status" : "OK", "message":[]}';
        }
        return false;
    }
}

CTodo::__setup();