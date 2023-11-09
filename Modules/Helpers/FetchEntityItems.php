<?php
namespace Api;

use Api\AppDB;
use Api\Authentication\CAuth;
use Api\Session\CSession;
use Entities\CEntities;
use Entities\CEntity;
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

$entity = Mt::getParam('entity', "");
$classification = Mt::getParam('classification', "");

require_once( __DIR__ . "/../Entities/Api/Bootstrap.php" );
CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

AppDB::ffwRealEscapeStringX($entity);
AppDB::ffwRealEscapeStringX($classification);

$q = ($entity != "" ? " name='$entity' " : "");
$cm = $q != "" ? " AND " : "";
$q .= ($classification != "" ? " $cm classification='$classification' " : "");

if ($q != "") $q = " WHERE $q";

switch($type) {
    case 'fetch':
        CEntityItem::fetchS($start, $limit);
        die();
        break;
    case 'table':
        CEntityItem::fetchN($start, $limit);
        die();
        break;
    case 'select':
        if ($q != "") {
            if ($res = AppDB::query("SELECT id, name, classification, flags FROM " . CEntityItem::$defaultTable . " $q LIMIT $start, $limit")) {
                echo '{"results": [';
                $cm = "";
                while ($item = AppDB::fetchAssoc($res))
                {
                    echo $cm . "{\"id\":{$item['id']}, \"text\":\"{$item['name']} ({$item['classification']})\"}";
                    $cm = ",";
                }
                echo ']}';
                die();
            }
            die();
        }

        if ($res = AppDB::query("SELECT id, name, classification, flags FROM " . CEntity::$defaultTable . " $q LIMIT $start, $limit")) {
            echo '{"results": [';
            $cm = "";
            while ($item = AppDB::fetchAssoc($res))
            {
                echo $cm . "{\"id\":{$item['id']}, \"text\":\"{$item['name']} ({$item['classification']})\"}";
                $cm = ",";
            }
            echo ']}';
            die();
        }
        return CStatus::jsonError("Unexpected error");
    case 'getDefault':
        CEntity::getDefault();
        die();
}
CStatus::jsonError("Ambiguous request");
