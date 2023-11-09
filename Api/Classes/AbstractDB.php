<?php
namespace Api;

use Api\Users\CUser;
use Ffw\Decimal\CDecimal;

abstract class AbstractDB {

    abstract static function table($name);
    abstract static function db();
    abstract static function __setup($db);

    static $numPendingTransactions = 0;
    static $remainingRollbacks = 0;

    static function constructLeftJoin($joinDBs, $joinElements, $dbItems) {
        $numJoinDBs = count($joinDBs);
        $numJoinElements = count($joinElements);

        if ($numJoinDBs != ($numJoinElements + 1) || ($numJoinDBs < 2)) return "";

        $q = "SELECT $dbItems FROM " . $joinDBs[0];
        for($c = 1; $c < $numJoinDBs; $c++) {
            $q .= " LEFT JOIN " . $joinDBs[$c] . " ON " . $joinElements[$c - 1];
        }
        return $q;
    }


    static function constructInnerJoin($joinDBs, $joinElements, $dbItems) {
        $numJoinDBs = count($joinDBs);
        $numJoinElements = count($joinElements);

        if ($numJoinDBs != ($numJoinElements + 1) || ($numJoinDBs < 2)) return "";

        $q = "SELECT $dbItems FROM " . $joinDBs[0];
        for($c = 1; $c < $numJoinDBs; $c++) {
            $q .= " INNER JOIN " . $joinDBs[$c] . " ON " . $joinElements[$c - 1];
        }
        return $q;
    }

    static function leftJoinX($table1, $table2, $joinCondition, $items="%2.*,%1.*") {
        $joinCondition = str_replace("%2", $table2, str_replace( "%1", $table1, $joinCondition));

        $items = str_replace("%2", $table2, str_replace( "%1", $table1, $items));
        return "SELECT $items FROM {$table1} LEFT JOIN $table2 ON {$joinCondition}";
    }
//
//    static function leftJoin($table1, $table2, $ele1, $ele2, $items="%2.*,%1.*") {
//        $items = str_replace("%2", $table2, str_replace( "%1", $table1, $items));
////        $ele1 = str_replace("%2", $table2, str_replace( "%1", $table1, $ele1));
////        $ele2 = str_replace("%2", $table2, str_replace( "%1", $table1, $ele2));
//        return "SELECT $items FROM {$table1} LEFT JOIN $table2 ON {$table1}.{$ele1} = {$table2}.{$ele2}";
//    }

