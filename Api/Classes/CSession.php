<?php
namespace Api\Session;

use Api\Mt;
use Ffw\Crypt\CCrypt8;

class CSession {
	static $prefix;
	
	static function __setup() {
	}

	static $c = 0;
    static function get($var) {
        if (isset($_SESSION[Mt::$mtPrefix . "_{$var}"])) {
            return CCrypt8::unScrambleText($_SESSION[Mt::$mtPrefix . "_{$var}"]);
        }
        return null;
    }

    static function getArray($var) {
        if (isset($_SESSION[Mt::$mtPrefix . "_{$var}"])) {
            $a = $_SESSION[Mt::$mtPrefix . "_{$var}"];
            if (!is_array($a)) {
                return CCrypt8::unScrambleText($a);
            }
            $b = array();
            foreach($a as $key => $value) {
                $b[$key] = CCrypt8::unScrambleText($value);
            }
            return $b;
        }
        return null;
    }

    static function getValue($var, $key) {
//        echo "kk=>" . print_r($key,true);
        if (isset($_SESSION[Mt::$mtPrefix . "_{$var}"]) && isset($_SESSION[Mt::$mtPrefix . "_{$var}"][$key])) {
            return CCrypt8::unScrambleText($_SESSION[Mt::$mtPrefix . "_{$var}"][$key]);
        }
        return null;
    }

    static function findValue($var, $value) {
        if (!isset($_SESSION[Mt::$mtPrefix . "_{$var}"]) || !is_array($_SESSION[Mt::$mtPrefix . "_{$var}"])) return null;

	    foreach($_SESSION[Mt::$mtPrefix . "_{$var}"] as $k=>$v) {
//	        echo "($k => $v)";
	        if (CCrypt8::unScrambleText($v) == $value) {
	            return $k;
            }
        }
	    return null;
    }

    static function deleteValue($var, $value) {
        if (!isset($_SESSION[Mt::$mtPrefix . "_{$var}"]) || !is_array($_SESSION[Mt::$mtPrefix . "_{$var}"])) return null;

        foreach($_SESSION[Mt::$mtPrefix . "_{$var}"] as $k=>$v) {
//	        echo "($k => $v)";
            if (CCrypt8::unScrambleText($v) == $value) {
                unset($_SESSION[Mt::$mtPrefix . "_{$var}"][$k]);
                return true;
            }
        }
        return null;
    }

    static function setArray($var, $v=null, $k = null) {
        if (!isset($_SESSION[Mt::$mtPrefix . "_{$var}"]) || !is_array($_SESSION[Mt::$mtPrefix . "_{$var}"]))
            $_SESSION[Mt::$mtPrefix . "_{$var}"] = array();

        if ($v == null) return;

        if ($k == null)
            array_push($_SESSION[Mt::$mtPrefix . "_{$var}"], CCrypt8::scrambleText($v));
        else
            $_SESSION[Mt::$mtPrefix . "_{$var}"][$k] = CCrypt8::scrambleText($v);
    }

    static function set($var, $value) {
        $_SESSION[Mt::$mtPrefix . "_{$var}"] = CCrypt8::scrambleText($value);
    }

    static function setArrayX($var, $array) {
        self::delete($var); //Remove previous values
        foreach($array as $k=>$v) {
            self::setArray($var, $v, $k);
        }
    }

    static function setArrayPlain($var, $array) {
        $_SESSION[Mt::$mtPrefix . "_{$var}"] = $array;
    }

    static function getArrayPlain($var) {
        return isset($_SESSION[Mt::$mtPrefix . "_{$var}"]) ? $_SESSION[Mt::$mtPrefix . "_{$var}"] : null;
    }

    static function exists($var) {
        return isset($_SESSION[Mt::$mtPrefix . "_{$var}"]);
    }

    static function delete($var) {
        if (is_string($var) ) {
            if (isset($_SESSION[Mt::$mtPrefix . "_{$var}"]))
                unset($_SESSION[Mt::$mtPrefix . "_{$var}"]);
        } else if (is_array($var)) {
            foreach ($var as $k) {
                if (isset($_SESSION[Mt::$mtPrefix . "_{$k}"]))
                unset($_SESSION[Mt::$mtPrefix . "_{$k}"]);
            }
        }
    }

    static function getJSON($var) {
        $str = self::get($var);
        if (is_string($str)) {
            try {
                return json_decode($str, true);
            } catch(Exception $e) {}
        }
        return null;
    }

    static function setJSON($var, $ar) {
        self::set($var, json_encode($ar));
    }
}
