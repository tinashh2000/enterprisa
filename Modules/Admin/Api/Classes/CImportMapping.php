<?php

namespace Api;

use Admin\CAdmin;
use Api\AppDB;
use Api\Mt;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Modules\CModule;
use Api\Forensic\CForensic;
use Api\CPrivilege;
use Api\ServiceClass;

class CImportMapping extends ServiceClass {

    const FLAG_READONLY  = 1 << 0;
    const FLAG_PERSONAL  = 1 << 10;
    const FLAG_CLASSIFIED  = 1 << 20;

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $name;
    var $mappings;
    var $flags;
    var $description;

    var $fileData;

    static $cache = array();

    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "ImportMapping";

    static $orderByParams = array("lastModifiedDate", "name", "id", "creationDate");
    static $selectItems = array("id", "name");
    static $joinElements = null;
    static $joinItems = null;
    static $joinDBs = null;
    static $searchParams = ["lastModifiedDate", "id", "name", "classification"];
    static $defaultCondition;

    function __construct($name, $type, $path, $classification, $description, $privileges=0, $flags=0)
    {
        $this->name = $name;
        $this->description = $description;
        $this->flags = $flags;
        $this->creationDate = $this->lastModifiedDate = gmdate("Y-m-d H:i:s", strtotime("now"));
        $this->creator = CurrentUser::getUsername();
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("ImportMappings");
        self::$defaultPath = Mt::dataPath("ImportMappings");
        self::$moduleDescription = "ImportMappings";
        self::$className = "CImportMappings";
        self::$defaultRecordName = "importMapping";
        self::$permissionsBase = CAdmin::IMPORTMAPPING_PERMISSIONSBASE;
        parent::_setupComplete();
    }

    static function init($reset) {

        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `name`          VARCHAR(250) NOT NULL UNIQUE,
                `mappings`      MEDIUMTEXT,
                flags           BIGINT UNSIGNED NOT NULL DEFAULT 0,
                description     TEXT NOT NULL); ";
		try {
			if (AppDB::query($q)) {
				return CStatus::pushStatus("Import mappings records creation successfully");
			}
		} catch (\Exception $e) {
			return CStatus::pushError("Import mappings creation failed");
		}
		return CStatus::pushError("Import mappings creation failed");

    }

    function sqlPrepare() {
        $this->id = intval($this->id);
        $this->flags = intval($this->flags);
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->mappings);
        AppDB::ffwRealEscapeStringX($this->description);
        AppDB::ffwRealEscapeStringX($this->creator);

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

        if (self::exists()) {
            CStatus::jsonError("Import mapping already exists");
        }

        $q = "INSERT INTO   " . self::$defaultTable . "  SET
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            mappings='$this->mappings',
            flags='$this->flags',
            description='$this->description'
        ";

        AppDB::beginTransaction();
        try {
            if ($res = AppDB::query($q)) {
                $this->id = AppDB::last_id($res); //Last inserted item
                CForensic::log(self::$forensicPath, "Create Import mapping", $this->id, json_encode($this));
                AppDB::commit();
                return CStatus::jsonSuccess("Import mapping created successfully");
            }
        }catch(\Exception $e) {

        }
        AppDB::rollback();
        return false;
    }

    function edit() {
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Import mapping already exists");
        }

        $q = "UPDATE " . self::$defaultTable . "  SET
            lastModifiedDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            `name`='$this->name',
            mappings='$this->mappings',
            flags='$this->flags',
            description='$this->description' WHERE id='$this->id'
        ";
        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            AppDB::commit();
            return CStatus::jsonSuccess("Import mapping edited successfully");
        }
        AppDB::rollback();
        return false;
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

CImportMapping::__setup();