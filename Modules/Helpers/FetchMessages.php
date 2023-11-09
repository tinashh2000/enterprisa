<?php

namespace Documents;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Mt;
use Api\Session\CSession;
use Currencies\CCurrency;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use Documents\CDocument;
use Api\Messaging\CMessage;
//require_once("Scripts/CheckLogin.php");


$start = CDecimal::integer64(Mt::getParam("offset", 0));
$limit = CDecimal::integer64(Mt::getParam("limit", 25));
$type = Mt::getParam("type", "table");
$query = trim(Mt::getParam('search', Mt::getParam("q")));
$sortBy = Mt::getParam('sort', null);
$sortOrder = Mt::getParam('order', null);
$startTime = Mt::getParam('time', "1980-01-01 00:00:00");
$uni = Mt::getParam('uni', null);
require_once(__DIR__ . "/../Assistant/Api/Bootstrap.php");

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);
$cond = "";
if (!CAuth::isLoggedIn()) return CStatus::jsonError("Login first");

switch ($type) {
    case "table":
        if ($limit >0) {
            $box = Mt::getParam('box', "inbox");;

            if ($uni || $box == "compact")
                CMessage::fetchUni($start, $limit, true, $startTime);
            else {
                CMessage::fetch($start, $limit, $box);
            }
            die();
        }
        CStatus::jsonError();
        die();

        return CStatus::jsonError("Unexpected error");
}
CStatus::jsonError("Ambiguous request");
