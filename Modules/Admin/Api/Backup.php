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
    CStatus::jsonError("Access Denied");
}

$postMethod = isset($_POST['r']) ? true : 0;
$rq = $postMethod ? $_POST['r'] : $_GET['r'];

switch ($rq) {
    case 'backup':

        if (true) {
            CBackup::backup();
            die();
        }
        break;
}
CStatus::jsonError("Resource not found");
