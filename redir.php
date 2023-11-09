<?php

use Api\Authentication\CAuth;
use Api\Mt;
use Api\Users\CurrentUser;
use Modules\CModule;
use Api\Session\CSession;

$contentTypes = array("image/jpeg", "image/png", "image/svg+xml", "image/tiff", "image/gif", "text/javascript", "text/css" );
$fileTypesArray = array("jpg", "png", "svg", "tiff", "gif", "js", "css");
$picsLastItem = 4;
$aLog = "";
$originalReDir = null;
$redirCurrentModule="";

require_once("Api/Bootstrap.php");
require_once("Modules/Api/Bootstrap.php");

function pError() {
	die($_GET['redirPageId']);
	header("Location: " . Mt::$appRelDir.  "/404.php");
	die();
}

function redirectPage($pg, $baseDir = "") {
	global $redirCurrentModule;
	require_once("Api/Bootstrap.php");
	require_once("Modules/Api/Bootstrap.php");
	if ($baseDir != "") {
		require_once("$baseDir/Api/Bootstrap.php");
		$redirCurrentModule = $baseDir;
	}

	require_once("$pg");
	die();
}

function checkLogin($showError = true) {
	require_once(__DIR__ . "/Api/Bootstrap.php");
	require_once(__DIR__ . "/Api/Classes/CAuth.php");
	if (CAuth::isLoggedIn()) return true;
	if ($showError) die("Please login");
}


require_once("Api/Bootstrap.php");
$routes = CModule::getMappedRoutes();

$r = $_GET['redirPageId'];
if (isset($routes[$r])) {
	$f = "Modules/" . $routes[$r]['path'] . "/$r.php";
	if (is_file($f)) {
		redirectPage($f, "Modules/" . $routes[$r]['path']);
		die();
	}
}

$d = dirname($r);
if ($slpos = strpos($d, "/")) {
	$d = substr($d, 0, $slpos);
}
if (isset($routes[$d]) && isset($routes[$d]['redir'])) {
	$f = "Modules/" . $routes[$d]['path'] . "/{$routes[$d]['redir']}";
	if (is_file($f)) {
		redirectPage($f, "Modules/" . $routes[$d]['path']);
		die();
	}
}

function reDirShowPic($img, $altImg="") {
	global $contentTypes, $fileTypesArray, $picsLastItem;
	$pi = pathinfo($img);



	if (is_file($img) && isset($pi['extension'])) {
		if (($r = array_search($pi['extension'], $fileTypesArray)) !== FALSE && $r <= $picsLastItem) {

			$ct = $contentTypes[$r];
			header("Content-type: $ct", true);
			echo file_get_contents($img);
			die();
		}
	}

	if ($altImg == "") $altImg = Mt::$appDir . "/Assets/img/placeholder.svg";
	$pi = pathinfo($altImg);

	if (is_file($altImg) && isset($pi['extension'])) {

		if (($r = array_search($pi['extension'], $fileTypesArray)) !== FALSE && $r <= $picsLastItem) {


			$ct = $contentTypes[$r];

			header("Content-type: $ct", true);
			echo file_get_contents($altImg);
			die();
		}
	}

	header("Content-type: image/svg+xml", true);
	echo file_get_contents($altImg = Mt::$appDir . "/Assets/img/placeholder.svg");
	die();
}


function tryRedir($dir, $path) {
	unset($path[0]);

	$_GET['redirPageId'] = implode("/",$path);
	$file = $dir ."/". implode("/",$path);
	$pi = pathinfo($file);
	$ext = isset($pi['extension']) ? strtolower($pi['extension']) : "";
	extRedir($file, $ext, __DIR__);
	die();
}

function extRedir($pg, $ext, $baseDir="") {
	global $contentTypes, $fileTypesArray, $path;

	if (file_exists($pg) && ($r = array_search($ext, $fileTypesArray)) !== FALSE) {
		$ct = $contentTypes[$r];
		header("Content-type: $ct", true);
		echo file_get_contents($pg);
		die();
	}else {
		$path = explode("/", $_GET['redirPageId']);
		if ($ext == "" && $path[0] == "Helper" && is_file($pg . ".php")) {
			redirectPage($pg .".php", $baseDir);
			die();
		}
		pError();
	}
	die();
}

