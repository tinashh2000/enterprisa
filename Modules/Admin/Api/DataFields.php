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
use Inventory\Products\CPackageFields;
use Inventory\Products\CPackage;
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
    case 'update':
        if (isset($_POST['data'])) {
            $data = json_decode($_POST['data'], true);
            return CPackage::updateDataFields($data);
        }
        break;

}
CStatus::jsonError("Resource not found");

