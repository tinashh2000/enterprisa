<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\CPerson;
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
        if ($person = PersonHelper::fromPost()) {
            if ($person->create()) {
                CStatus::jsonSuccess("Person Created");
            }
            CStatus::jsonError();
        }
        break;
    case 'edit':
        if ($person = PersonHelper::fromPost()) {
            if ($person->edit()) {
                CStatus::jsonSuccess("Person Successfully Edited");
            }
            CStatus::jsonError("Error while updating person");
        }
        break;
    case 'get':
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);
            CPerson::get($id);
            die();
        }
        break;


    case 'delete':
        if (isset($_POST['id'])) {
            if (CPerson::delete($_POST['id'])) {
                CStatus::jsonSuccess("Deleted");
            }
            CStatus::jsonError();
        }
        break;
}
CStatus::jsonError("Resource not found");

