<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Roles\CRole;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Status\CStatus;
use Api\Mt;

require_once("Bootstrap.php");
require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");

if (!isset($_POST['r']) && !isset($_GET['r'])) {
	die('{"status": "Error", "message": "Resource not found"}');
	return false;
}

require_once ("Classes/CRole.php");

$postMethod = isset($_POST['r']) ? true : 0;
$rq = $postMethod ? $_POST['r'] : $_GET['r'];
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

switch ($rq) {
	case 'create':

		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		if (isset($_POST['flags']) && isset($_POST['name']) && isset($_POST['privileges']) && isset($_POST['privilegesList']) && isset($_POST['description'])) {
			$role = new CRole($_POST['name'], $_POST['privileges'], $_POST['privilegesList'], $_POST['description'], 0);
			$role->create();
			die();
		}
		break;
	case 'edit':
		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		if (isset($_POST['flags']) && isset($_POST['name']) && isset($_POST['privileges']) && isset($_POST['privilegesList']) && isset($_POST['description'])) {
			$role = new CRole($_POST['name'], $_POST['privileges'], $_POST['privilegesList'], $_POST['description'], 0);
			$role->id = $_POST['id'];

			$role->edit();
		}
		break;
	case 'fetch':
		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		if (isset($_POST['start']) && isset($_POST['limit'])) {
			$start = intval($_POST['start']);
			$limit = intval($_POST['limit']);
			if ($limit >0) {
				CRole::fetch($start, $limit);
				die();
			}
		}
		CStatus::jsonError();
		break;

	case 'get':

		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		if (isset($_POST['id'])) {
			CRole::get($_POST['id']);
			die();
		}
		CStatus::jsonError();
		break;


	case 'delete':

		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		if (isset($_POST['id'])) {
			if (CRole::delete($_POST['id'])) {
				CStatus::jsonSuccess("Deleted");
			}
			CStatus::jsonError();
		}
		break;
}
CStatus::jsonError("Resource not found");

?>
