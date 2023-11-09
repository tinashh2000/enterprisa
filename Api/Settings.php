<?php
namespace Api;

use Api\Authentication\CAuth;
use Api\AppDB;
use Api\Mt;
use Api\Roles\CRole;
use Api\Session\CSession;
use Accounta\Accounts\CAccount;
use Api\Messaging\CMessage;
use Ffw\Status\CStatus;
use Api\CSettings;

require_once("Bootstrap.php");
require_once(Mt::$appDir . "/Scripts/ApiCheckLogin.php");

if (!isset($_POST['r']) && !isset($_GET['r'])) {
	die('{"status": "Error", "message": "Resource not found"}');
	return false;
}

require_once ("Classes/CRole.php");

$postMethod = isset($_POST['r']) ? true : 0;

$rq = $postMethod ? $_POST['r'] : $_GET['r'];

CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

//print_r($_POST);
//print_r($_FILES);
//die();
switch ($rq) {
	case 'set':

		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		if (isset($_POST['name']) && isset($_POST['data']) && isset($_POST['description'])) {
			$settings = new CSettings($_POST['name'], $_POST['data'], $_POST['description'], 0);
			$settings->create();
			die();
		}
		break;
	case 'get':

		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		if (isset($_POST['name'])) {
			return CSettings::get($_POST['name']);
		}
		break;
	case 'setCurrentCompany':
		if (isset($_POST['companyId'])) {
			CSettings::setCurrentCompany($_POST['companyId']);
		}
		break;
	case 'setSetting':

		if (!CPrivilege::isAdmin())
			return CStatus::jsonError("Access Denied");

		$c = 0;

		$setting = new CSettings("","", "");
		if (isset($_POST['email']) && isset($_POST['uid'])) {
			$setting->name = "Email";
			$setting->data = $_POST['email'];
			$setting->description="";
			$setting->set();

			$setting->name = "ProfileUid";
			$setting->data = $_POST['uid'];
			$setting->description="";
			$setting->set();
			$c++;
		}

		if (isset($_POST['defaultModule'])) {
			$setting->name = "DefaultModule";
			$setting->data = $_POST['defaultModule'];
			$setting->description="";
			$setting->set();
			$c++;
		}

		if (isset($_POST['footerText'])) {
			$setting->name = "FooterText";
			$setting->data = $_POST['footerText'];
			$setting->description="";
			$setting->set();
			$c++;
		}

		if (isset($_FILES['headerLogo'])) {
			$fil = "headerLogo";
			$fil = $setting->uploadPicture(\Api\CSettings::NO_IMAGE_CONVERT, "pics", $fil,"headerLogo");
			@copy($fil, Mt::$appDir . "/Assets/img/hlogo.jpg");
			$fil = Mt::removePrefix($fil, $setting::$defaultPath . "/pics/");
			$setting->name = "HeaderLogo";
			$setting->data = $fil;
			$setting->description="";
			$setting->set();
			$c++;
		}

		if (isset($_FILES['defaultLogo'])) {
			$fil = "defaultLogo";
			$fil = $setting->uploadPicture(\Api\CSettings::NO_IMAGE_CONVERT, "pics", $fil,"defaultLogo");
			copy($fil, Mt::$appDir . "/Assets/img/logo.jpg");
			$fil = Mt::removePrefix($fil, $setting::$defaultPath . "/pics/");
			$setting->name = "DefaultLogo";
			$setting->data = $fil;
			$setting->description="";
			$setting->set();
			$c++;
		}
		if ($c > 0) die(CStatus::jsonSuccess("Updated successfully"));
		break;
}
CStatus::jsonError("Resource not found");

?>
