<?php
namespace Cademia;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Ffw\Decimal\CDecimal;
use Cademia\CModule;
use Ffw\Status\CStatus;
use Cademia\CaDB;
use Cademia\CClass;
use Api\Mt;

$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null);

require_once( __DIR__ . "/../Cademia/Api/Bootstrap.php");
require_once( __DIR__ . "/../Cademia/Api/Classes/CModule.php");

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

if ($limit <= 0) return CStatus::jsonSuccess("Nothing to do");

if ($type == "table") {
    CModule::fetchN($start, $limit);
    die();
} else if ($type == "select") {
    if ($res = CaDB::query("SELECT moduleId, name FROM " . CModule::$defaultTable . " LIMIT $start, $limit")) {
        echo '{"results": [';
        $cm = "";
        while ($item = CaDB::fetchAssoc($res)) {
            echo $cm . "{\"id\":\"{$item['moduleId']}\", \"text\":\"{$item['name']} ({$item['moduleId']})\"}";
            $cm = ",";
        }
        echo ']}';
        die();
        return CStatus::jsonError("Unexpected error");
    }
    CStatus::jsonError("Request failed");
}
CStatus::jsonError("Ambiguous request");
