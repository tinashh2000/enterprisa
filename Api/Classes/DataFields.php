<?php

namespace Api;

use Api\AppDB;
use Api\Mt;
use Api\SqlBuilder;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Inventory\CInventory;
use Currencies\CCurrency;
use Modules\CModule;
use Api\Forensic\CForensic;
use Api\CPrivilege;
use Api\ServiceClass;
use ProductList\CProductList;

abstract class DataFields
{

    var $creationDate;
    var $lastModifiedDate;
    var $id;
    var $entityId;
    var $format;
    var $fieldsData;
    var $flags;
    var $creator;

    static $cache = array();

    public static $entityName;
    public static $entityDB;
    public static $maxFields = 20;
    public static $defaultTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase = 0;
    protected static $forensicPath = "DataFields";

    static $orderByParams = array("id", "name");
    static $selectItems = array("id", "name");
    static $dbItems = array("*");
    static $searchParams = array("name", "description");

    function __construct($format = null, $fieldsData = [])
    {
        $this->format = $format;
        $this->fieldsData = $fieldsData;
        $this->creator = CurrentUser::getUsername();
        $this->creationDate =
        $this->lastModifiedDate = gmdate("Y-m-d H:i:s", strtotime("now"));
    }

    static function __setup()
    {
//        static::$defaultTable = CInventory::tableName("DataFields");
        static::$defaultPath = Mt::dataPath("DataFields");
        static::$moduleDescription = "DataFields";
        static::$className = "DataFields";
        static::$defaultRecordName = "dataField";
        static::$entityName = (isset(static::$entityName) && static::$entityName != "") ? static::$entityName : "entity";
    }

