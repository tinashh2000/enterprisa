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

class CEntityItem extends ServiceClass {

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $entity;    //The entity that owns this  item
    var $name;
    var $title;
    var $value;
    var $privilegesList;
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

    function __construct($entity, $name, $title, $value, $description, $flags = 0)
    {
        $this->id=0;
        $this->entity = $entity;
        $this->name = $name;
        $this->title = $title;
        $this->value = $value;
        $this->description = $description;
        $this->flags = $flags;
        $this->creator = CurrentUser::getUsername();
    }

    static function __setup() {
        self::$defaultTable = CEntities::tableName("EntityItems");
        self::$defaultPath = CEntities::moduleDataPath("EntityItems");
        self::$moduleDescription = "EntityItem";
        self::$className = "CEntityItem";
        self::$defaultRecordName = "entityItem";
        self::$permissionsBase = CEntities::BASIC_PERMISSION;
        parent::_setupComplete();
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);
        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id                  BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                entity              BIGINT UNSIGNED,
                `name`              VARCHAR(250) NOT NULL,
                `title`             TEXT NOT NULL,
                `value`             MEDIUMTEXT NOT NULL,
                `privilegesList`    MEDIUMTEXT NOT NULL,
                `description`       TEXT NOT NULL,
                creator             TEXT NOT NULL,
                flags               BIGINT UNSIGNED NOT NULL DEFAULT 0); ";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::jsonSuccess("Entities created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::jsonError("Entities creation failed");
    }

    function sqlPrepare() {
        $this->id = CDecimal::integer64($this->id);
        $this->flags = intval($this->flags);
        AppDB::ffwRealEscapeStringX($this->entity);
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->title);
        AppDB::ffwRealEscapeStringX($this->value);
        AppDB::ffwRealEscapeStringX($this->description);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

    static function getItems($entityId) {
        $q = "SELECT * FROM " . self::$defaultTable . " WHERE entity='$entityId'";
        if ($res = AppDB::query($q)) {
            return $res;
        }
        return null;
    }

    function exists() {
        $id = AppDB::ffwRealEscapeString($this->id);
        $name = AppDB::ffwRealEscapeString($this->name);
        $title = AppDB::ffwRealEscapeString($this->title);
        $entity = AppDB::ffwRealEscapeString($this->entity);
        $value = AppDB::ffwRealEscapeString($this->value);
        if ($res = AppDB::query("SELECT * FROM " . self::$defaultTable . " WHERE name='$name' AND entity='$entity' AND value='$value' AND id != '$id' LIMIT 1")) {
            return AppDB::numRows($res) > 0;
        }
        return false;
    }

    function create() {
        $this->sqlPrepare();
        if ($this->exists()) {
            return CStatus::jsonError("Entity already exists");
        }
        $q = "INSERT INTO   " . self::$defaultTable . "  SET
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `entity`='$this->entity',
            `name`='$this->name',
            `title`='$this->title',
            `value`='$this->value',
            `privilegesList`='$this->privilegesList',
            `description`='$this->description',
            `creator`='$this->creator',
            `flags`='$this->flags'";
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
            return CStatus::jsonError("Entity already exists");
        }
        AppDB::beginTransaction();
        if ($res = AppDB::query("UPDATE " . self::$defaultTable . "  SET
            lastModifiedDate=UTC_TIMESTAMP(),
            `entity`='$this->entity',
            `name`='$this->name',
            `title`='$this->title',
            `value`='$this->value',
            `privilegesList`='$this->privilegesList',
            `description`='$this->description',
            `creator`='$this->creator',
            `flags`='$this->flags' WHERE id='$this->id'")) {
            CForensic::log(self::$forensicPath, "Edit Entity", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Entity updated successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Entity update failed");
    }

    static function search($query="", $start=0, $limit=25) {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name LIKE '%$query%' OR description LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }
}

CEntityItem::__setup();