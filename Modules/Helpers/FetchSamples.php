<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Api\Users\CurrentUser;
use Bngr\Media\CMedia;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Api\CPerson;
use Api\Mt;

$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null);

require_once( __DIR__ . "/../Assistant/Api/Bootstrap.php" );
require_once( __DIR__ . "/../Bngr/Api/Bootstrap.php" );
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

if ($limit <= 0) return CStatus::jsonSuccess("Nothing to do");

if ($type == "table") {
    CStatus::pushSettings(0);
    $res = CMedia::fetchN($start, $limit, $sortOrder, $sortBy, $query);

    if ($res) {

        $total = 0;
        echo '{"status":"OK", "total":'. $total . ', "rows": [';
        $cm = "";

        while ($item = AppDB::fetchAssoc($res)) {
            if (CMedia::hasAccess($item['id'])) {
                $item['hasAccess'] = true;
            }
            echo $cm . json_encode($item);
            $cm = ",";
        }
        echo ']}';
        die();
    }
    return CStatus::jsonError("Error");
    CStatus::popSettings();
} else if ($type == "select") {

    if ($res = AppDB::query("SELECT id, name FROM " . CMedia::$defaultTable . " LIMIT $start, $limit")) {
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
