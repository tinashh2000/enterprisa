<?php
namespace Api;

use Api\Session\CSession;

class CPrivilege {
    const CREATE = 1;
    const DELETE = 2;
	const READ   = 3;
	const MODIFY = 4;
	const WRITE = self::MODIFY;
	const PROCESS = 5;
	const ALL    = 9;
	const CUSTOM = 20;

	const CREATE_USER	=	1 << 1;
	const DELETE_USER	=	1 << 2;
	const VIEW_USER		=	1 << 3;
	const ALTER_USER	=	1 << 4;
	const ALL_USER		=	CPrivilege::CREATE_USER | CPrivilege::DELETE_USER | CPrivilege::VIEW_USER | CPrivilege::ALTER_USER;
    const IS_ADMIN		=	1 << 40;

	const ROLE_ADMINISTRATOR    = 9223372036854775807;
    const ROLE_STANDARD         = 0;

	const L_CREATE_USER = 1;
	const L_DELETE_USER = 2;
	const L_VIEW_USER = 3;
	const L_ALTER_USER = 4;
	const L_ALL_USER = 9;

//	const L_CREATE_USER = 1;
//	const L_DELETE_USER = 2;
//	const L_VIEW_USER = 3;
//	const L_ALTER_USER = 4;
//	const L_ALL_USER = 9;

    static $privilegesList = null;

    static function addPrivilegesList($privilegesArray) {
        array_push(self::$privilegesList, $privilegesArray);
    }

    static function verifyPrivilege($p) {
        if (self::checkList($p)) return true;
        require(Mt::$appDir . "/404.php");
        die();
    }
    static function clearPrivilegesList() {
        self::$privilegesList = array();
    }

    static function getPrivilegesList() {
        if (self::$privilegesList === null) {
            self::$privilegesList = array();
            try {
                if (!$hf = fopen(Mt::$appDir . "/Modules/enterprisa", "r")) throw new \Exception("File reading error");
                while(!feof($hf)) {
                    $m  = trim(fgets($hf));
                    $mDir = Mt::$appDir . "/Modules/$m";
                    $privFile = $mDir . "/Api/Privileges.php";
                    if (is_file($privFile)) {
                        require_once ($privFile);
                    }
                }
                fclose($hf);
                return self::getPrivilegesList();
            } catch(\Exception $e) {

            }
        }
        return self::$privilegesList;
    }

    static function isAdmin() {
        if (class_exists("Api\Install\CInstall", false)) return true;
        $priv = CSession::get("privileges");
        if ($priv == self::ROLE_ADMINISTRATOR)
            return true;

        return false;
    }

    static function check($privilege) {
        if (class_exists("Api\Install\CInstall", false)) return true;   //When installing always return true

        if ($privilege == self::VIEW_USER) return true;

        $priv = CSession::get("privileges");
        return ($priv & $privilege == $privilege);
    }

    static function checkList($privilege) {

        if (class_exists("Api\Install\CInstall", false)) return true;


        $priv = CSession::get("privileges");

        if ($priv == self::ROLE_ADMINISTRATOR)
            return true;

        if ($privilege < 63) {
            if ($privilege == self::L_ALL_USER)
                return ($priv & self::ALL_USER) == self::ALL_USER;

            return ($priv & 1 << $privilege) > 0;
        }

        $priv = CSession::get("privilegesList");

        $privs = explode(",", $priv);
        if (array_search($privilege, $privs) !== FALSE)  {
            return true;
        }

        $privW = $privilege % 100;

        if ($privW != 9) {
            $privW = ($privilege - $privW) + 9;

            if (array_search($privW, $privs) !== FALSE)  {
                return true;
            }
        }

        return false;
    }

    /* Check if current user has privileges which start at $base and end at $base + 1000 */
    static function checkBase($base) {
        if (class_exists("Api\Install\CInstall", false)) return true;

        $priv = CSession::get("privileges");

        if ($priv == self::ROLE_ADMINISTRATOR)
            return true;

        $priv = CSession::get("privilegesList");
        if ($priv != null) {
            $privs = explode(",", $priv);
            foreach ($privs as $p) {
                if ($p >= $base && $p < $base + 1000) return true;
            }
        }
        return false;
    }
}
