<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Entities\CEntities;
use Entities\CEntity;
use Ffw\Status\CStatus;
use Api\Mt;

require_once("Bootstrap.php");
require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");

if (!isset($_POST['r']) && !isset($_GET['r'])) {
	die('{"status": "Error", "message": "Resource not found"}');
	return false;
}

require_once("Bootstrap.php");

$postMethod = isset($_POST['r']) ? true : 0;

$rq = $postMethod ? $_POST['r'] : $_GET['r'];

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

class EntityHelper {
    static function fromPost() {
        if (isset($_POST['name']) && isset($_POST['module']) && isset($_POST['classification']) && isset($_POST['type']) && isset($_POST['value']) && isset($_POST['description'])  && isset($_POST['location']) && isset($_POST['flags'])) {
            $entity = new CEntity($_POST['name'], $_POST['module'], $_POST['classification'], $_POST['type'], $_POST['value'], $_POST['description'], $_POST['flags']);
            $entity->location = $_POST['location'];
            return $entity;
        }
        return null;
    }
}

switch ($rq) {
	case 'create':
        if (($entity = EntityHelper::fromPost()) && $entity->create()) {
            CStatus::jsonSuccess("Entity Created");
            die("OK");
        }
        CStatus::jsonError(Mt::getLastError());
		break;
	case 'fetch':
		if (isset($_POST['start']) && isset($_POST['limit'])) {
			$start = intval($_POST['start']);
			$limit = intval($_POST['limit']);
			if ($limit >0) {
				CEntity::fetch($start, $limit);
				die("OK");
			}
		}
		CStatus::jsonSuccess();
		break;
	case 'edit':
		if (isset($_POST['id']) && ($entity = EntityHelper::fromPost())) {
			$entity->id = $_POST['id'];
			if ($entity->edit()) {
				CStatus::jsonSuccess("Entity Updated");
				die("OK");
			}
			CStatus::jsonError(Mt::getLastError());
		}
		CStatus::jsonError();
		break;
	case 'delete':
		if (isset($_POST['id'])) {
			if (CEntity::delete($_POST['id'])) {
				CStatus::jsonSuccess("Deleted");
				die("OK");
			}
			CStatus::jsonError();
		}
	case 'get':
		if (isset($_POST['id'])) {
			if (CEntity::get($_POST['id'])) {
				CStatus::jsonSuccess("Success");
			}
			CStatus::jsonError("Unexpected error");
		}
}
CStatus::jsonError("Resource not found");
