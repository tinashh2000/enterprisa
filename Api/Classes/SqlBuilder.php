<?php
namespace Api;

class SqlBuilder  {
    private $items;
    private $extraItems;
    private $conditions;
    private $q;

    const ADDITIONAL = 1;
    const NO_QUOTES = 2;    //Dont put quotes on value.

    const NO_BLANK  = 16; //Dont allow blank values

    const INSERT = 1;
    const UPDATE = 2;

//Connectives
    const C_NONE = "";
    const C_AND = " AND ";
    const C_OR = "  OR ";
//    const C_OR = "  OR ";

//Operators
    const O_EQ = " = ";
    const O_GT = " > ";
    const O_GTE = " >= ";
    const O_LT  = " < ";
    const O_LTE = " <= ";

    function __construct($tableName, $mode)  {
        $this->items = array();
        $this->extraItems = array();
        $this->conditions = "";
        $cm = "";
        switch ($mode) {
            case self::INSERT:
                $this->q = "INSERT INTO $tableName SET ";
                break;
            case self::UPDATE:
                $this->q = "UPDATE $tableName SET ";
                break;
            default:
                throw new \Exception("Unknown mode");
                break;
        }
    }

    static function derive($target, $source) {
        foreach($source as $k=>$v) {
            $target[$k] = $v;
        }
    }

    function addItem($q, $flags=0) {
        if (!self::ADDITIONAL)
            array_push($this->items, $q);
        else
            array_push($this->extraItems, $q);
    }

    function addN($column, $value, $flags=0) {
        if ($column == null || ($value == null && (($flags & self::NO_BLANK) || !is_string($value))))   {
            return null;
        }
        AppDB::ffwRealEscapeStringX($column);

        $quote = $flags & self::NO_QUOTES ? "":"'";

        if (!$flags && self::ADDITIONAL)
            array_push($this->items, "`$column`" . "  = {$quote}" . $value . "{$quote}");
        else
            array_push($this->extraItems, "`$column`" . "  = {$quote}" . $value . "{$quote}");
    }

    function add($column, $value, $flags=0) {
        if ($column == null || $column == "" || $value == null) return null;
        AppDB::ffwRealEscapeStringX($column);
        $quote = $flags & self::NO_QUOTES ? "":"'";
        if (!$flags & self::ADDITIONAL)
            array_push($this->items, "`$column`" . "  = $quote" . $value . "$quote");
        else
            array_push($this->extraItems, "`$column`" . "  = $quote" . $value . "$quote");
    }

    function condition($column, $value, $operator, $connective = self::NONE) {
        $this->conditions .=  "{$connective} `{$column}`{$operator}'{$value}'";
    }

    function count() {
        return count($this->items);
    }

    function get() {
        if (count($this->items) > 0) {
            $this->q .= " "   .join(",", array_merge($this->items, $this->extraItems));
            if ($this->conditions != "") {
                $this->q .= " WHERE " . $this->conditions;
            }
            return $this->q;
        }
        return null;
    }
}