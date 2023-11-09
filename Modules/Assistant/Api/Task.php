<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\CTask;
use Ffw\Status\CStatus;
use Api\Users\CurrentUser;
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
switch ($rq) {
    case 'create':
        if (isset($_POST['name']) && isset($_POST['participants']) && isset($_POST['status']) && isset($_POST['priority']) && isset($_POST['startDate']) && isset($_POST['endDate']) && isset($_POST['description'])) {

            $flags = Mt::getPostVarZ('flags');
            $position = Mt::getPostVarN("position");
            $task = new CTask($_POST['name'], $_POST['status'], $_POST['priority']);
            $task->entityId = 0;
            $task->participants = $_POST['participants'];
            $task->description = $_POST['description'];
            $task->notes = Mt::getPostVarN("notes");
            $task->startDate = $_POST['startDate'];
            $task->endDate =  $_POST['endDate'];
            $task->flags = $flags;

            if ($task->create()) {
                CStatus::jsonSuccess("Task Created");
            }
            CStatus::jsonError(Mt::getLastError());
        }
        print_r($_POST);
        break;
    case 'edit':
        if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['participants']) && isset($_POST['status']) && isset($_POST['priority']) && isset($_POST['startDate']) && isset($_POST['endDate']) && isset($_POST['description'])) {

            $flags = Mt::getPostVarZ('flags');
            $task = new CTask($_POST['name'], $_POST['participants'], $_POST['status'], $_POST['startDate'], $_POST['endDate'], $_POST['description']);

            $flags = Mt::getPostVarZ('flags');
            $position = Mt::getPostVarN("position");
            $task = new CTask($_POST['name'], $_POST['status'], $_POST['priority']);
            $task->entityId = 0;
            $task->position = $position;
            $task->participants = $_POST['participants'];
            $task->description = $_POST['description'];
            $task->notes = Mt::getPostVarN("notes");
            $task->startDate = $_POST['startDate'];
            $task->endDate =  $_POST['endDate'];
            $task->flags = $flags;

            if ($task->edit()) {
                CStatus::jsonSuccess("Task Created");
            }
            return CStatus::jsonError(Mt::getLastError());
        }
        break;
    case 'setStatus':
        if (isset($_POST['id']) && (isset($_POST['status']) || isset($_POST['progress']))) {
            CTask::setStatus($_POST['id'], $_POST['status'] ?? null, $_POST['progress'] ?? null );
            die();
        }
        break;
    case 'fetch':
        if (isset($_POST['start']) && isset($_POST['limit'])) {
            $start = intval($_POST['start']);
            $limit = intval($_POST['limit']);
            if ($limit >0) {
                CTask::fetch($start, $limit);
                die();
            }
        }
        CStatus::jsonSuccess();
        break;
    case 'delete':
        if (isset($_POST['id'])) {
            if (CTask::delete($_POST['id'])) {
                CStatus::jsonSuccess("Deleted");
            }
            CStatus::jsonError();
        }
    case 'get':
        if (isset($_POST['id'])) {
            CTask::getTask($_POST['id']);
            CStatus::jsonError("Unexpected error");
        }
}
CStatus::jsonError("Resource not found");

?>
