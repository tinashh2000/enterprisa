<?php
namespace Api;

use Api\Roles\CRole;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Api\Mt;
use Api\Authentication\CAuth;
require_once (Mt::$appDir . "/Api/Classes/CRole.php");

$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null);

//require_once( __DIR__ . "/../Inventory/Api/Bootstrap.php" );
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

if (!CAuth::isLoggedIn()) return CStatus::jsonError("Login first");

if ($limit <= 0) return CStatus::jsonSuccess("Nothing to do");

if ($type == "table") {
    CRole::fetchN($start, $limit);
    die();
} else if ($type == "select") {
    if ($res = AppDB::query("SELECT id, name FROM " . CRole::$defaultTable . " LIMIT $start, $limit")) {
        echo '{"results": [';
        $cm = "";
        while ($item = AppDB::fetchAssoc($res))
        {
            echo $cm . "{\"id\":\"{$item['id']}\", \"text\":\"{$item['name']}\"}";
            $cm = ",";
        }
        echo ']}';
        die();
    }
    return CStatus::jsonError("Unexpected error");
}
CStatus::jsonError("Ambiguous request");
