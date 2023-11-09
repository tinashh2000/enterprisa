<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Currencies\CCurrency;
use Ffw\Status\CStatus;
use Api\Mt;
use Helpers\CurrencyHelper;

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

		if ($currency = CurrencyHelper::fromPost()) {
			if ($currency->create()) {
				CStatus::jsonSuccess("Currency Created");
			}
			CStatus::jsonError(Mt::getLastError());
		}
		break;
	case 'edit':
		if ($currency = CurrencyHelper::fromPost()) {
			if ($currency->edit()) {
				CStatus::jsonSuccess("Currency Updated");
			}
			CStatus::jsonError(Mt::getLastError());
		}
		CStatus::jsonError();
		break;
	case 'fetch':
		if (isset($_POST['start']) && isset($_POST['limit'])) {
			$start = intval($_POST['start']);
			$limit = intval($_POST['limit']);
			if ($limit >0) {
				CCurrency::fetch($start, $limit);
				die();
			}
		}
		CStatus::jsonSuccess();
		break;
	case 'delete':
		if (isset($_POST['id'])) {
			if (CCurrency::delete($_POST['id'])) {
				CStatus::jsonSuccess("Deleted");
			}
			CStatus::jsonError();
		}
	case 'get':
		if (isset($_POST['id'])) {
			CCurrency::get($_POST['id']);
			CStatus::jsonError("Unexpected error");
		}
}
CStatus::jsonError("Resource not found");

?>
