<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Api\Users\CUser;
use Ffw\Status\CStatus;
use Api\Users\CurrentUser;



require_once("Bootstrap.php");
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);
if (!isset($_POST['r']) && !isset($_GET['r'])) {
	CStatus::jsonError("Resource not found");
	return false;
}

require_once("Classes/CAuth.php");
require_once("Classes/CUser.php");
require_once("Classes/CRole.php");

$postMethod = isset($_POST['r']) ? true : 0;

$rq = $postMethod ? $_POST['r'] : $_GET['r'];

switch ($rq) {
	case 'signIn':
	case 'login':
		if (!isset($_POST['username']) || !isset($_POST['password'])) {
			CStatus::jsonError("Resource not found");
		}

		if ($res = CAuth::login($_POST['username'], $_POST['password'], true)) {
			$userprofile =	'"name" : "' . CSession::get("name") . '",' .
							'"privileges" : "' . CSession::get("privileges") . '",' .
							'"username" : "' . CSession::get("privileges") . '"';
            $retLink = Mt::$appRelDir . Mt::getGetVarZ("sIRA", "/Home");

			die('{"status": "OK", "message": "Hi,'. CurrentUser::getFullname() . '", "profile":{' . $userprofile . '}, "return": "' . $retLink . '"}');
		}
		CStatus::jsonError("Invalid username or password");
		break;
	case 'signUp':
	    if (isset($_POST['fullname']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['address']) && isset($_POST['password'])) {
	        $person = new CPerson($_POST['fullname'], $_POST['email'], 1, 0, $_POST['phone'], "user", "", $_POST['address'], "", "", "", 0);

	        $user = new CUser($_POST['username'], $_POST['password'], 0, "", 0);
	        $person->user = $user;

	        if ($person->create()) {
                return CStatus::jsonSuccess("User successfully created");
            } else {
	            return CStatus::jsonError("User successfully created");
            }

        }
	    break;
	case 'signOut':
	case 'logout':
		logOut();
		die('{"status": "OK", "message": "Done!"}');
		break;
	case 'authorize':
		if (!isset($_POST['username'], $_POST['password'])) break;
		CUser::auth($_POST['username'], $_POST['password']);
		break;
}
CStatus::jsonError("Resource not found");
