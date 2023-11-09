<?php
namespace Api;

use Api\Assistant\CNotification;
use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Roles\CRole;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Status\CStatus;
use Api\CAssistant;
use Api\Users\CUser;
use Api\Mt;

require_once("Bootstrap.php");
require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");

if (!isset($_POST['r']) && !isset($_GET['r'])) {
    die('{"status": "Error", "message": "Resource not found"}');
    return false;
}

AppDB::disableLogs();

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

if ($lastPing = CSession::get("lastPinged")) {
    if ((strtotime("now") - $lastPing) >= 60) {
        CUser::setLastPinged();
        CSession::set("lastPinged", strtotime("now"));
    }
}
else {
    CSession::set("lastPinged", strtotime("now"));
}

switch ($_POST['r']) {
    case "getStats":
        CAssistant::getStats();
        die();
    case "checkAndGet":
        if ($_POST['lastNotifications'] && $_POST['lastMessages']) {
            CAssistant::getUpdates();
            die();
        }
        break;
    case "getNotifications":
        if (isset($_POST['ts'])) {
            CNotification::fetch(0, 10, $_POST['ts']);
            die();
        }
    case "getMessages":
        if (isset($_POST['ts'])) {
            CMessage::fetchUni(0, 10, true, $_POST['ts']);
            die();
        }
}
print_r($_POST);
die('{"status": "Error", "message": "Resource not found"}');
