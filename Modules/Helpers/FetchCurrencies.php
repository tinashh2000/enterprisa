<?php
namespace Api;

use Api\AppDB;
use Api\Authentication\CAuth;
use Api\Session\CSession;
use Currencies\CCurrency;
use Ffw\Decimal\CDecimal;
use Ffw\Status\CStatus;
use Hira\CDepartment;
use Modules\CModule;
use Api\Mt;

$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null);

require_once( __DIR__ . "/../Currencies/Api/Bootstrap.php" );
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

switch($type) {
    case 'fetch':
        CCurrency::fetchS($start, $limit);
        die();
        break;
    case 'table':
        CCurrency::fetchN($start, $limit);
        die();
        break;
    case 'select':
        $default = "";
        if ($res = AppDB::query("SELECT id, name, ratio, flags FROM " . CCurrency::$defaultTable . " LIMIT $start, $limit")) {
            echo '{"results": [';
            $cm = "";
            while ($item = AppDB::fetchAssoc($res))
            {
               if ($item['flags'] & 1) {
                   echo $cm . "{\"id\":{$item['id']}, \"text\":\"{$item['name']} (Default)\", \"factor\": {$item['ratio']}}";
                   $default = $item['id'];
               }
               else
                   echo $cm . "{\"id\":{$item['id']}, \"text\":\"{$item['name']}\", \"factor\": {$item['ratio']}}";
                $cm = ",";
            }
            echo ']';

            $count = CCurrency::count();
            echo ', "count" : ' . $count;
            if ($default != "") echo ',"default" : "' . $default . '"';
            echo '}';
            die();
        }
        return CStatus::jsonError("Unexpected error");
    case 'getDefault':
        CCurrency::getDefault();
        die();
}
CStatus::jsonError("Ambiguous request");
