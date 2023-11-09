<?php
namespace Accounta;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\CPrivilege;
use Api\Mt;
use Api\Roles\CRole;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Crypt\CCrypt9;
use Ffw\Status\CStatus;
use Api\CSettings;

require_once("Bootstrap.php");
require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

if (!isset($_POST['r']) && !isset($_GET['r'])) {
    CStatus::jsonError("Resource not found");
    return false;
}

if (!CPrivilege::isAdmin()) {
    return CStatus::jsonError("Access Denied");
}

$postMethod = isset($_POST['r']) ? true : 0;
$rq = $postMethod ? $_POST['r'] : $_GET['r'];

switch ($rq) {
    case 'set':
    case 'setEmailSettings':

        if (isset($_POST['outgoingServer']) || isset($_POST['outgoingSecurity']) || isset($_POST['outgoingDelivery']) || isset($_POST['outgoingPort']) || isset($_POST['username']) || isset($_POST['password'])) {

            $set["serviceProvider"] = $_POST["serviceProvider"];
            $set["displayName"] = $_POST["name"];
            $set["incomingServer"] = Mt::getPostVar("incomingServer");
            $set["incomingSecurity"] = Mt::getPostVar("incomingSecurity");
            $set["incomingDelivery"] = Mt::getPostVar("incomingDelivery");
            $set["incomingPort"] = Mt::getPostVar("incomingPort");
            $set["outgoingServer"] = $_POST["outgoingServer"];
            $set["outgoingSecurity"] = $_POST["outgoingSecurity"];
            $set["outgoingDelivery"] = $_POST["outgoingDelivery"];
            $set["outgoingPort"] = $_POST["outgoingPort"];
            $set["username"] = $_POST["username"];
            $set["password"] = $_POST["password"];

            $settings = new CSettings("Automailer", CCrypt9::scrambleText(json_encode($set)), "Email Settings", 0);
            if ($settings->set())

                return CStatus::jsonSuccess("Operation completed");

            return CStatus::jsonError("Operation failed");

        }
        break;
}
CStatus::jsonError("Resource not found");
