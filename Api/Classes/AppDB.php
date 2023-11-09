<?php
namespace  Api;

class AppDB extends AbstractDB {
    protected static $dbPrefix;
    protected static $db = null;

    static function db() {
        return self::$db;
    }

    static function table ($name) {
        return self::$dbPrefix . $name;
    }

    static function __setup($db=null) {
        self::$db = $db == null ? Mt::$db : $db;
        static::$dbPrefix = defined('Api\dbPrefix') ? dbPrefix : "";
    }
}
