<?php
namespace Api;

use Api\CPrivilege;
use Api\AppConfig;
use Api\AppDB;
use Api\SqlBuilder;
use Api\Mt;
use Api\Users\CurrentUser;
use Api\Session\CSession;
use Ffw\Database\Sqlite\SqliteDB;
use Ffw\Crypt\CCrypt8;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Currencies\CCurrency;
use \Exception;
use Api\ServiceClass;
use Api\Forensic\CForensic;
use Crm\Sales\CSale;
use Api\CAssistant;
abstract class TaskClass extends ServiceClass {

    const _STATUS_PENDING = 1;
    const _STATUS_STARTED = 2;
    const _STATUS_IN_PROGRESS = 3;
    const _STATUS_FINALIZING = 4;
    const _STATUS_COMPLETED = 5;
    const _STATUS_SUSPENDED = 6;
    const _STATUS_ABORTED = 7;

    const _STATUS_LOWEST = self::_STATUS_PENDING;
    const _STATUS_HIGHEST = self::_STATUS_ABORTED;
    const _STATUS_INACTIVE = self::_STATUS_COMPLETED;

    const statusValues = [0, 15, 25, 75, 100];

    const _PRIORITY_VERY_LOW = 1;
    const _PRIORITY_LOW = 2;
    const _PRIORITY_NORMAL = 4;
    const _PRIORITY_MEDIUM = 5;
    const _PRIORITY_HIGH = 6;
    const _PRIORITY_VERY_HIGH = 7;

    const _F_FLAG_ACTIVE = 1 << 0;

    const _F_FLAG_ALARM_ON_DATE_START = 1 << 10;
    const _F_FLAG_ALARM_ON_DATE_END = 1 << 11;

    protected $module; // = new CModule();
    var $id;
    var $creationDate;
    var $lastUpdateDate;
    var $entityId;
    var $name;
    var $participants;
    var $description;
    var $notes;
    var $startDate;
    var $endDate;
    var $status;
    var $priority;
    var $position;
    var $creator;
    var $flags;

    var $entityClass = null;

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
//    static $joinElements = array('%1.entityId=%2.id');
//    static $joinItems = "%1.*";
//    static $joinDBs = null; //Initialized in __setup

    public static $permissionsBase;
//    protected static $forensicPath = "Tasks";

    static function __setup() {
//        self::$defaultTable = CAssistant::tableName("Tasks");
//        self::$defaultPath = CAssistant::moduleDataPath("Tasks");
//        self::$moduleDescription = "Assistant\Task";
//        self::$className = "CTask";
//        self::$defaultRecordName = "task";
        self::$permissionsBase = 0;
        $currentUser = CurrentUser::getUsername();
        self::$defaultCondition = "creator='{$currentUser}' OR FIND_IN_SET('{$currentUser}', participants) > 0";
        parent::_setupComplete();
    }

