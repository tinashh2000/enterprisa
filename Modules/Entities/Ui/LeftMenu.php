<?php
namespace Entities;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Admin\CAdmin;
use Api\CPrivilege;
use Helpers\HtmlHelper;
use Entities\CEntities;
use const Entities\moduleName;
//
//$mnu = HtmlHelper::createMenu(moduleName);
//$mnu->addItem("fas fa-euro-sign", "Manage", "Currencies/Currencies");
//$mnu->close();
require_once (__DIR__ . "/../Api/Bootstrap.php");

if (!CPrivilege::checkList(CEntities::BASIC_PERMISSION)) return;

if ($mnu = HtmlHelper::getSettingsMenu()) {
    $subMenu = $mnu->addMenu("Entities", null, null, "fas fa-project-diagram");
    $subMenu->addItem("fas fa-project-diagram", "Entities", "Entities/Home");
    $subMenu->addItem("fas fa-sitemap", "Companies", "Entities/Companies");
    $subMenu->addItem("fas fa-dollar-sign", "Taxes", "Entities/Taxes");
    $subMenu->close();
    $mnu->close();
}
