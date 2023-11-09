<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\CPerson;
use Ffw\Status\CStatus;
use Api\Mt;

require_once("Bootstrap.php");
require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");

if (!isset($_POST['r']) && !isset($_GET['r'])) {
    die('{"status": "Error", "event": "Resource not found"}');
    return false;
}

require_once("Classes/CEvent.php");

$postMethod = isset($_POST['r']) ? true : 0;

$rq = $postMethod ? $_POST['r'] : $_GET['r'];

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

switch ($rq) {
    case 'create':
        if (isset($_POST['name']) &&  isset($_POST['message']) &&  isset($_POST['description']) &&  isset($_POST['notes']) &&  isset($_POST['visibility']) &&  isset($_POST['startDate']) &&  isset($_POST['endDate']) &&  isset($_POST['venue']) &&  isset($_POST['location']) &&  isset($_POST['flags'])) {
            $flags = $_POST['flags'];
            $event = new CEvent($_POST['name'], $_POST['message'], $_POST['description'], $_POST['notes'], $_POST['visibility'], $_POST['startDate'], $_POST['endDate'], $flags);
            $event->venue = $_POST['venue'];
            $event->location = $_POST['location'];
            if ($event->create()) {
                CStatus::jsonSuccess("Event Created");
            }
            CStatus::jsonError();
        }
        break;
    case 'edit':
        if (isset($_POST['id']) && isset($_POST['name']) &&  isset($_POST['message']) &&  isset($_POST['description']) &&  isset($_POST['notes']) &&  isset($_POST['visibility']) &&  isset($_POST['startDate']) &&  isset($_POST['endDate']) &&  isset($_POST['venue']) &&  isset($_POST['location']) &&  isset($_POST['flags'])) {
            $flags = $_POST['flags'];
            $event = new CEvent($_POST['name'], $_POST['message'], $_POST['description'], $_POST['notes'], $_POST['visibility'], $_POST['startDate'], $_POST['endDate'], $flags);
            $event->id = $_POST['id'];
            $event->venue = $_POST['venue'];
            $event->location = $_POST['location'];
            if ($event->edit()) {
                CStatus::jsonSuccess("Event Successfully Edited");
            }
            CStatus::jsonError("Error while updating event");
        }
        break;

    case 'get':
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);
            CEvent::get($id);
            die();
        }
        break;

    case 'fetch':
        if (isset($_POST['start']) && isset($_POST['limit']) && isset($_POST['startDate']) && isset($_POST['endDate'])) {
            $start = intval($_POST['start']);
            $limit = intval($_POST['limit']);

            if ($limit >0) {
                CEvent::fetch($start, $limit);
                die();
            }
            CStatus::jsonError();
        }
        break;

    case 'delete':
        if (isset($_POST['id'])) {
            if (CEvent::delete($_POST['id'])) {
                CStatus::jsonSuccess("Deleted");
            }
            CStatus::jsonError();
        }
        break;
}
CStatus::jsonError("Resource not found");