    function __construct($name, $status = self::_STATUS_PENDING, $priority= self::_PRIORITY_NORMAL) {
        $this->name = $name;
        $this->status = $status;
        $this->priority = $priority;
        $this->creator = CurrentUser::getUsername();
        $this->creationDate = gmdate("Y-m-d H:i:s", strtotime("now"));
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);
        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . " (
                `id`            BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                `creationDate`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `lastUpdateDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `startDate`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `endDate`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()," .
            (isset(self::$entityName) && self::$entityName != "" ?
                (self::$entityName . "Id          BIGINT UNSIGNED,
                FOREIGN KEY(" . self::$entityName . "Id) REFERENCES " . self::$joinDBs[1] . "(id),") : "") .
                "`name`          VARCHAR(200) NOT NULL,
                `participants`  MEDIUMTEXT NOT NULL,
                `status`        INT UNSIGNED NOT NULL,
                `priority`      INT UNSIGNED NOT NULL,
                `position`       BIGINT UNSIGNED NOT NULL,
                `creator`       VARCHAR(64) NOT NULL,
                `flags`         BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `notes`         MEDIUMTEXT NOT NULL,
                `description`   MEDIUMTEXT NOT NULL);";
                
        try {
            if (!file_exists(self::$defaultPath))
                mkdir(self::$defaultPath, 0777, true);

            if (AppDB::query($q)) {
                CForensic::initModule(static::$forensicPath);
                return CStatus::pushStatus("Tasks records creation successfully");
            }
        } catch (\Exception $e) {
            return CStatus::pushError("Tasks creation failed");
        }
    }

    function exists() {
        $id = AppDB::ffwRealEscapeString($this->id);
        $entityId = CDecimal::integer($this->entityId);
        $name = AppDB::ffwRealEscapeString($this->name);
        if (isset($this->entityName))
            $entityName = AppDB::ffwRealEscapeString($this->entityName);
        $creator = AppDB::ffwRealEscapeString($this->creator);

        $q = "SELECT * FROM " . self::$defaultTable . " WHERE (name='$name' AND status < '". self::_STATUS_INACTIVE ."') AND id!='$id' " . (isset($entityName) && $entityName != "" && $entityId != 0 ?
                " AND {$entityName}Id='{$entityId}'"
                : ""
            ) . " LIMIT 1";

        if ($res = AppDB::query($q)) {
            if (AppDB::numRows($res) > 0)
                return true;
        }
        return false;
    }

    function sqlPrepare() {
        $this->creator = CurrentUser::getUsername();
        $this->id = CDecimal::integer64($this->id);
        $this->entityId = CDecimal::integer64($this->entityId);
        $this->position = CDecimal::integer64($this->position);
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->status);
        AppDB::ffwRealEscapeStringX($this->priority);
        AppDB::ffwRealEscapeStringX($this->participants);
        $this->startDate = date("Y-m-d", strtotime($this->startDate));
        $this->endDate = date("Y-m-d", strtotime($this->endDate));
        AppDB::ffwRealEscapeStringX($this->description);
        AppDB::ffwRealEscapeStringX($this->creator);
        $this->flags = CDecimal::integer64($this->flags);
    }

    function create() {
//        if (!self::checkPrivilege(CPrivilege::CREATE)) return false;
        $this->sqlPrepare();

        if ($this->exists()) {
            return CStatus::jsonError("Task already exists");
        }

        if ($this->entityClass != null && $this->entityId != 0) {
            CStatus::pushSettings(0);
            $item = $this->entityClass->get($this->entityId);  //If we can get it from the entity class then we have permission to create or edit its tasks
            CStatus::popSettings();
            if (!$item) return CStatus::jsonError("Operation not complete");
        }

        $q = "INSERT INTO " . self::$defaultTable . " SET
            `creationDate` = UTC_TIMESTAMP(),
            `lastUpdateDate` = UTC_TIMESTAMP(),
            `name` = '$this->name', " .
        (isset($this->entityName) && $this->entityName != "" && $this->entityId != 0 ?
            "`entityId` = '$this->entityId'," : "") .
            "`status` = '$this->status',
            `priority` = '$this->priority',
            `position` = '0',
            `creator` = '$this->creator',
            `startDate` = '$this->startDate',
            `endDate` = '$this->endDate',
            `participants` = '$this->participants',
            `description` = '$this->description',
            `notes` = '$this->notes',
            `flags` = '$this->flags'";

        AppDB::beginTransaction();
        try {
            if ($res = AppDB::query($q)) {
                $this->id = AppDB::last_id($res); //Last inserted item
                CForensic::log(static::$forensicPath,"Create " . static::$forensicPath, $this->id, json_encode($this), "");
                AppDB::commit();
                return CStatus::jsonSuccess("Task created");
            }
            AppDB::rollback();
            return CStatus::jsonError("Task not created");
        } catch (\Exception $e) {
            AppDB::rollback();
            return CStatus::jsonError("Exception : " . $e->getMessage());
        }
    }

    function edit() {
        $this->sqlPrepare();
        if ($this->exists()) {
            CStatus::jsonError("Task already exists $this->id");
            return false;
        }

        if ($this->entityClass != null && $this->entityId != 0) {
            CStatus::pushSettings(0);
            $item = $this->entityClass->get($this->entityId);  //If we can get it from the entity class then we have permission to create or edit its tasks
            CStatus::popSettings();

            if (!$item) return CStatus::jsonError("Operation not complete");
        }

        $q = "UPDATE " . self::$defaultTable . " SET
            `lastUpdateDate` = UTC_TIMESTAMP(),
            `name` = '$this->name', ".
//        (isset(self::$entityName) && self::$entityName != "" && self::$entityId != 0 ? "`entityId` = '$this->entityId'," : "") .
            "`status` = '$this->status',
            `priority` = '$this->priority',
            `creator` = '$this->creator',
            `startDate` = '$this->startDate',
            `endDate` = '$this->endDate',
            `participants` = '$this->participants',
            `description` = '$this->description',
            `notes` = '$this->notes',
            `flags` = '$this->flags' WHERE id='$this->id'
        ";
        AppDB::beginTransaction();
        try {
            CStatus::pushSettings(0);
            $item = self::get($this->id);
            CStatus::popSettings();

            if ($item) {
                $this->lastUpdateDate = gmdate("Y-m-d H:i:s");
                if ($res = AppDB::query($q)) {
                    CForensic::log(self::$forensicPath,"Update " . self::$forensicPath, $this->id, json_encode($this), "");
                    AppDB::commit();
                    return CStatus::jsonSuccess("Task updated successfully");
                }
            } else
                return CStatus::jsonError("Task not found");

            AppDB::rollback();
            return CStatus::jsonError("Task not updated");
        } catch (\Exception $e) {
            AppDB::rollback();
            return CStatus::jsonError("Exception : " . $e->getMessage());
        }
    }

    static function search($query="", $start=0, $limit=25)
    {
        return CStatus::jsonError("Not yet implemented");
    }

    static function setStatus($taskId, $status) {
        $taskId = CDecimal::integer64($taskId);
        if ($taskId == 0) return CStatus::jsonError("Task not found");

        CStatus::pushSettings(0);
        $task = self::get($taskId);
        CStatus::popSettings();

        if ($task) {
            if ($task['id'] != $taskId) return CStatus::jsonError("Error while updating task");
            if (is_numeric($status) && $status >= self::_STATUS_LOWEST && $status <= self::_STATUS_HIGHEST) {
                $status = CDecimal::integer64($status);
                if (AppDB::query("UPDATE " . CTask::$defaultTable . " SET status='$status' WHERE id='{$task['id']}';")) {
                    return CStatus::jsonSuccess("Done");
                }
            } else {
                return CStatus::jsonError("Invalid task details");
            }
        } else {
            return CStatus::jsonError("Task not found");
        }
        return CStatus::jsonError("Error");
    }
}

TaskClass::__setup();
