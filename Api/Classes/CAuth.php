<?php

namespace Api\Authentication;
use Api\AppConfig;
use Api\AppDB;
use Api\CPerson;
use Api\CPrivilege;
use Api\CSettings;
use Api\Mt;
use Api\Session\CSession;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Ffw\Crypt\CCrypt8;
use Ffw\Crypt\CCrypt9;
use Api\Roles\CRole;
use Api\Users\CUser;
require_once("CRole.php");

class CAuth {

    static function login($username, $password, $remember) {
        global $_SESSION;

        AppDB::ffwRealEscapeStringX($username);

        $q = AppDB::leftJoinX(CUser::$defaultTable, CPerson::$defaultTable, "%1.personUid=%2.uid", "%1.*, %2.*") . " WHERE " . CUser::$defaultTable . ".username='$username' OR " . CPerson::$defaultTable  . ".email='$username' LIMIT 1";
        $res = AppDB::query($q);

        if ($res && ($item = AppDB::fetchAssoc($res))) {
            if ($item["password"] == crypt($password,$item['password'])) {

                if ($item['roles'] != "") {
                    $r = CRole::getValues($item['roles']);

                    $privList = @explode(",", $r['privileges']);
                    foreach($privList as $v) {
                        if ($v == 1) {
                            $item['privileges'] |= CPrivilege::ROLE_ADMINISTRATOR;
                            break;
                        }
                    }

                    $rolesArray = @explode(";", $r['roles']);
                    $privsArray = trim($item['privilegesList']) == "" ? array() : explode(",", trim($item['privilegesList']));

                    foreach($rolesArray as $i) {
                        if (array_search($i, $privsArray) === FALSE) {
                            array_push($privsArray, $i);
                        }
                    }
                    $item['privilegesList'] = implode(",", $privsArray);
                }

                $_SESSION = array();

                CSettings::loadSession();

                CSession::set("name",$item["name"]);
                CSession::set("email", $item["email"]);
                CSession::set("username", $item["username"]);
                CSession::set("uid", $item["uid"]);
                CSession::set("regdate", $item["creationDate"]);
                CSession::set("lastUpdated", $item["lastUpdateDate"]);
                CSession::set("privileges", $item['privileges']);
                CSession::set("privilegesList", $item['privilegesList']);
                CSession::set("userType", $item['type']);
                CSession::set("logged", "world20");

                if ($remember) {
                    setcookie("username", CCrypt9::scrambleText($username), strtotime("+120 days"), Mt::$appRelDir);
                    setcookie("password", CCrypt9::scrambleText($password), strtotime("+120 days"), Mt::$appRelDir);
                }
                CUser::setLoggedIn();
                return true;
            }
        }
        $errormsg = "Invalid username or password";
        return false;
    }

    static function getUserType() {
        if (CAuth::isLoggedIn())
            return CSession::get("userType");

        return null;
    }

    static function isUserOfType($t) {
        if ($type = self::getUserType()) {
            $types = explode(",", $type);

            if (array_search($t, $types) !== FALSE)
                return true;

            return false;
        }
    }

    static function remember($username, $password) {
        setcookie("username", CCrypt9::scrambleText($username), strtotime("+120 days"), Mt::$appRelDir);
        setcookie("password", CCrypt9::scrambleText($password), strtotime("+120 days"), Mt::$appRelDir);
    }

    static function verifyUser() {

        if ($lastVerified = CSession::get("lastVerified")) {
            $dt = gmdate("Y-m-d H:i:s", strtotime($lastVerified . " + 20 minutes"));
            if (gmdate("Y-m-d H:i:s") < $dt) {	//Avoid checking each time. Instead check after 20 minutes
                return true;
            }
        }

        $username = CurrentUser::getUsername();

        $q = "SELECT lastUpdateDate FROM " . CUser::$defaultTable . " WHERE email='$username' LIMIT 1";
        if ($res = AppDB::query($q)) {
//            $pref = $mtPrefix;
            if ($item = AppDB::fetchAssoc($res)) {
                if ($item['lastUpdateDate'] == $lastVerified) {
                    CSession::set("lastVerified", date("Y:m:d H:i:s"));
                    return true;
                }

//                echo "verifyUser";
//                CSession::set("deleted", "123123123");
                CSession::delete("logged");
                header("Location:SignIn.php");
                die();
            }
        }
        return false;
    }

