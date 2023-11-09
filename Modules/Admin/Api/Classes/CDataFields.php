<?php

namespace Api;

use Admin\CAdmin;
use Api\AppDB;
use Api\Forensic\CForensic;
use Ffw\Status\CStatus;
use Inventory\CInventory;
use Api\DataFields;

class CDataFields extends DataFields {

    static $cache = array();

    public static $defaultTable;
    public static $defaultNamesTable;
    public static $defaultPath;
    public static $moduleDescription;
    public static $className;
    public static $defaultRecordName;
    public static $permissionsBase;
    protected static $forensicPath = "Admin_DataFields";

    static $orderByParams = array("id", "name");
    static $selectItems = array("id", "name");
    static $dbItems = array("*");
    static $searchParams = array("name", "description");

    static function init($reset) {
        $reset && AppDB::query("DROP TABLE IF EXISTS " . static::$defaultNamesTable);
        $q = "CREATE TABLE IF NOT EXISTS " . static::$defaultNamesTable . "(
                id                  BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                creationDate        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                lastModifiedDate    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `name`              VARCHAR(250) NOT NULL UNIQUE,
                `flags`             BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `creator`           TEXT NOT NULL);";

        try {
            if (AppDB::query($q)) {
                CForensic::initModule(static::$forensicPath);

                CStatus::pushSettings(0);
                if (parent::init($reset)) {
                    CStatus::popSettings();
                    return CStatus::pushStatus("Data fields created successfully");
                }

            }
        } catch (\Exception $e) {
            CStatus::jsonError($e->getMessage());
        }
        return CStatus::pushError("Data fields creation failed");
    }

    static function __setup() {
        self::$defaultTable = Mt::tableName("DataFields");
        self::$entityDB =  self::$defaultNamesTable = Mt::tableName("DataFieldsNames");
        static::$defaultTable = self::$defaultTable;
        self::$defaultPath = Mt::dataPath("DataFields");
        self::$moduleDescription = "DataFields";
        self::$className = "CDataFields";
        self::$defaultRecordName = "dataFields";
        static::$entityName = "dataFields";
        self::$permissionsBase = CAdmin::DATAFIELDS_PERMISSIONSBASE;
    }

    function sqlPrepare() {
        parent::sqlPrepare();
    }

    static function updateDataFields($data) {
        if (!self::checkPrivilege(CPrivilege::MODIFY)) return false;
        if (isset($data['entityId'])) {
            $fields = $data['fields'];
            if (!CDataFields::verifyFieldsFormat($fields)) return CStatus::jsonError("Data verification error");
            $fieldsFormat = json_encode($fields);
            $name = $data['name'];
            $q = "UPDATE " . self::$defaultTable. " SET name='$name', dataFieldsFormat='$fieldsFormat' WHERE id='{$data['entityId']}'";
            if (AppDB::query($q)) {
                return CStatus::jsonSuccess("Records updated successfully");
            }
        }
        return CStatus::jsonError("An error occurred and no changes were made");
    }
}
CDataFields::__setup();