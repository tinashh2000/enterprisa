<?php
namespace TariAds;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\CSlide;
use Api\Mt;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Status\CStatus;
use Helpers\HtmlHelper;
use Helpers\UserHelper;
use Helpers\PersonHelper;
use Helpers\SlideHelper;
use Api\Users\CurrentUser;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

header("Access-Control-Allow-Origin: *");
require_once("Bootstrap.php");
//require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");
if (!isset($_POST['r']) && !isset($_GET['r'])) {
	die('{"status": "Error", "message": "Resource not found"}');
	return false;
}

$postMethod = isset($_POST['r']) ? true : 0;
$rq = $postMethod ? $_POST['r'] : $_GET['r'];
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

switch ($rq) {
	case 'create':
		if ($slide = SlideHelper::fromPost()) {
			$slide->create();
		}
		break;
	case 'edit':
        if ($slide = SlideHelper::fromPost()) {
			$slide->edit();
		}
		break;

	case 'fetch':
		if (isset($_POST['start']) && isset($_POST['limit'])) {
			$start = intval($_POST['start']);
			$limit = intval($_POST['limit']);
			if ($limit >0) {
				CSlide::fetch($start, $limit);
				die();
			}
		}
		CStatus::jsonError();
		break;

	case 'get':
		if (isset($_POST['id'])) {
			CSlide::get($_POST['id']);
			die();
		}
		CStatus::jsonError();
		break;

	case 'delete':
		if (isset($_POST['id'])) {
			if (CSlide::delete($_POST['id'])) {
				CStatus::jsonSuccess("Deleted");
			}
			CStatus::jsonError();
		}
		break;
}

CStatus::jsonError("Resource not found");