    static function leftJoin2($table1, $table2, $table3, $items="%3.*,%2.*,%1.*", $firstJoin='%1.id=%2.id', $secondJoin='%2.id=%3.id') {
        $items = str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $items)));
        $firstJoin = str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $firstJoin)));
        $secondJoin = str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $secondJoin)));
        return "SELECT $items FROM {$table1} LEFT JOIN {$table2} ON $firstJoin LEFT JOIN {$table3} ON $secondJoin ";
    }



    static function innerJoin($table1, $table2, $table3, $items="%3.*,%2.*,%1.*", $firstJoin='%1.id=%2.id', $secondJoin='%2.id=%3.id') {
        $items = str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $items)));
        $firstJoin = str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $firstJoin)));
        $secondJoin = str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $secondJoin)));
        return "SELECT $items FROM {$table1} INNER JOIN {$table2} ON $firstJoin INNER JOIN {$table3} ON $secondJoin ";
    }

    static function innerJoin4($table1, $table2, $table3, $table4, $items="%4.*,%3.*,%2.*,%1.*", $firstJoin='%1.id=%2.id', $secondJoin='%2.id=%3.id', $thirdJoin='%3.id=%4.id') {
        $items = str_replace("%4", $table4,  str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $items))));
        $firstJoin = str_replace("%4", $table4,  str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $firstJoin))));
        $secondJoin = str_replace("%4", $table4,  str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $secondJoin))));
        $thirdJoin = str_replace("%4", $table4,  str_replace("%3", $table3, str_replace("%2", $table2, str_replace( "%1", $table1, $secondJoin))));
        return "SELECT $items FROM {$table1} INNER JOIN {$table2} ON $firstJoin INNER JOIN {$table3} ON $secondJoin  INNER JOIN {$table4} ON $thirdJoin ";
    }

    static function count($tableName,$cond="") {
        AppDB::ffwRealEscapeStringX($tableName);

        if ($cond != "")
            $tableName .= " WHERE $cond";

        if ($res = self::query("SELECT COUNT(*) as count FROM $tableName")) {
            $item = self::fetchAssoc($res);
            return $item['count'];
        }
        return 0;
    }

    static function countX($str) {
        if ($res = self::query($str)) {
            $item = self::fetchAssoc($res);
            return $item['count'];
        }
        return 0;
    }

    static function disableLogs() {
        if ($sdb = static::db()) {
            $sdb->baseclass->debug = false;
//        $sdb->baseclass->errorreport = false;
        } else {
//            echo "DB Error";
        }
    }

    static function query($str) {
        return static::db()->query($str);
    }

    static function multiQuery($str) {
        return static::db()->multiquery($str);
    }

    static function fetchAssoc($str) {
        return static::db()->fetchAssoc($str);
    }

    static function affectedRows() {
        return static::db()->affectedRows();
    }

    static function ffwRealEscapeString($str) {
        return static::db()->ffwRealEscapeString($str);
    }

    static function ffwRealEscapeStringX(&$str) {
        return static::db()->ffwRealEscapeStringX($str);
    }

    /*
     *
     * Allow nested transactions, with the ability to rollback everything if an error occurs.
     *
     */
    static function beginTransaction() {
        if (self::$numPendingTransactions++ > 0) return true; //Just call this once. Since
        return static::db()->beginTransaction();
    }

    /*
     *
     *  Commit does not commit until the last commit call in the nested calls.
     *  This is because the commit function doesn't keep nested count
     *  And if an error occurs, it should be able to rollback the entire transaction
     *  Thats why we wait until the last commit.
     *  If a rollback is called before the last commit then everything is thrown away to rollback the transaction
     *
     */

    static function commit() {
        if (self::$numPendingTransactions > 1) {
            self::$numPendingTransactions--;
            return true;
        } else if (self::$numPendingTransactions == 1) {
            self::$numPendingTransactions = 0;
            return static::db()->commit();
        }
        return true;
    }

    /*
     *
     * Rollback on the first call, and ignore on the other nested calls.
     *
     *
     */
    static function rollback() {
        /*
         * If a certain rollback was done, but it was nested,
         * we just count down the nests without doing anything
         *
         */
        if (self::$remainingRollbacks > 0) {
            self::$remainingRollbacks--;
        }
        else if (self::$numPendingTransactions > 0) {
            /*
             *
             * If no previous rollback has pending nests, record the pending nested rollbacks
             * And reset # of pending transactions.
             * This allows newer transactions to overlap with failed transactions
             * Newer transactions can be safely committed, while old nested failed transactions are rolled back
             *
             */
            self::$remainingRollbacks += (self::$numPendingTransactions - 1);
            self::$numPendingTransactions = 0;
            return static::db()->rollback();
        }
        return true;
    }

    static function last_id($res) {
        return static::db()->last_id($res);
    }

    static function numRows($res) {
        return static::db()->numRows($res);
    }


    const GL_FIELD_IS_INTEGER = 1;  //Field in question is an integer
    const GL_FIELD_IS_DECIMAL = 2;  //Field in question is a decimal
    const GL_FIELD_IS_NOT_ZERO = 4; //Field in question is not 0
    const GL_FIELD_IS_NOT_BLANK = 8;    //Field in question is not blank
    const GL_DB_ON_BLANK_CONDITION = 128;   //Performs DB operation if condition is blank

    public static function getList($field, $list, $flags=(self::GL_FIELD_IS_INTEGER | self::GL_FIELD_IS_NOT_ZERO), $table=null) {
        if ($field == "") return null;
        $_items = explode(",", $list);
        $cond = "";
        if (count($_items) > 0) {
            foreach ($_items as $co) {
                $co = trim($co);
                if ($co == "*") {   //If wildcard, don't put a condition.
                    $cond = "";
                    break;
                }
                if ($flags & self::GL_FIELD_IS_INTEGER) {
                    if  (!is_numeric($co)) continue;
                    $co = CDecimal::integer64($co);
                    if (($flags & self::GL_FIELD_IS_NOT_ZERO) && $co == 0) continue; //Invalid
                }
                else if ($flags & self::GL_FIELD_IS_DECIMAL) {
                    if  (!is_numeric($co)) continue;
                    $co = CDecimal::decimal($co);
                    if (($flags & self::GL_FIELD_IS_NOT_ZERO) && $co == 0) continue; //Invalid
                } else {
                    $co = AppDB::ffwRealEscapeString($co);
                    if (($flags & self::GL_FIELD_IS_NOT_BLANK) && $co == "") continue; //Invalid
                }
                $cond .= " OR $field='$co'";
            }
            if ($cond != "")
                $cond = "WHERE " . substr($cond, 3);

            if ($table != null && ($cond != "" || ($flags & self::GL_DB_ON_BLANK_CONDITION))) {
                return AppDB::query("SELECT * FROM $table $cond");
            }
        }
        return $cond;
    }
}