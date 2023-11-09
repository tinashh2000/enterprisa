<?php

/***************************************************************************************
* Copyright (C) 2020 Tinashe Mutandagayi                                               *
*                                                                                      *
* This file is part of the PayrollPro source code. The author(s) of this file		   *
* is/are not liable for any damages, loss or loss of information, deaths, sicknesses   *
* or other bad things resulting from use of this file or software, either direct or    *
* indirect.                                                                            *
* Terms and conditions for use and distribution can be found in the license file named *
* LICENSE.TXT. If you distribute this file or continue using it,                       *
* it means you understand and agree with the terms and conditions in the license file. *
* binding this file.                                                                   *
*                                                                                      *
* Happy Coding :)                                                                      *
****************************************************************************************/

namespace {
	use Api\Mt;
    use Modules\CModule;

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
	$GLOBALS['classDirectory'] = array("CAssistant" => "Assistant", "CNotification" => "Assistant", "CEntities"=> "Entities", "CEntity" => "Entities", "CEntityItem" => "Entities", "CCompany" => "Companies", "CCompanies"=>"Companies");
    $GLOBALS['helperClassDirectory'] = array();
    $GLOBALS['directClasses'] = array("CCrypt8" => "/Api/Ffw/Crypt/Bootstrap.php");
	function EnterprisaAutoload($classname)
	{
		$classDirectory = $GLOBALS['classDirectory'];
        $helperClassDirectory = $GLOBALS['helperClassDirectory'];
        $directClasses = $GLOBALS['directClasses'];
		$filename = "";

		$classPath = str_replace("\\", "/", $classname);
        $cls = basename(str_replace("\\", "/", $classname));
        $baseDir = "";

        $appDir = str_replace("\\", "/", realpath(__DIR__ . "/../"));

		if (isset($classDirectory[$cls]) && ($d = $classDirectory[$cls]) != "") {
		        $baseDir = $appDir . "/Modules/$d/Api";
				$filename = $appDir . "/Modules/$d/Api/Classes/$cls.php";
        }  else if (is_file($appDir . "/Api/Classes/$cls.php")) {

            $baseDir = $appDir . "/Api";
            $filename = $appDir . "/Api/Classes/$cls.php";

		}  else if (isset($directClasses[$cls]) && ($d = $directClasses[$cls]) != "") {
            $filename = $appDir . $directClasses[$cls];
        }
		else if (is_file($appDir . "/Scripts/$cls.php")) {
		    $filename = $appDir . "/Scripts/$cls.php";
        }
		else if (isset($helperClassDirectory[$cls])) {
            $d = $helperClassDirectory[$cls];
	        $filename = $appDir . "/$d";
        }
        else if (is_readable($appDir . "/Api/{$classPath}.php")) {
            $filename = $appDir . "/Api/{$classPath}.php";
        }

		if (is_readable($filename)) {
		    if ($baseDir != "" && is_file($baseDir. "/D.php") )
		        require_once("$baseDir/D.php");
			require_once($filename);
		} else {
		    $mod = CModule::getModuleNames();

		    $pth = explode("/", $classPath);
		    $newPath = realpath($appDir . "/Modules/{$pth[0]}/Api");
		    $cnt = count($pth);

//		    echo $cnt . "$newPath" . is_dir($newPath) . " --- " .is_file($newPath . "/Bootstrap.php");

		    if ($cnt > 1 && is_dir($newPath . "/Classes") && is_file($newPath . "/Bootstrap.php")) {

//		        echo "Path: $newPath";
//		        echo "Include $newPath/Bootstrap.php";
		        require_once("$newPath/Bootstrap.php");
		        $newPath .= "/Classes";
                for ($cc = 1; $cc<$cnt; $cc++) {
                    if (is_dir($newPath . "/{$pth[$cc]}" )) {
                        $newPath .= "/{$pth[$cc]}";
                    } else if ($cc == $cnt- 1 && is_file($newPath . "/{$pth[$cc]}.php")) {
                        $newPath .= "/{$pth[$cc]}.php";
                        require_once("$newPath");
                    } else {
                        break;
                    }
                }
            }

		}
	}

	if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
		//SPL autoloading was introduced in PHP 5.1.2
		if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
			spl_autoload_register('EnterprisaAutoload', true, true);
		} else {
			spl_autoload_register('EnterprisaAutoload');
		}
	}
}

namespace Api {

	use Api\Authentication\CAuth;
    use Ffw\Crypt\CCrypt8;
    use Ffw\Database\Sql;
	use Ffw\Database\Sql\SqlConfig;
	use Api\Session\CSession;
    use Api\Mt;
	const API_VERSION = 1.0;

	if (file_exists(__DIR__ . "/D.php")) {
        require_once("D.php");
        $GLOBALS['isInstalled'] = true;
    }
    else {
        $isInstalled = false;
        $a =explode('/',$_SERVER['PHP_SELF']);
        $cnt = count($a);
        array_splice($a, 0, $cnt - 3);

        $baseName = implode("/", $a);

        if ($cnt > 3) {
            if ($baseName ===  "Api/Install/Install" || $baseName ===  "Api/Install/Install.php")
                $isInstalled = true;
            else {
echo " No NO";
            }
        } else if (array_search($baseName, ["redir.php"]) !== false) {
            $isInstalled = true;
        }




        if (!$isInstalled) {
            echo "[$baseName]";
            $GLOBALS['isInstalled'] = false;
        }
    }

	if (!isset($_SERVER['REQUEST_SCHEME']) || $_SERVER['REQUEST_SCHEME'] == "http" || !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != "on") {
	//	header("Location:https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	//	die();
	}

	if (session_status() != PHP_SESSION_ACTIVE)
		session_start();

	bcscale(4);

	require_once(__DIR__ . "/Classes/Enterprisa.php");
	require_once(__DIR__ . "/Ffw/Api/Bootstrap.php");
	require_once(__DIR__ . "/../Modules/Api/D.php");

	$isLocal = (substr(strtolower(getcwd()), 0, 11) == "/c/website/" || substr(strtolower(getcwd()), 0, 10) == "c:\website") ? true : false;

    if (defined('Api\AppData')) {
        $json = json_decode(CCrypt8::unScrambleText(AppData), true);
        Mt::$database = new SqlConfig($json['database'], $json['host'], $json['username'], $json['password']);

//        Mt::$database = new SqlConfig("enterprisa", "localhost", "ndeip", "Jcare2019@");
        Mt::$db = Mt::$database->database();
        AppDB::__setup();    //Default database

        if (CAuth::isLoggedIn()) {        //If logged in, include more stuff
            require_once(Mt::$appDir . "/Api/Extensions.php");
            if (isset($NeedSql)) {
                if (!CAuth::verifyUser()) {
                    CAuth::logOut();
                    CSession::set("signInReturnAddressXX", basename($_SERVER['REQUEST_URI']));
                    die("Session error. Click <a href='SignIn'>here</a> to login");
                }
            }
        }
    } else if (!$isInstalled){
        die("App Data not defined");
    }

    if ($GLOBALS['isInstalled'] != true) {
        require_once(__DIR__ . "/NotInstalled.php");
        die();
    }
}