    static function init($reset)
    {

        $reset && AppDB::query("DROP TABLE IF EXISTS " . static::$defaultTable);
        $q = "CREATE TABLE IF NOT EXISTS " . static::$defaultTable . "(
                id                  BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP()," .
            static::$entityName . "Id          BIGINT UNSIGNED, " .
            ((static::$entityName != "entity" && static::$entityDB != "") ? //Only use a foreign key if the entity is not called entity and if there is a joining DB
                "FOREIGN KEY(" . static::$entityName . "Id) REFERENCES " . static::$entityDB . "(id)," : "");

        for ($i = 0, $d = 1; $i < static::$maxFields; $i++, $d++) {
            $q .= "`field{$d}`  " . (($d > 15) ? "MEDIUMTEXT" : "TEXT") . ",";
        }

        $q .= "
            `flags`             BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `creator`           TEXT NOT NULL
            );";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(static::$forensicPath);
                return CStatus::pushStatus("Data fields created successfully");
            }
        } catch (\Exception $e) {
            CStatus::jsonError($e->getMessage());
        }
        return CStatus::pushError("Data fields creation failed");
    }

    function sqlPrepare()
    {
        $this->id = CDecimal::integer64($this->id);
        $this->entityId = CDecimal::integer64($this->entityId);

        $nFields = count($this->fieldsData);
        for ($i = 0, $d = 1; $i < $nFields; $i++, $d++) {
            if ($this->fieldsData[$i] !== null) {   //Ignore all fields that are going to be null
                AppDB::ffwRealEscapeStringX($this->fieldsData[$i]);
            }
        }

        $this->flags = CDecimal::integer64($this->flags);
        $this->creator = AppDB::ffwRealEscapeString(CurrentUser::getUsername());
    }

    function exists()
    {
        $id = AppDB::ffwRealEscapeString($this->id);
        $entityId = AppDB::ffwRealEscapeString($this->entityId);
        $entityName = AppDB::ffwRealEscapeString(static::$entityName);

//        echo "SELECT * FROM " . static::$defaultTable . " WHERE ({$entityName}Id='{$entityId}' AND id != '$id') LIMIT 1";

        if ($res = AppDB::query("SELECT * FROM " . static::$defaultTable . " WHERE ({$entityName}Id='{$entityId}' AND id != '$id') LIMIT 1")) {
            if ($p = AppDB::fetchAssoc($res)) {
                return $p;
            }
        }
        return false;
    }

    public static function verifyFieldsFormat($dataFields)
    {
        if (!is_array($dataFields)) return false;

        foreach ($dataFields as $field) {

            if (!isset($field['name']) || !isset($field['type']))
                return false;
            switch ($field['type']) {
                case 'list':
                case 'marital':
                case 'gender':
                    if (!isset($field['values'])) return false;
                    break;
                case 'int':
                case 'dec':
                case 'perc':
                case 'check':
                case 'longstr':
                case 'str':
                    break;
                default:
                    return false;
            }
        }
        return true;
    }

    static function verifyData($format, $data)
    {
        if (is_array($data) && is_array($format)) {
            $nItems = count($format);

            for ($c = 0; $c < $nItems; $c++) {

                $fieldFormat = $format[$c] ?? null;
                $fieldData = $data[$c] ?? null;

                if (!isset($fieldFormat['name']) || !isset($fieldFormat['type']))
                    return CStatus::jsonError("Name or type missing on data format");

                switch ($fieldFormat['type']) {
                    case 'list':
                    case 'marital':
                    case 'gender':
                        if (!isset($fieldFormat['values'])) return CStatus::jsonError("No value specified for list");
                        break;
                    case 'check':
                    case 'int':
                    case 'dec':
                    case 'perc':
                    case 'str':
                    case 'longstr':
                        break;
                    default:
                        return CStatus::jsonError("Invalid data field");
                }
            }
            return true;
        }
        return CStatus::jsonError("Ambiguous data fields");
    }

    function create()
    {
        $this->sqlPrepare();
        if (!$this->verifyData($this->format, $this->fieldsData)) {
            return CStatus::jsonError("Data fields format error" . CStatus::popError());
        }
        if ($this->exists()) {
            return CStatus::jsonError("Data field already exists");
        }

        $q = "INSERT INTO   " . static::$defaultTable . "  SET
                creationDate=UTC_TIMESTAMP(),
                lastModifiedDate=UTC_TIMESTAMP()," .
                static::$entityName . "Id = '{$this->entityId}',
                ";

        $nFields = count($this->fieldsData);
        for ($i = 0, $d = 1; $i < $nFields; $i++, $d++) {
            if ($this->fieldsData[$i] !== null) {   //Ignore all fields that are going to be null
                $q .= "`field{$d}` = '{$this->fieldsData[$i]}',";
            }
        }

        $q .= "`creator`       = '$this->creator',
                `flags` = '$this->flags'";

        if ($res = AppDB::query($q)) {
            $this->id = AppDB::last_id($res);
            CForensic::log(static::$forensicPath, "Create DataField", $this->id, json_encode($this));
            return CStatus::jsonSuccess("DataField created successfully");
        }
        return CStatus::jsonError("DataField creation failed");
    }

    function edit()
    {
        $this->sqlPrepare();
        if (!$this->verifyData($this->format, $this->fieldsData)) {
            return CStatus::jsonError("Data fields format error");
        }

        if (static::exists()) {
            return CStatus::jsonError("Data field already exists");
        }

        $sql = new SqlBuilder(static::$defaultTable, SqlBuilder::UPDATE);
        $sql->addN("lastModifiedDate","UTC_TIMESTAMP()", SqlBuilder::NO_QUOTES | SqlBuilder::ADDITIONAL);
//        $sql->add('name', $this->name);
//        $sql->add('description', $this->description);

        $nFields = count($this->fieldsData);
        for ($i = 0, $d = 1; $i < $nFields; $i++, $d++) {
            $sql->add( "field{$d}", $this->fieldsData[$i]);
        }

        $sql->condition('id', $this->id, SqlBuilder::O_EQ, SqlBuilder::C_NONE);
        $q = $sql->get();

        if ($q == null) {
            return CStatus::jsonError("Nothing to do");
        }

        if ($res = AppDB::query($q)) {
            CForensic::log(static::$forensicPath, "Edit DataField", $this->id, json_encode($this));
            return CStatus::jsonSuccess("DataField updated successfully");
        }
        return CStatus::jsonError("DataField not updated");
    }

    static function search($query = "", $start = 0, $limit = 25)
    {
        AppDB::ffwRealEscapeStringX($query);
        if ($query == "")
            $q = "SELECT * FROM " . static::$defaultTable . " LIMIT $start, $limit";
        else
            $q = "SELECT * FROM " . static::$defaultTable . " WHERE name LIKE '%$query%' OR description LIKE '%$query%' LIMIT $start, $limit";
        return $res = AppDB::query($q);
    }

}

DataFields::__setup();
