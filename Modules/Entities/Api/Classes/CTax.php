<?php

namespace Entities\Taxes;

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
use Entities\CEntities;
use const Taxes\modulePrivilegesBase;

class CTax extends ServiceClass {

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $name;
    var $amount;
    var $method;
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

    protected static $forensicPath = "Entities_Taxes";
    function __construct($name, $amount, $method, $description, $flags = 0)
    {
        $this->id=0;
        $this->name = $name;
        $this->amount = $amount;
        $this->method = $method;
        $this->description = $description;
        $this->flags = $flags;
        $this->creator = CurrentUser::getUsername();
    }

    static function __setup() {
        self::$defaultTable = CEntities::tableName("Taxes");
        self::$defaultPath = CEntities::moduleDataPath("Taxes");
        self::$moduleDescription = "Taxes";
        self::$className = "CTax";
        self::$defaultRecordName = "tax";
        self::$permissionsBase = CEntities::TAXES_PERMISSIONSBASE;
        parent::_setupComplete();
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `name`          VARCHAR(64) NOT NULL UNIQUE,
                `amount`        DECIMAL(30,2),
                `method`        INT UNSIGNED,
                creator        TEXT NOT NULL,
                flags           BIGINT UNSIGNED NOT NULL DEFAULT 0,
                description     TEXT NOT NULL); ";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::pushStatus("Taxes created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::pushError("Taxes creation failed");
    }

    function sqlPrepare() {
        $this->id = CDecimal::integer64($this->id);
        $this->flags = CDecimal::integer($this->flags);
        AppDB::ffwRealEscapeStringX($this->name);
        $this->amount = CDecimal::decimal($this->amount);
        $this->method = CDecimal::integer($this->method);
        AppDB::ffwRealEscapeStringX($this->description);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());

        if ($this->amount == 0) return CStatus::jsonError("Please enter a non-zero amount");
        if ($this->method <= 0 || $this->method >= 3) return CStatus::jsonError("Invalid values");
        return true;
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
        if (!$this->sqlPrepare()) return false;

        if ($this->exists()) {
            return CStatus::jsonError("Tax already exists");
        }

        $q = "INSERT INTO   " . self::$defaultTable . "  SET
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            `amount`='$this->amount',
            `method`='$this->method',
            description='$this->description',
            creator='$this->creator',
            flags='$this->flags'
        ";

        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            $this->id = AppDB::last_id($res);
            CForensic::log(self::$forensicPath, "Create Tax", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Tax created successfully");
        }

        AppDB::rollback();
        return CStatus::jsonError("Tax creation failed");
    }

    function edit()
    {
        if (!$this->sqlPrepare()) return false;

        if (self::exists())
            return CStatus::jsonError("Tax already exists");

        AppDB::beginTransaction();
        if ($res = AppDB::query("UPDATE " . self::$defaultTable . "  SET
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            `amount`='$this->amount',
            `method`='$this->method',
            description='$this->description',
            creator='$this->creator' WHERE id='$this->id'")) {

            CForensic::log(self::$forensicPath, "Edit Tax", $this->id, json_encode($this));
            AppDB::commit();

            return CStatus::jsonSuccess("Tax updated successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Tax update failed");
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

CTax::__setup();