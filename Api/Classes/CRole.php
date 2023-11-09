<?php

namespace Api\Roles;

use Api\AppDB;
use Api\CEnterprisa;
use Api\Mt;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Api\Forensic\CForensic;
use Api\CPrivilege;
use Api\ServiceClass;
//use const Roles\modulePrivilegesBase;

class CRole extends ServiceClass {

    const FLAG_DEFAULT  = 1 << 0;
    const FLAG_VIRTUAL  = 1 << 1;

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $name;
    var $privileges;
    var $privilegesList;
    var $description;
    var $flags;
    var $username;

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
    protected static $forensicPath = "Roles";

    function __construct($name, $privileges, $privilegesList, $description, $flags = 0)
    {
        $this->id=0;
        $this->name = $name;
        $this->privileges = $privileges;
        $this->privilegesList = $privilegesList;
        $this->description = $description;
        $this->flags = $flags;
        $this->username = CurrentUser::getUsername();
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("Roles");
        self::$defaultPath = Mt::dataPath("Roles");
        self::$moduleDescription = "Roles";
        self::$className = "CRoles";
        self::$defaultRecordName = "role";
        self::$permissionsBase = CEnterprisa::ROLES_PERMISSIONSBASE;
        parent::_setupComplete();
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `name`          VARCHAR(64) NOT NULL UNIQUE,
                username        TEXT NOT NULL,
                privileges      BIGINT UNSIGNED NOT NULL DEFAULT 0,
                privilegesList  MEDIUMTEXT NOT NULL,
                flags           BIGINT UNSIGNED NOT NULL DEFAULT 0,
                description     TEXT NOT NULL); ";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::pushStatus("Roles created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::pushError("Roles creation failed");
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
        if (!CPrivilege::check(CPrivilege::ROLE_ADMINISTRATOR)) return CStatus::jsonError("Access Denied");
        if ($this->exists()) {
            CStatus::jsonError("Role already exists");
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
            CForensic::log(self::$forensicPath, "Create Role", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Role created  successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Role creation failed");
    }

    function edit()
    {
        if (!CPrivilege::check(CPrivilege::ROLE_ADMINISTRATOR)) return CStatus::jsonError("Access Denied");
        $this->sqlPrepare();

        if (self::exists()) {
            CStatus::jsonError("Role already exists!");
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
            return CStatus::jsonSuccess("Role updated successfully");
        }
        AppDB::rollback();
        return CStatus::jsonError("Role update failed");
    }

    static function search($query="", $start=0, $limit=25) {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . self::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name LIKE '%$query%' OR description LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }

    static function getValues($roles) {
        $roles =str_replace(";", ",", $roles );
        AppDB::ffwRealEscapeStringX($roles);
        $q = "SELECT GROUP_CONCAT(DISTINCT (privileges) SEPARATOR ',') as privileges, GROUP_CONCAT(privilegesList SEPARATOR ',') AS roles FROM " . self::$defaultTable . " WHERE id IN ($roles)";
        if ($res = AppDB::query($q)) {
            $item = AppDB::fetchAssoc($res);
            return $item;
        }
        return "";
    }

}

CRole::__setup();
