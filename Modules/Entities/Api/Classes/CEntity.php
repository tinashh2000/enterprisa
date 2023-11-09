<?php

namespace Entities;

use Api\AppDB;
use Api\Mt;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Inventory\CInventory;
use Modules\CModule;
use Api\Forensic\CForensic;
use Api\CPrivilege;
use Api\ServiceClass;
use const Entities\modulePrivilegesBase;

class CEntity extends ServiceClass
{

    const FLAG_DEFAULT = 1 << 0;
    const FLAG_VIRTUAL = 1 << 1;

    const FLAG_INLINE = 1 << 10;    //Within the record
    const FLAG_LIST = 1 << 11;      //List in another table
    const FLAG_YESNO = 1 << 12;     //Simple Yes/No option
    const FLAG_ENTITY_TYPE_LIST = 1;
    const FLAG_ENTITY_TYPE_YESNO = 2;

    const FLAG_CONTROL = 1 < 20;

    const TYPE_LIST = 10;
//    const TYPE_LIST = 10;


    const TYPE_CONTROL_TEXTBOX = 31;
    const TYPE_CONTROL_COMBOBOX = 32;
    const TYPE_CONTROL_CHECKBOX = 33;
    const TYPE_CONTROL_RADIOBUTTONS = 34;

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $name;
    var $module;
    var $classification;
    var $type;
    var $value;
    var $location;
    var $description;
    var $flags;
    var $creator;

    static $cache = array();

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "Entities";

    static $orderByParams = ["name", "module", "classification", "type"];
    static $selectItems = array("id", "name");
    static $joinElements = null;
    static $joinItems = null;
    static $joinDBs = null; //Initialized in __setup
    static $searchParams = ["name", "module", "classification", "type"];


    function __construct($name, $module, $classification, $type, $value, $description, $flags = 0)
    {
        $this->id = 0;
        $this->name = $name;
        $this->module = $module;
        $this->classification = $classification;
        $this->type = $type;
        $this->value = $value;
        $this->description = $description;
        $this->flags = $flags;
        $this->creator = CurrentUser::getUsername();
    }

    static function __setup()
    {
        self::$defaultTable = CEntities::tableName("Entities");
        self::$defaultPath = CEntities::moduleDataPath("Entities");
        self::$moduleDescription = "Entities";
        self::$className = "CEntities";
        self::$defaultRecordName = "entity";
        self::$permissionsBase = CEntities::BASIC_PERMISSION;
        parent::_setupComplete();
    }

    static function init($reset)
    {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id                  BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `name`              VARCHAR(64) NOT NULL,
                `module`            VARCHAR(64) NOT NULL,
                `classification`    VARCHAR(250) NOT NULL,
                `type`              VARCHAR(128) NOT NULL,
                `value`             MEDIUMTEXT NOT NULL,
                `location`          TEXT NOT NULL,
                creator             TEXT NOT NULL,
                flags               BIGINT UNSIGNED NOT NULL DEFAULT 0,
                description         TEXT NOT NULL); ";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::jsonSuccess("Entities created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::jsonError("Entities creation failed");
    }

    function sqlPrepare()
    {
        $this->id = CDecimal::integer64($this->id);
        $this->flags = CDecimal::integer64($this->flags);
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->module);
        AppDB::ffwRealEscapeStringX($this->classification);
        AppDB::ffwRealEscapeStringX($this->type);
        AppDB::ffwRealEscapeStringX($this->value);
        AppDB::ffwRealEscapeStringX($this->location);
        AppDB::ffwRealEscapeStringX($this->description);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

    static function getEntity($name, $module, $classification)
    {
        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE name='$name' AND module='$module' AND classification='$classification' LIMIT 1")) {
            return AppDB::fetchAssoc($res);
        }
    }

    function exists()
    {
        $name = AppDB::ffwRealEscapeString($this->name);
        $id = AppDB::ffwRealEscapeString($this->id);
        $module = AppDB::ffwRealEscapeString($this->module);
        $classification = AppDB::ffwRealEscapeString($this->classification);
        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE name='$name' AND module='$module' AND classification='$classification' AND id != '$id' LIMIT 1")) {
            return AppDB::numRows($res) > 0;
        }
        return false;
    }

    function create()
    {
        $this->sqlPrepare();

        if ($this->exists()) {
            CStatus::jsonError("Entity already exists");
        }

        $q = "INSERT INTO   " . self::$defaultTable . "  SET
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            `module`='$this->module',
            `classification`='$this->classification',
            `type`='$this->classification',
            `value`='$this->value',
            `location`='$this->location',
            description='$this->description',
            creator='$this->creator',
            flags='$this->flags'
        ";

        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            $this->id = AppDB::last_id($res);
            CForensic::log(self::$forensicPath, "Create Entity", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Entity created  successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Entity creation failed");
    }

    function edit()
    {
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Entity already exists");
        }

        AppDB::beginTransaction();
        if ($res = AppDB::query("UPDATE " . self::$defaultTable . "  SET
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            `module`='$this->module',
            `classification`='$this->classification',
            `type`='$this->classification',
            `value`='$this->details',
            `location`='$this->location',
            description='$this->description',
            creator='$this->creator',
            flags='$this->flags' WHERE id='$this->id'")) {
            CForensic::log(self::$forensicPath, "Edit Entity", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Entity updated successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Entity update failed");
    }

    static function search($query = "", $start = 0, $limit = 25)
    {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name LIKE '%$query%' OR description LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }
}

CEntity::__setup();