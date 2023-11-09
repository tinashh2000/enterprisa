<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Session\CSession;
use Api\Users\CurrentUser;
use Bngr\CGenre;
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

//CContact::init(true);

if ($limit <= 0) return CStatus::jsonSuccess("Nothing to do");

if ($type == "table") {
    CGenre::fetchN($start, $limit, $sortOrder, $sortBy, $query);
    die();
} else if ($type == "select") {
//    $total = AppDB::count(self::$professionsTable);

    $currentUser = CurrentUser::getUsername();

    if ($res = AppDB::query("SELECT id, name FROM " . CGenre::$defaultTable . " LIMIT $start, $limit")) {
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
