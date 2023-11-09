<?php
namespace Ffw\Database\Sqlite;

use Api\CEnterprisa;

class sqliteDB extends \SQLite3 {

   var $error;

    function __construct($filename, $flags=SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $encryption_key = NULL) {
        $encryption_key = CEnterprisa::$sqliteEncryption;
        try {
           $this->open($filename, $flags, $encryption_key);
           $this->error = false;
        } catch (\Exception $e) {
            echo "error " . $e->getMessage();
        }
    }

    static function escape($str) {
        return escapeString($str);
    }

    static function escapeStringX(&$str) {
        $str = \SQLite3::escapeString($str);
    }

    function fetchAssoc($result) {
      return $result->fetchArray(SQLITE3_ASSOC);
    }

    function fetchArray($result) {
      return $result->fetchArray(SQLITE3_NUM);
    }

    function fetch ($result) {
       return $result->fetch();
    }

    function hasRows($result) {
        return $result->numColumns();// && $result->columnType(0) != SQLITE3_NULL;
    }

    function numColumns($result) {
       return $result->numColumns;
    }

    function numRows($result) {
        return $result->numRows();
    }

    function closeResult($result) {
       $result->finalize();
    }

}

?>
