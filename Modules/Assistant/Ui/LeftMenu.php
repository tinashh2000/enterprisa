<?php
namespace Assistant;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\CPrivilege;
use Api\CAssistant;
use Helpers\HtmlHelper;
use Api\Mt;
use const Assistant\moduleName;

require_once (__DIR__ . "/../Api/Bootstrap.php");

$root = Mt::$appRelDir;
//$mnu = HtmlHelper::createProfileMenu("<i class='d-none d-sm-inline far fa-comment'></i><span class='d-inline d-sm-none'><i class='far fa-comment'></i> Messages</span>", 0);
//$mnu->addItem("", "<div class='p-5 bg-primary'>My Div</div>", "Messages#inbox");
//$mnu->addItem("fas fa-share deg45 text-danger", "Sent Items", "Messages#sent");
//$mnu->addItem("fas fa-cogs text-primary", "Email Settings", "MyEmailSettings");

$mnu = HtmlHelper::createProfileMenu("Profile", "","fas fa-user");
$mnu->addItem("fas fa-user", "My Profile", "MyProfile");
$mnu->addItem("fas fa-user", "Messages", "Messages");
$mnu->addItem("fas fa-users", "People", "People");
if (CPrivilege::checkList(CAssistant::EVENTS_BASIC))
    $mnu->addItem("fas fa-calendar-check", "Events", "Events");
$mnu->addItem("fas fa-calendar", "Calendar", "Calendar");
$mnu->addItem("fas fa-check", "Tasks", "MyTasks");
$mnu->addItem("fas fa-cogs", "Preferences", "Preferences");
$mnu->addItem("fas fa-power-off", "Sign Out", "SignOut");

$mnu->close();
