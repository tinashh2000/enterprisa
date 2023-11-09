<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\Users\CUser;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Api\Users\CurrentUser;
use Api\Mt;

$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null); //ASC or DESC

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);
if (!CAuth::isLoggedIn()) return CStatus::jsonError("Login first");

switch ($type) {
    case 'table':
        CUser::fetchN($start, $limit, $sortOrder, $sortBy, $query);
        die();
    case 'select':
        CUser::fetchS($start, $limit, $sortOrder, $sortBy, $query);
//        $currentUsername = CurrentUser::getUsername();
//
//        $q = AppDB::leftJoin(CUser::$defaultTable, CPerson::$defaultTable, "personUid", "uid", "%1.username, %2.name") .
//            (CPrivilege::isAdmin() ?
//                "" :
//                " WHERE " . CUser::$defaultTable .
//                ".username='$currentUsername' OR FIND_IN_SET('*', visibility) > 0 OR FIND_IN_SET('$currentUsername', visibility) > 0 ") .
//            " LIMIT $start, $limit";
//
//        if ($res = AppDB::query($q )) {
//            echo '{"results": [';
//            $cm = "";
//            while ($item = AppDB::fetchAssoc($res))
//            {
//                echo $cm . "{\"id\":\"{$item['username']}\", \"text\":\"{$item['name']}\"}";
//                $cm = ",";
//            }
//            echo ']}';
//            die();
//        }
        return CStatus::jsonError("Unexpected error");
}
CStatus::jsonError("Ambiguous request");
