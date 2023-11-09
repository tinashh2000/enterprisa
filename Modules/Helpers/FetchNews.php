<?php
namespace Api;

use Api\AppDB;
use Api\Assistant\CNotification;
use Api\Authentication\CAuth;
use Api\Session\CSession;
use Api\Users\CurrentUser;
use Crm\Customers\CCustomer;
use Ffw\Decimal\CDecimal;
use Inventory\CInventory;
use Inventory\Products\CPackage;
use Inventory\Products\CBaseProduct;
use Ffw\Status\CStatus;
use Api\CTask;
use Api\Mt;
use Crm\Sales\CSale;
use Modules\CModule;

$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', "position");
$sortOrder = Mt::getParam('order', "ascending");

require_once( __DIR__ . "/../Crm/Api/Bootstrap.php" );

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

if (!CAuth::isLoggedIn()) return CStatus::jsonError("Login first");

if ($limit <= 0) return CStatus::jsonSuccess("Nothing to do");

CModule::use_module("Inventory");
$currentUser = CurrentUser::getUsername();

if ($type == "table") {
    CNotification::fetch(0,10,"1980-01-01 00:00:00","Crm");
    die();
}
CStatus::jsonError("Ambiguous request");
