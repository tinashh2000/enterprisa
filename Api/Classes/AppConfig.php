<?php

namespace Api;

use Companies\CCompany;
use Ffw\Crypt\CCrypt8;
use Ffw\Decimal\CDecimal;
use Paynow\Payments\Payment;

class Mt {
	static $db = null;
	static $database = null;
	static $dbPrefix = "";
	static $mtPrefix  = "mt_enterprisa";
	static $appDir;
	static $appRoot;
	static $appRel = "enterprisa";
	static $appRelDir;
	static $isMaster;   //Indicates whether this is the main system or the remote one
	static $databaseName = "enterprisa";
	static $defaultUser = "admin@enterprisa.co.zw";
	static $defaultName = "Administrator";
	static $dbRootPath;
	static $dataPath;
	static $doReload=false;
	static $loggedIn=false;
	static $defaultDatabase;
	static $errors = array();
    static $modulePrivilegesStart = 5000;
    static $moduleMaxPrivileges = 10000;

	static function setError($err) {
		array_push(self::$errors, $err);
	}

	static function getErrors() {
		return self::$errors;
	}

	static function getLastError() {
		return count(self::$errors) > 0 ? self::$errors[count(self::$errors) -1] : "";
	}

	static function getRelativePath() {
        $curPage = pathinfo($_SERVER['PHP_SELF'])["dirname"];
        $s = strrpos($curPage, "/");
        $curPage = substr($curPage, 0, $s);
        $s = strrpos($curPage, "/");
        $curPage = substr($curPage, 0, $s);
    }

    static function createUniqueFile($path, $id) : array {
        if (!is_dir($path)) mkdir($path, 0777);
        $c = 0;
        do {
            $unique = static::generateUid($id);
            $filename = "{$path}/" . $unique;
        } while (file_exists($filename) && $c++ > 100);

        $hf = fopen($filename, "w");
        return ["handle" => $hf, "name" => $unique];
    }

    static function generateUid($id) {
	    try {
            return CCrypt8::scrambleNumber(rand(100, 999) . $id) . "_" . CCrypt8::scrambleNumber(rand(10000,99999) . strtotime("now")) . "_" . bin2hex(openssl_random_pseudo_bytes(9));
        } catch (\Exception $e) {
        }
        return CCrypt8::scrambleNumber(rand(10000, 99999) . $id) . "_" . CCrypt8::scrambleNumber(strtotime("now")) . "_" . $bytes = bin2hex(openssl_random_pseudo_bytes(9));
    }

	static function __setup() {
        self::$dbPrefix = defined('Api\dbPrefix') ? dbPrefix : "";
        self::$appRoot = $_SERVER['DOCUMENT_ROOT'];
		$basePath = str_replace("\\", "/", realpath(__DIR__ . "/../../"));
        $rootPath = str_replace("\\", "/",realpath(self::$appRoot));

        if (($p = strpos($basePath, $rootPath)) !== false) {
            if ($p == 0) {
                Mt::$appRelDir = substr($basePath, strlen($rootPath));
            }
        } else {
            Mt::$appRelDir = parentPath;
        }
        Mt::$appRel = substr(Mt::$appRelDir,0, 1) == "/" ? substr(Mt::$appRelDir,1) : Mt::$appRelDir;
        Mt::$appDir = Mt::$appRoot . (Mt::$appRel == "" ? "" : "/" . Mt::$appRel) ;
        Mt::$dbRootPath = Mt::$appDir . "/res/private/employee/db";
        Mt::$dataPath = Mt::$appDir . "/Data/Private";
	}

    static function getPostVar($v) {
        return isset($_POST[$v]) ? $_POST[$v] : "";
    }

    static function getPostVarN($v) {
        return isset($_POST[$v]) ? $_POST[$v] : null;
    }

    static function getPostVarZ($v, $def = 0) {
        return isset($_POST[$v]) ? $_POST[$v] : $def;
    }

    static function getGetVar($v) {
        return isset($_GET[$v]) ? $_GET[$v] : "";
    }

    static function getGetVarN($v) {
        return isset($_GET[$v]) ? $_GET[$v] : null;
    }

    static function getGetVarZ($v, $def = 0) {
        return isset($_GET[$v]) ? $_GET[$v] : $def;
    }

    static function getParam($v, $def = "") {
        return isset($_GET[$v]) ? $_GET[$v] : (isset($_POST[$v]) ? $_POST[$v] :$def);
    }

    static function tableName($name) {
		return self::$dbPrefix . "$name";
	}

	static function dataPath($name) {
	    return self::$dataPath . "/$name";
    }

    public static function removePrefix($str, $prefix)
    {
        $plen = strlen($prefix);
        if (substr($str,0, $plen) == $prefix) {
            $str = substr($str, $plen);
        }
        return $str;
    }

    public static function getReturnAddress() {

	    if (isset($_GET['sIRA']))
	        return "?sIRA={$_GET['sIRA']}"; // $_GET['sIRA'];

	    $addr = basename($_SERVER['REQUEST_URI']);
        $link = Mt::removePrefix($addr, Mt::$appRelDir);

        if (array_search($addr, ["/Login", "/SignIn", "/SignOut"]) == FALSE) {
            return "?sIRA={$link}";
        }

	    return "Home";

    }

    public static function caps($str)
    {
        return strtoupper(substr($str, 0 ,1)) . substr($str, 1);
    }

    public static function printR($array, $level=0, $after='<br>', $before='') {
        echo "{$before}";
        foreach($array as $k => $v) {
            echo "[$k]=>";
            if (is_array($v) || is_object($v)) {
                echo is_array($v) ? " Array(" : "Object(";
                self::printR($v, $level++, ")" . $after,  $before);
            } else{
                echo $v;
            }
        }
        echo $after;
    }

}

Mt::__setup();
