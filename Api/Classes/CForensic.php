<?php

namespace Api\Forensic;

use Ffw\Status\CStatus;
use Api\AppConfig;
use Api\AppDB;
use Api\Mt;
use Api\Users\CurrentUser;
class  CForensic {

    public static $defaultTable;
    public static $defaultPath;

    static function __setup() {
        self::$defaultTable = Mt::tableName("Forensic");
        self::$defaultPath = Mt::$dataPath . "/Forensic";
    }

    static function init($reset) {
        $q = "CREATE TABLE IF NOT EXISTS " . self::$defaultTable . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                `date`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `itemId`        VARCHAR(250) NOT NULL DEFAULT '',
                `module`        TEXT NOT NULL,
                `description`   TEXT NOT NULL,
                details         MEDIUMTEXT,
                username        VARCHAR(64) NOT NULL,
                flags           BIGINT UNSIGNED NOT NULL DEFAULT 0,
                comments        TEXT NOT NULL); ";
        try {
            if (AppDB::query($q)) {
                return CStatus::pushStatus("Forensics Initialized");
            }
            return CStatus::pushError("Forensics not initialized");
        } catch (\Exception $e) {
            CStatus::pushError($e->getMessage());
        }
    }

    static function initModule($module) {
        $q = "CREATE TABLE IF NOT EXISTS " . self::table($module) . "(
                id              BIGINT UNSIGNED UNIQUE AUTO_INCREMENT,
                `date`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                `itemId`        VARCHAR(250) NOT NULL DEFAULT '',
                `description`   TEXT NOT NULL,
                details         MEDIUMTEXT,
                username        VARCHAR(64) NOT NULL,
                flags           BIGINT UNSIGNED NOT NULL DEFAULT 0,
                comments        TEXT NOT NULL); ";
        AppDB::query($q);		
    }

    static function table($module) {
        return self::$defaultTable . "_$module";
    }

    static function logx ($module, $description, $details) {
        AppDB::ffwRealEscapeStringX($module);
        AppDB::ffwRealEscapeStringX($description);
        AppDB::ffwRealEscapeStringX($details);
        AppDB::query("INSERT INTO " . self::$defaultTable. " SET module='$module', description='$description', details='$details', username='" . CurrentUser::getUsername() . "', flags=0, comments=''");
    }

    static function log ($module, $description, $itemId, $details) {
        AppDB::ffwRealEscapeStringX($module);
        AppDB::ffwRealEscapeStringX($description);
        AppDB::ffwRealEscapeStringX($itemId);
        AppDB::ffwRealEscapeStringX($details);
        AppDB::query("INSERT INTO " . self::table($module) . " SET description='$description', itemId='$itemId', details='$details', username='" . CurrentUser::getUsername() . "', flags=0, comments=''");
    }
}

CForensic::__setup();
