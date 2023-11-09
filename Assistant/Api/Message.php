<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Status\CStatus;
use Api\Users\CurrentUser;
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
        if (isset($_POST['recipients']) && isset($_POST['message']) && isset($_POST['subject'])) {
            $msg = new CMessage($_POST['subject'], $_POST['message'], $_POST['recipients']);
            $msg->from = CurrentUser::getUsername();
            $msg->categories = Mt::getPostVar('categories');
            $msg->types = Mt::getPostVar('types');
            $msg->flags = (Mt::getPostVar("sendInternal") == "on" ? CMessage::FLAG_INTERNAL : 0) | (Mt::getPostVar("sendEmail") == "on" ? CMessage::FLAG_EMAIL : 0);

            //print_r($msg);

            if ($msg->send()) {
                CStatus::jsonSuccess("Message(s) Sent");
            }
            CStatus::jsonError();
        }
        break;
    case 'fetch':
        if (isset($_POST['start']) && isset($_POST['limit'])) {
            $start = intval($_POST['start']);
            $limit = intval($_POST['limit']);
            $box = isset($_POST['box']) ? $_POST['box'] : "inbox";

            if ($limit >0) {
                CMessage::fetch($start, $limit, $box);
                die();
            }
            CStatus::jsonError();
        }
        break;

    case 'fetchHeaders':
        if (isset($_POST['start']) && isset($_POST['limit'])) {
            $start = intval($_POST['start']);
            $limit = intval($_POST['limit']);
            $box = isset($_POST['box']) ? $_POST['box'] : "inbox";
            if ($limit >0) {
                CMessage::fetchHeaders($start, $limit);
                die();
            }
            CStatus::jsonError();
        }
        break;
    case 'getUnreadCount':
        $box = isset($_POST['box']) ? $_POST['box'] : "inbox";
        CMessage::unReadCount($box);
        die();
        break;
    case 'get':
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $box = isset($_POST['box']) ? $_POST['box'] : "inbox";

            CMessage::get($id, $box);
            die();
        }
        CStatus::jsonError();
        break;


    case 'getUserMessages':
        if (isset($_POST['user'])) {

            $box = isset($_POST['box']) ? $_POST['box'] : "inbox";

            CMessage::getUserMessages($_POST['user']);
            die();
        }
        CStatus::jsonError();
        break;


    case 'delete':
        if (isset($_POST['id'])) {
            if (CMessage::delete($_POST['id'])) {
                CStatus::jsonSuccess("Deleted");
            }
            CStatus::jsonError();
        }
        break;

    case 'setEmailSettings':

        if (isset($_POST['incomingServer']) || isset($_POST['incomingSecurity']) || isset($_POST['incomingDelivery']) || isset($_POST['incomingPort']) || isset($_POST['incomingServer']) || isset($_POST['incomingSecurity']) || isset($_POST['incomingDelivery']) || isset($_POST['incomingPort']) || isset($_POST['username']) || isset($_POST['password'])) {

            $set["incomingServer"] = $_POST["incomingServer"];
            $set["incomingSecurity"] = $_POST["incomingSecurity"];
            $set["incomingDelivery"] = $_POST["incomingDelivery"];
            $set["incomingPort"] = $_POST["incomingPort"];
            $set["outgoingServer"] = $_POST["outgoingServer"];
            $set["outgoingSecurity"] = $_POST["outgoingSecurity"];
            $set["outgoingDelivery"] = $_POST["outgoingDelivery"];
            $set["outgoingPort"] = $_POST["outgoingPort"];
            $set["username"] = $_POST["username"];
            $set["password"] = $_POST["password"];

            CAssistant::setEmailSettings($set);
            CStatus::jsonSuccess("Operation completed");
            die("Done");
        }
        break;

}
CStatus::jsonError("Resource not found");

