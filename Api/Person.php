<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\CPerson;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Users\CurrentUser;
use Ffw\Status\CStatus;
use Helpers\PersonHelper;
use Api\Mt;

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

        if (!CPrivilege::checkList(CEnterprisa::PEOPLE_CREATE) && CurrentUser::getUid() != $this->uid)
            return CStatus::jsonError("Access Denied");

        if ($person = PersonHelper::fromPost()) {
            if ($person->create()) {
                CStatus::jsonSuccess("Contact Created");
            }
            CStatus::jsonError();
        }
        break;
    case 'edit':

        if (!CPrivilege::checkList(CEnterprisa::PEOPLE_WRITE) && CurrentUser::getUid() != $this->uid)
            return CStatus::jsonError("Access Denied");

        if ($person = PersonHelper::fromPost()) {
            if ($person->edit()) {
                CStatus::jsonSuccess("Contact Successfully Edited");
            }
            CStatus::jsonError("Error while updating contact");
        }
        break;
    case 'get':
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);
            CPerson::get($id);
            die();
        }
        break;

    case 'search':
        if (isset($_POST['query'])) {
            CPerson::search($_POST['query']);
            die();
        }
        break;

    case 'getByEmail':
        if (isset($_POST['query'])) {
            CPerson::getByEmail($_POST['query']);
            die();
        }
        break;

    case 'getByName':
        if (isset($_POST['query'])) {
            CPerson::getByName($_POST['query']);
            die();
        }
        break;

    case 'delete':

        if (!CPrivilege::checkList(CEnterprisa::PEOPLE_DELETE))
            return CStatus::jsonError("Access Denied");

        if (isset($_POST['id'])) {
            if (CPerson::delete($_POST['id'])) {
                CStatus::jsonSuccess("Deleted");
            }
            CStatus::jsonError();
        }
        break;
}
CStatus::jsonError("Resource not found");

