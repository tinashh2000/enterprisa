<?php
namespace Admin;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\CPrivilege;
use Api\Users\CurrentUser;
use Helpers\HtmlHelper;
use const Crm\moduleName;

require_once(__DIR__ . "/../Api/Bootstrap.php");
if (!CPrivilege::checkList(CAdmin::BASIC_PERMISSION)) return;

//HtmlHelper::addMenu(moduleName);

if ($mnu = HtmlHelper::getSettingsMenu()) {
    $mnu->addItem("fas fa-tools", "Settings", "Admin/Settings");
    $mnu->addItem("fas fa-user", "Users", "Admin/Users");
    $mnu->addItem("fas fa-list", "Roles", "Admin/Roles");

    $mnu->addItem("fas fa-envelope", "Auto-mailer Settings", "Admin/EmailSettings");
    $mnu->addItem("fas fa-edit", "Custom Forms", "Admin/CustomForms");
    $mnu->addItem("fas fa-edit", "Import Mappings", "Admin/ImportMappings");
    $mnu->close();
}