if (isset($_GET['redirPageId'])) {
	$originalReDir = $_GET['redirPageId'];
	$dirs = explode("/", $_GET['redirPageId']);
	$pg = "Modules/{$_GET['redirPageId']}";
	$baseDir = "Modules/" . $dirs[0];

	$c = count($dirs);

	if ($_GET['redirPageId'] == 'Assets/js/Stats.js') {
		if (checkLogin(false)) {
			echo "var currentUser='" . CurrentUser::getUsername() . "';";
			die();
		}
	}

	if (file_exists($baseDir . "/enterprisa") && file_exists($baseDir . "/Api/Bootstrap.php")) {	//If it is a valid dir or file
		if (file_exists($pg)) {	//if the whole path exists after adding a /Module to the path
			if (is_dir($pg)) { //If this is a directory
				if (file_exists("$pg/Home.php")) { // then look for a homepage
					redirectPage("$pg/Home.php", $baseDir);
				}	//If no homepage is found, error
			}
			else {	//If not a directory
				$c = count($dirs);
				if ($c > 0) {	//if there are items in the path
					$fname = $dirs[count($dirs) - 1];
					$ext = strtolower(pathinfo($fname)["extension"]);
					if ($ext != "php") {	//Anything referenced should be a php or should match these extensions
						extRedir($pg, $ext);
					}
					redirectPage($pg, $baseDir);	//If it is a php file.A php file will protect itself if need be
					die();
				}
			}
		} else if (file_exists("$pg.php")) {	//If given filename is missing a .php
			redirectPage("$pg.php", $baseDir);
			die();
		} else {	//otherwise walk the directories and see if we have a further redirection handler
			$c = count($dirs);
			if ($c > 0) {
				$cDir = $baseDir;
				for ($i = 0; $i < $c; $i++) {
					if (file_exists($cDir . "/" . $dirs[$i])) {
						$cDir .= "/" . $dirs[$i];
						unset($dirs[$i]);
					} else {
						break;
					}
				}

				unset($dirs[0]);
				if (is_dir($cDir) && file_exists($cDir . "/redir.php")) {
					$_GET['redirPageId'] = implode("/", $dirs);
					redirectPage($cDir . "/redir.php", $baseDir);
				}
			}
		}
	}
	else {
		if (is_array($dirs) && isset($dirs[0])) {
			if ($dirs[0] == "Helpers") {
				if (count($dirs) > 1) {
					if (is_file("$pg.php")) {
						checkLogin();
						redirectPage("$pg.php");
						die();
					}
				}
			} else if ($dirs[0] == "users" || $dirs[0] == "messages" || $dirs[0] == "people" || $dirs[0] == "shops" || $dirs[0] == "products" || $dirs[0] == "baseProducts" || $dirs[0] == "listings" || $dirs[0] == "slides") {
					$baseDir = "Assistant";
					redirectPage("Assistant/redir.php", $baseDir);
			} else if ($dirs[0] == "invoices") {
				$baseDir = "Modules/Sales";
				checkLogin();
				redirectPage("Modules/Sales/redir.php", $baseDir);
			}  else if ($dirs[0] == "customers" || $dirs[0] == "sales" || $dirs[0] == "invoices") {
				$baseDir = "Modules/Crm";
				checkLogin();
				redirectPage("Modules/Crm/redir.php", $baseDir);
			}  else if ($dirs[0] == "products") {
				$baseDir = "Modules/Inventory";
				checkLogin();
				redirectPage("Modules/Inventory/redir.php", $baseDir);
			} else if ($dirs[0] == "companies") {
				if (!isset($dirs[2]) || $dirs[2] != "pic") checkLogin();
				$baseDir = "Modules/Companies";
				redirectPage("Modules/Companies/redir.php", $baseDir);
			} else if ($dirs[0] == "budget" || $dirs[0] == "budgets" || $dirs[0] == "requisition" || $dirs[0] == "requisitions") {
				if (!isset($dirs[2]) || $dirs[2] != "pic") checkLogin();
				$baseDir = "Modules/Accounta";
				redirectPage("Modules/Accounta/redir.php", $baseDir);
			} else if ($dirs[0] == "Assets") {
				if ($c > 2) {
					switch($dirs[1]) {
						case "img":
							switch ($dirs[2]) {
								case "logo":
									$defaultLogo = \Api\Session\CSession::getValue("settings", "DefaultLogo");
									$path = \Api\CSettings::$defaultPath . "/pics/" . $defaultLogo;
									reDirShowPic($path, Mt::$appDir . "/Assets/img/logo.jpg");
									break;
								case "hlogo":

									$headerLogo = \Api\Session\CSession::getValue("settings", "HeaderLogo");
									$path = \Api\CSettings::$defaultPath . "/pics/" . $headerLogo;
									reDirShowPic($path,Mt::$appDir . "/Assets/img/hlogo.jpg");
									break;
                                default:

							}
							break;
					}
				}
			}
		}

		pError();
	}
} else {
	header("Location: 404.php");
	die();
}

$n = basename($_GET['redirPageId']);
if ($n == "Wrapper") {
	require_once("Wrapper.php");
	die();
}



function nameToSvgPic($name) {
	$names = explode(" ", $name);
	$cn = count($names);
	if ($cn < 2) {
		$initials = substr(trim($names[0]), 0, 2);
	} else
		$initials = substr(trim($names[0]), 0, 1) . substr(trim($names[$cn-1]), 0, 1);
	header("Content-type: image/svg+xml", true);
	echo '<?xml version="1.0" encoding="utf-8"?'.'>
<!-- Generator: Enterprisa Pro)  -->
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 140 140" style="enable-background:new 0 0 140 140;" xml:space="preserve">
<style type="text/css">
	.st0{fill:#FFFFFF;}
	.st1{font-family:"Open Sans", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";}
	.st2{font-size:64px;}
</style>
<ellipse cx="50%" cy="50%" rx="70" ry="70" fill="#456580"/>
<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="st0 st1 st2">'.strtoupper($initials).'</text>
</svg>';

}

pError();
