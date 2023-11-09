<?php
namespace Companies;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\CPerson;
use Api\Mt;
use Api\Session\CSession;
use Companies\CCompany;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;



$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null);

require_once( __DIR__ . "/../Companies/Api/Bootstrap.php" );
require_once( __DIR__ . "/../Companies/Api/Classes/CCompany.php" );
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

if ($limit <= 0) return CStatus::jsonSuccess("Nothing to do");

if ($type == "table") {
    CCompany::fetchN($start, $limit);
    die();
} else if ($type == "select") {
    $q = AppDB::leftJoinX(CCompany::$defaultTable, CPerson::$defaultTable, "%1.personId=%2.id", "%2.name,%1.id") . " LIMIT $start, $limit";
    if ($res = AppDB::query($q)) {
        echo '{"results": [';
        $cm = "";
        while ($item = AppDB::fetchAssoc($res))
        {
            echo $cm . "{\"id\":{$item['id']}, \"text\":\"{$item['name']}\"}";
            $cm = ",";
        }
        echo ']}';
        die();
    }
    return CStatus::jsonError("Unexpected error");
}
CStatus::jsonError("Ambiguous request");
