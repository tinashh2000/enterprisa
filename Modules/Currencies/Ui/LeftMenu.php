<?php
namespace Currencies;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Admin\CAdmin;
use Api\CPrivilege;
use Helpers\HtmlHelper;
use Currencies\CCurrency;
use const Currencies\moduleName;

require_once(__DIR__ . "/../Api/Bootstrap.php");
if (!CPrivilege::checkList(CCurrency::BASIC_PERMISSION)) return;

if ($mnu = HtmlHelper::getSettingsMenu()) {
    $mnu->addItem("fas fa-money-bill-alt", "Currencies", "Currencies/Home");
    $mnu->close();
}