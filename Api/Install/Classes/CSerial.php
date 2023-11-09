<?php


namespace Api\Install;

use Ffw\Crypt\CCrypt9;
use Ffw\Crypt\CCrypt8;
use Api\CSimpleCrypt;
use Api\Mt;

require_once (__DIR__ . "/../../Bootstrap.php");
//require_once (Mt::$appRelDir . "/Api/Classes/CSimpleCrypt.php");

class CSerial
{
    static function grandValidate($serial) {
        return true;
        if (self::validate($serial)) {
            return true;
        }
        return false;
    }

    static function validate($serial) {
			return true;
        if (!isset($_SERVER['REQUEST_SCHEME']) || $_SERVER['REQUEST_SCHEME'] == "http" || !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != "on")
            return false;

        $key = CSimpleCrypt::decrypt($serial);
        $key = CCrypt8::unScrambleText(CCrypt9::unScrambleText($key));
        $cipher = hex2bin($key);
//        echo $cipher;
//        echo "<br>".$_SERVER['HTTP_HOST'];
//
//            echo $_SERVER['REQUEST_SCHEME'];
//        die();


        if ($cipher == crypt($_SERVER['HTTP_HOST'], $cipher))
            return true;

        return false;
    }
}
