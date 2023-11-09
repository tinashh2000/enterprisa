<?php

namespace Api\Scheduler;

use Api\AppDB;
use Api\CEnterprisa;
use Api\Mt;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Api\Forensic\CForensic;
use Api\CPrivilege;

class CSched {

    var $creationDate;
    var $lastModifiedDate;


    static $cache = array();

    static $orderByParams = array("name");
    static $selectItems = array("id", "name");
    static $dbItems = array("*");
    static $searchParams = ["name"];
    static $defaultCondition;

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "Schedules";

    function __construct($name, $privileges, $privilegesList, $description, $flags = 0)
    {
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("Schedules");
        self::$defaultPath = Mt::dataPath("Schedules");
        self::$moduleDescription = "Schedules";
        self::$className = "CSchedules";
        self::$defaultRecordName = "schedule";
        self::$permissionsBase = CEnterprisa::SCHEDULES_PERMISSIONSBASE;
        parent::_setupComplete();
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `name`          VARCHAR(64) NOT NULL UNIQUE,
                `privileges`    BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `module`        TEXT NOT NULL,
                `handler`       TEXT NOT NULL,
                flags           BIGINT UNSIGNED NOT NULL DEFAULT 0,
                description     TEXT NOT NULL); ";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::pushStatus("Schedules created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::pushError("Schedules creation failed");
    }

    function sqlPrepare() {
        $this->id = CDecimal::integer64($this->id);
        $this->flags = intval($this->flags);
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->description);
        $this->privileges = CDecimal::integer64($this->privileges);
        AppDB::ffwRealEscapeStringX($this->privilegesList);
        $this->username = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

    function exists() {
        $name = AppDB::ffwRealEscapeString($this->name);
        $id = AppDB::ffwRealEscapeString($this->id);

        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE name='$name' AND id != '$id' LIMIT 1")) {
            return AppDB::numRows($res) > 0;
        }
        return false;
    }

    function create() {
        $this->sqlPrepare();
        if (!CPrivilege::check(CPrivilege::SCHEDULE_ADMINISTRATOR)) return CStatus::jsonError("Access Denied");
        if ($this->exists()) {
            CStatus::jsonError("Schedule already exists");
        }

        $q = "INSERT INTO   " . self::$defaultTable . "  SET
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            privileges='$this->privileges',
            privilegesList='$this->privilegesList',
            description='$this->description',
            username='$this->username',
            flags='$this->flags'
        ";

        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            $this->id = AppDB::last_id($res);
            CForensic::log(self::$forensicPath, "Create Schedule", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Schedule created  successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Schedule creation failed");
    }

    function edit()
    {
        if (!CPrivilege::check(CPrivilege::SCHEDULE_ADMINISTRATOR)) return CStatus::jsonError("Access Denied");
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Schedule already exists!");
        }

        AppDB::beginTransaction();
        if ($res = AppDB::query("UPDATE " . self::$defaultTable . "  SET
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            privileges='$this->privileges',
            privilegesList='$this->privilegesList',
            description='$this->description',
            username='$this->username',
            flags='$this->flags' WHERE id='$this->id'")) {
            CForensic::log(self::$forensicPath, "Edit Supplier", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Schedule updated successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Schedule update failed");
    }

    static function search($query="", $start=0, $limit=25) {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name LIKE '%$query%' OR description LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }

    static function getValues($schedules) {
        $schedules =str_replace(";", ",", $schedules );
        AppDB::ffwRealEscapeStringX($schedules);
        $q = "SELECT GROUP_CONCAT(DISTINCT (privileges) SEPARATOR ',') as privileges, GROUP_CONCAT(privilegesList SEPARATOR ',') AS schedules FROM " . self::$defaultTable . " WHERE id IN ($schedules)";
        if ($res = AppDB::query($q)) {
            $item = AppDB::fetchAssoc($res);
            return $item;
        }
        return "";
    }

}

CSchedule::__setup();
