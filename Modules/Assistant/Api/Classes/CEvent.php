<?php
namespace Api;

use Accounta\Audit\CAudit;
use Api\AppDB;
use Api\CPrivilege;
use Api\Mt;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Crm\CCrm;
use Modules\CModule;
use Api\Forensic\CForensic;
use Api\ServiceClass;
use const Events\modulePrivilegesBase;

class CEvent extends ServiceClass {

    const FLAG_DEFAULT  = 1 << 0;
    const FLAG_VIRTUAL  = 1 << 1;

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $name;
    var $message;
    var $description;
    var $notes;
    var $visibility;
    var $venue;
    var $location;
    var $responses;
    var $startDate;
    var $endDate;
    var $flags;
    var $creator;

    static $cache = array();


    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "Assistant_Event";

    static $orderByParams = array("name", "venue", "description");
    static $selectItems = array("id", "name");
    static $dbItems = array("*");
    static $searchParams = array("name", "venue", "description");


    function __construct($name, $message, $description, $notes, $visibility, $startDate, $endDate, $flags = 0)
    {
        $this->id=0;
        $this->creationDate=gmdate("Y-m-d H:i:s");
        $this->name=$name;
        $this->message=$message;
        $this->description=$description;
        $this->notes=$notes;
        $this->visibility=$visibility;
        $this->venue = "";
        $this->location="";
        $this->responses="";
        $this->startDate=$startDate;
        $this->endDate=$endDate;
        $this->flags=$flags;
        $this->creator = CurrentUser::getUsername();
    }

    static function __setup() {
        self::$defaultTable = CAssistant::tableName("Events");
        self::$defaultPath = CAssistant::moduleDataPath("Events");
        self::$moduleDescription = "Events";
        self::$className = "CEvent";
        self::$defaultRecordName = "event";
        self::$permissionsBase = 0; //CAssistant::
        parent::_setupComplete();
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                startDate       DATETIME NOT NULL,
                endDate         DATETIME NOT NULL,
                `name`          VARCHAR(220) NOT NULL UNIQUE,
                `message`       TEXT NOT NULL,  
                `description`   TEXT NOT NULL,  
                `notes`         MEDIUMTEXT NOT NULL,  
                `visibility`    MEDIUMTEXT NOT NULL,  
                `venue`         MEDIUMTEXT NOT NULL,  
                `location`      MEDIUMTEXT NOT NULL,  
                `responses`     MEDIUMTEXT NOT NULL,  
                `pic`           MEDIUMTEXT NOT NULL,             
                `creator`      TEXT NOT NULL,
                `flags`         BIGINT UNSIGNED NOT NULL DEFAULT 0); ";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::pushStatus("Event created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::pushError("Event creation failed");
    }

    function sqlPrepare() {
        $this->creationDate = $this->lastModifiedDate=gmdate("Y-m-d H:i:s", strtotime("now"));
        $this->id = CDecimal::integer64($this->id);
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->message);
        AppDB::ffwRealEscapeStringX($this->description);
        AppDB::ffwRealEscapeStringX($this->notes);
        AppDB::ffwRealEscapeStringX($this->visibility);
        AppDB::ffwRealEscapeStringX($this->venue);
        AppDB::ffwRealEscapeStringX($this->location);
        AppDB::ffwRealEscapeStringX($this->responses);
        AppDB::ffwRealEscapeStringX($this->startDate);
        AppDB::ffwRealEscapeStringX($this->endDate);
        AppDB::ffwRealEscapeStringX($this->flags);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

    function exists() {
        $name = AppDB::ffwRealEscapeString($this->name);
        $id = AppDB::ffwRealEscapeString($this->id);
        $startDate =AppDB::ffwRealEscapeString($this->startDate);
        $endDate =AppDB::ffwRealEscapeString($this->endDate);

        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE (name='$name' AND startDate='$startDate' AND endDate='$endDate') AND id != '$id' LIMIT 1")) {
            return AppDB::numRows($res) > 0;
        }
        return false;
    }

    function verify() {
        if ($this->startDate > $this->endDate) return CStatus::jsonError("Start date must be before end date");
        return true;
    }
    function create() {
        $this->sqlPrepare();

        if ($this->exists()) {
            CStatus::jsonError("Event already exists");
        }

        $this->verify();

        $q = "INSERT INTO " . self::$defaultTable . "  SET
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`          = '$this->name',
            startDate       = '$this->startDate',
            endDate         = '$this->endDate',
            `message`       = '$this->message',  
            `description`   = '$this->description',  
            `notes`         = '$this->notes',  
            `visibility`    = '$this->visibility',
            `venue`         = '$this->venue',
            `location`      = '$this->location',
            `responses`     = '$this->responses',
            `pic`           = '',             
            `creator`      = '$this->creator',
            `flags`         = '$this->flags'
        ";

        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            $this->id = AppDB::last_id($res);
            self::uploadPicture(self::MODIFY_DB, "pics", $this->id."_pp");
            CForensic::log(self::$forensicPath, "Create Event", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Event created successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Event creation failed");
    }

    function edit()
    {
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Event already exists");
        }

        $this->verify();

        AppDB::beginTransaction();
        if ($res = AppDB::query("UPDATE " . self::$defaultTable . "  SET
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`          = '$this->name',
            startDate       = '$this->startDate',
            endDate         = '$this->endDate',
            `message`       = '$this->message',  
            `description`   = '$this->description',  
            `notes`         = '$this->notes',  
            `visibility`    = '$this->visibility',
            `venue`         = '$this->venue',
            `location`      = '$this->location',
            `responses`     = '$this->responses',           
            `creator`      = '$this->creator',
            `flags`         = '$this->flags' WHERE id='$this->id'")) {

            $this->id = AppDB::last_id($res);
            self::uploadPicture(self::MODIFY_DB, "pics", $this->id."_pp");
            CForensic::log(self::$forensicPath, "Edit Event", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Event updated successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Event update failed");
    }


    static function fetch($start=0, $limit=25, $startDate = null, $endDate = null) {    //Fetch for records
        $start = intval($start);
        $limit = intval($limit);
        $startDate = ($startDate == null) ? "1900-01-01" : $startDate;
        $endDate = ($endDate == null) ? gmdate("Y-m-d", strtotime("now +2 years")) : $endDate;

        if ($res = AppDB::query("SELECT * FROM " . static::$defaultTable . " WHERE startDate >='$startDate' AND startDate <= '$endDate' LIMIT $start, $limit")) {
            CStatus::jsonSuccessDB(static::$defaultRecordName."x", $res);
            die();
        }
        CStatus::jsonError("Unexpected error!");
    }

    static function search($query="", $start=0, $limit=25) {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name LIKE '%$query%' OR notes LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }
}

CEvent::__setup();