    static function logOut() {
		CSession::delete( array("name", "username", "logged", "reload_f") );
        $_SESSION = array();

        try {
            @setcookie("username", '', strtotime("-1 day"), Mt::$appRelDir);
            @setcookie("password", '', strtotime("-1 day"), Mt::$appRelDir);
        } catch(\Exception $e) {
            $_COOKIE = array();
        }
    }

    static function auth($username, $password) {
        AppDB::ffwRealEscapeStringX($username);
        $q = "SELECT * FROM " . CUser::$defaultTable . " WHERE email='$username' LIMIT 1";
        $res = AppDB::query($q);

        if ($item = AppDB::fetchAssoc($res)) {
			$auth = CSession::get("auth");
            if ($item["password"] == crypt($password,$item['password'])) {
                if(!is_array($auth)) {
                    $auth = array();
                }
                $c = 0;
                do {
                    $token =  $c . bin2hex(microtime() . time() . rand(11111111, 99999999) . $_SERVER['REMOTE_ADDR']);
                    $c++;
                } while (in_array($token, $auth));
                array_push($auth, $token);
				CSession::set("auth", $auth);
                die('{"status": "OK", "token": "' . $token . '"}');
                return true;
            }
        }
		die('{"status": "Error", "message": "Invalid credentials"}');
    }

    static function verifyAuth($token) {
		$auth = CSession::get("auth");

        $k = array_search($token, $auth);
        if ($k === FALSE) return false;
		unset($auth[$k]);
		CSession::set("auth", $auth);
        return true;
    }

    static function isLoggedIn() {
        if (CSession::get("logged") == 'world20' && CSession::exists("lastUpdated") ) {

            return true;
        }
        else if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {

            $a =explode('/',$_SERVER['PHP_SELF']);
            $cnt = count($a);
            array_splice($a, 0, $cnt - 3);
            $baseName = implode("/", $a);

            if ($cnt > 3) {
                if ($baseName ===  "Api/Install/Install" || $baseName ===  "Api/Install/Install.php" || class_exists("Api\Install\CInstall", false))
                    return true;
            }

            if (CAuth::login(CCrypt9::unScrambleText($_COOKIE['username']), CCrypt9::unScrambleText($_COOKIE['password']), true)) {
                if (CSession::get("logged") == 'world20' && CSession::exists("lastUpdated")) {
                    return true;
                }
            }
            CAuth::logOut();
        }
        return false;
    }

    static function isUserAdmin() {
        if (!self::isLoggedIn()) return false;
        return CPrivilege::check(CPrivilege::IS_ADMIN);
    }

    static function proceedIfLoggedIn() {

        if (CSession::get("reload_f") == 3) {
            CSession::set("reload_f", 1);	//Make sure the page refreshes and clears posted data, esp after login.
            CSession::delete("reload_f");
            header("Location:{$_SERVER['PHP_SELF']}");
            die();
        }

        $link = Mt::removePrefix($_SERVER['REQUEST_URI'], Mt::$appRelDir);

        if (self::isLoggedIn()) {
                $loggedIn = true;
                return true;
        }
		else if (CSession::get("logged") == 'locked') {
			CSession::set("signInReturnAddress", basename($_SERVER['REQUEST_URI']));
			require(Mt::$appDir . "/LockScreen.php?sIRA=" . basename($_SERVER['REQUEST_URI']));
			die();
        }
        
        die(header("Location:". Mt::$appRelDir . "/ReAuth.php?sIRA=" . $link ));

    }
}
?>
