<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Mt;
use Api\Users\CUser;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Status\CStatus;
use Helpers\HtmlHelper;
use Helpers\UserHelper;
use Helpers\PersonHelper;
use Api\Users\CurrentUser;

require_once("Bootstrap.php");
require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");
if (!isset($_POST['r']) && !isset($_GET['r'])) {
	die('{"status": "Error", "message": "Resource not found"}');
	return false;
}

$postMethod = isset($_POST['r']) ? true : 0;

$rq = $postMethod ? $_POST['r'] : $_GET['r'];

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

switch ($rq) {
	case 'create':

        if (!CPrivilege::checkList(CPrivilege::CREATE_USER))
            return CStatus::jsonError("Access Denied");

        if ($person = PersonHelper::fromPost(['needsUser' => true])) {
			$person->create((Mt::getPostVar('sendActivationEmail') == "on"));
		}

		break;
	case 'edit':


        if ($person = PersonHelper::fromPost(['needsUser' => true])) {
            if (!CPrivilege::checkList(CPrivilege::ALTER_USER) && CurrentUser::getUid() != $person->uid)
                return CStatus::jsonError("You are not allowed to modify this record");

			$person->edit();
		}
		break;
	case 'fetch':

//        print_r($_SERVER);

//        if (!CPrivilege::checkList(CPrivilege::VIEW_USER))
//            return CStatus::jsonError("Access Denied");

        if (isset($_POST['start']) && isset($_POST['limit'])) {
			$start = intval($_POST['start']);
			$limit = intval($_POST['limit']);
			if ($limit >0) {
				CUser::fetch($start, $limit);
				die();
			}
		}
		CStatus::jsonError();
		break;

	case 'get':

        if (!CPrivilege::checkList(CPrivilege::VIEW_USER)) {
            if ($_POST['username'] != "me" && CurrentUser::getUsername() != $_POST['username'])
                return CStatus::jsonError("Access Denied");
        }
        if (isset($_POST['username'])) {
			CUser::get($_POST['username']);
			die();
		}
		CStatus::jsonError();
		break;


	case 'delete':

        if (!CPrivilege::checkList(CPrivilege::DELETE_USER))
            return CStatus::jsonError("Access Denied");

        if (isset($_POST['id'])) {
			if (CUser::delete($_POST['id'])) {
				CStatus::jsonSuccess("Deleted");
			}
			CStatus::jsonError();
		}
		break;

}
CStatus::jsonError("Resource not found");

