<?php
namespace Entities\Categories;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Mt;
use Api\Session\CSession;
use Ffw\Decimal\CDecimal;
use Entities\Categories\CCategory;
use Ffw\Status\CStatus;


$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null);
$entities = Mt::getParam('entities', null); //isset($_GET['entities']) ? trim($_GET['entities']) : "";

require_once( __DIR__ . "/../Entities/Api/Bootstrap.php");
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

switch ($type) {
    case "table":
        CCategory::fetchN($start, $limit, $entities);
        die();
    case "select":
        if ($res = CCategory::customQuery($start, $limit, $entities)) {
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
