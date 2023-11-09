<?php


namespace Api;


use Api\Forensic\CForensic;
use Api\Users\CurrentUser;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Api\AppDB;
use Api\Mt;
use Api\ServiceClass;
use Api\Session\CSession;

class CModuleSettings extends ServiceClass
{

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $module;
    var $name;
    var $data;
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
    protected static $forensicPath = "Settings";

    function __construct($module, $name, $data, $description, $flags = 0)
    {
        $this->id=0;
        $this->module = $module;
        $this->name = $name;
        $this->data = $data;
        $this->description = $description;
        $this->flags = $flags;
        $this->creator = CurrentUser::getUsername();
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("ModuleSettings");
        self::$defaultPath = Mt::dataPath("ModuleSettings");
        self::$moduleDescription = "Settings";
        self::$className = "CModuleSettings";
        self::$defaultRecordName = "settings";
        self::$permissionsBase = 0; //CSettings::BASIC_PERMISSION;
    }

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . self::$defaultTable);

        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id                  BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `module`            VARCHAR(250) NOT NULL UNIQUE,
                `name`              VARCHAR(250),
                `data`              MEDIUMTEXT NOT NULL,
                `description`       TEXT NOT NULL,
                creator             TEXT NOT NULL,
                flags               BIGINT UNSIGNED NOT NULL DEFAULT 0); ";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(self::$forensicPath);
                return CStatus::pushStatus("Settings created successfully");
            }
        } catch (\Exception $e) {
        }
        return CStatus::pushError("Settings creation failed");
    }

    function sqlPrepare() {
        $this->id = CDecimal::integer64($this->id);
        $this->flags = intval($this->flags);
        AppDB::ffwRealEscapeStringX($this->module);
        AppDB::ffwRealEscapeStringX($this->name);
        AppDB::ffwRealEscapeStringX($this->data);
        AppDB::ffwRealEscapeStringX($this->description);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

        function exists($fetch = false) {
            AppDB::ffwRealEscapeStringX($this->id);
            AppDB::ffwRealEscapeStringX($this->name);
            AppDB::ffwRealEscapeStringX($this->module);
            $q = "SELECT * FROM " . self::$defaultTable . " WHERE name='{$this->name}' AND module='{$this->module}' AND id != '$this->id' LIMIT 1";
            if ($res = AppDB::query($q)) {
                if (AppDB::numRows($res) > 0) {
                    if (!$fetch) return true;
                    return AppDB::fetchAssoc($res);
                }
            }
            return false;
        }

        function create() {
        }

        function edit() {

        }

    function set() {
        self::sqlPrepare();
        if ($s = self::exists(true)) {
            $q = "UPDATE " . self::$defaultTable . " SET lastModifiedDate=UTC_TIMESTAMP(), data='{$this->data}' WHERE id='{$s['id']}'";
            if (AppDB::query($q)) {
                $this->id = $s['id'];
                CForensic::log(self::$forensicPath, "Edit Settings", $this->id, json_encode($this));
                AppDB::commit();
                return CStatus::jsonSuccess("Settings updated successfully");
            }
            return CStatus::jsonError("Settings not updated");
        }

        $q = "INSERT INTO " . self::$defaultTable. " SET  
            creationDate=UTC_TIMESTAMP(),
            lastModifiedDate=UTC_TIMESTAMP(),
            name='{$this->name}',
            data='{$this->data}',
            description='{$this->description}',
            creator='{$this->creator}',
            flags=$this->flags
        ";

        AppDB::beginTransaction();
        if ($res = AppDB::query($q)) {
            $this->id = AppDB::last_id($res);
            CForensic::log(self::$forensicPath, "Create Settings", $this->id, json_encode($this));
            AppDB::commit();
            return CStatus::jsonSuccess("Settings successfully updated");
        }
        AppDB::rollback();
        return CStatus::jsonError("Settings not updated");
    }

    static function get($id, $cond="") {
        $name = $id;
        AppDB::ffwRealEscapeStringX($name);
        $q = "SELECT * FROM " . self::$defaultTable . " WHERE name='{$name}' LIMIT 1";
        if ($res = AppDB::query($q)) {
            $item = AppDB::fetchAssoc($res);
            return CStatus::jsonSuccessItem("settings", $item);
        }
        return CStatus::jsonError("Settings not found");
    }

    static function gets($names) {
        $namex = explode(",", $names);

        $q = "SELECT * FROM " . self::$defaultTable . " WHERE ";
        $cm = "";
        foreach($namex as $n) {
            $n = trim($n);

            if ($n != "") {
                $q .= "{$cm}name='$n'";
                $cm = " OR ";
            }
        }

        if ($res = AppDB::query($q)) {
            $ret = [];
            while ($item = AppDB::fetchAssoc($res)) {
                //$ret[$item['name']] = ["data" => $item['data'], "description"=>$item['description']];
                $ret[$item['name']] = $item['data'];
            }
            return CStatus::jsonSuccessItem("settings", $ret);
        }
        return CStatus::jsonError("Settings not found");
    }

}

CModuleSettings::__setup();