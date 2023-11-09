<?php

namespace Api\Install;

use Api\AppConfig;
use Api\Mt;
use Api\Users\CUser;
use Api\Forensic\CForensic;
use Ffw\Status\CStatus;
use Ffw\Decimal\CDecimal;
use const Api\API_VERSION;
use Api\Session\CSession;

set_time_limit(0); //Unlimited max execution time

$leftMenuBar = array();
$moduleDirectory = "";
$modulesList = array();

class CModules
{
    static $detectedModules = null;

    static function isModule($moduleDir, &$dest)
    {
        if (file_exists("$moduleDir/enterprisa") && !is_dir("$moduleDir/enterprisa") && file_exists("$moduleDir/Api")) {
            $efile = explode("\n", file_get_contents("$moduleDir/enterprisa"));

            $moduleName = "";
            $moduleLabel = "";
            $moduleDescription = "";
            $modulePermissions = 0;
            $moduleRequirements = 0;
            $modulePrivilegesBase =0;
            $moduleDependencies = "";
            $moduleAccessVerification="";
            foreach ($efile as $line) {
                $lns = explode(":", $line);
                if (count($lns) > 1) {
                    $lns[1] = trim($lns[1]);
                    switch (trim($lns[0])) {
                        case "Name":
                            $moduleName = $lns[1];
                            break;
                        case "Description":
                            $moduleDescription = $lns[1];
                            break;
                        case "Label":
                            $moduleLabel = $lns[1];
                            break;
                        case "Permissions":
                            $modulePermissions = CDecimal::integer64($lns[1]);
                            break;
                        case "Requirements" :
                            $moduleRequirements = CDecimal::decimal($lns[1]);
                            break;
                        case "PrivilegesBase" :
                            $modulePrivilegesBase = CDecimal::integer64($lns[1]);
                            break;
                        case "Dependencies":
                            $moduleDependencies = $lns[1];
                            break;
                         case "AccessVerification":
                            $moduleAccessVerification = $lns[1];
                            break;
                        default:
                            break;
                    }
                }
            }
            if ($moduleRequirements > API_VERSION)  //If a module requires a future version, don't succeed.
                return false;

            $dest = array("name" => $moduleName, "description" => $moduleDescription, "label" => $moduleLabel, "permissions" => $modulePermissions, "privilegesBase" => $modulePrivilegesBase, "requirements" => $moduleRequirements, "accessVerification" => $moduleAccessVerification, "dependencies" => $moduleDependencies);
            return true;
        }
        return false;
    }

    protected static function installModule($name) {
        $baseModuleDir = Mt::$appDir . "/Modules";
        $modules = self::$detectedModules == null ? self::searchModules() : self::$detectedModules;
        $moduleDir = $baseModuleDir . "/$name";
        $modInfo=array();
        $privilegesBase = 0;
        if (self::isModule($moduleDir, $modInfo)) {
            if ($hf =  fopen("$moduleDir/Api/D.php", "w+")) {
                fwrite($hf,
                    "<?php
	namespace {$modInfo['label']};
	const parentPackage=\"Enterprisa Pro\";
	const parentPath=\"" . Mt::$appRelDir . "\";
	const parentCapabilities=1;
	const parentUiCapabilities=1;
	const modulePath=\"" . substr($moduleDir, strlen(Mt::$appRoot)) . "\";
	const modulePrivilegesBase={$modInfo['privilegesBase']};
	const moduleName=\"" . trim($name) . "\";
	const moduleDependencies=\"{$modInfo['dependencies']}\";
	const moduleAccessVerification=\"{$modInfo['accessVerification']}\";
	");

    fclose($hf);
                if ($hf =  fopen("$baseModuleDir/Api/E.php", "a+")) {
                    fwrite($hf, json_encode($modInfo));
                    fclose($hf);
                }
                self::initModule($moduleDir);
                CStatus::pushStatus($modInfo['name'] . " installed successfully");
                return true;
            }
        }
        return false;
    }

    static function initModule($moduleDir)
    {
        if (file_exists("$moduleDir/Api/Install/Init.php")) {
            require_once("$moduleDir/Api/Install/Init.php");
        }
    }

    static function searchModules()
    {
        $baseModuleDir = Mt::$appDir . "/Modules";
        $modInfo = array();
        $modules = array();
        if ($handle = opendir($baseModuleDir)) {
            if (file_exists("$baseModuleDir/Api/E.php")) unlink("$baseModuleDir/Api/E.php");
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && substr($entry,0,1) != "." && is_dir($baseModuleDir . "/$entry")) {
                    $moduleDir = $baseModuleDir . "/$entry";
                    if (file_exists("$moduleDir/enterprisa")) {
                        if (self::isModule($moduleDir, $modInfo)) {
                            $modules[$modInfo["label"]] = $modInfo;
                        }
                    }
                }
            }
            closedir($handle);
            self::$detectedModules = $modules;
            return $modules;
        }
    }

    /*
     * Resolves Dependencies by ordering modules according to their dependencies. Those modules without dependencies or those
     * modules whose dependencies are already in the list are also appended to the end if the list.
     *
     * If a module has dependencies which are yet to be discovered, those dependencies are added to a temporary list
     * and will be resolved first in the next pass. The module is also added after its dependencies in a temporary list
     *
     * If a module
     */

    static function resolveDependencies($modulesToInstall, $modulesList): array
    {
        $order = [];

        $neededDeps = $modulesToInstall;
        $nItems = count($modulesToInstall);
        $iterations = 0;
        while (count($neededDeps) > 0 && $iterations++ < ($nItems)) {
            $next = [];
            $nCount = count($neededDeps);
            for ($i=0;$i < $nCount; $i++) {
                $m = $neededDeps[$i];
                if (isset($modulesList[$m])) {
                    $mm = $modulesList[$m];
                    $n = 0;
                    if (isset($mm["dependencies"])) {
//                        echo "<p>Deps: $m => {$mm['dependencies']}<br></p>";
                        $deps = explode(",", $mm["dependencies"]);
                        $arLen = count($deps);
                        foreach ($deps as $dep) {
                            $dep = trim($dep);
                            //If dependency is not already in order array
                            if ($dep != "" && !in_array($dep, $order) && in_array($dep, $modulesToInstall)) {
                                //If a dependency is not in the next queue already, add it
                                if (!in_array($dep, $next))
                                    array_push($next, $dep);
                                $n++;
    //                            echo "[push:$dep]";
                            } else {
  //                              echo "[ignore:$dep]" . (in_array($dep, $order) ? 1: 0) . "   " . print_r($order, true) . " ";
                            }
                        }
                    } else {
//                        echo "[No deps: $m]";
                    }
                    if ($n == 0) {    //Has no dependencies
                        if (!in_array($m, $order))
                            array_push($order, $m);
                    }
                    else {
                        if (!in_array($m, $next))
                            array_push($next, $m);
                    }

                } else {
                    CStatus::pushError("Module " . $m . " not found, and therefore was not installed");
                }
            }
            $neededDeps = $next;
        }
//        echo "[i : $iterations, cnt: $nItems]";
        return $order;
    }

    static function installModules($installModules, $reset) {
        $modules = self::searchModules();
        $installed = CSession::getArrayPlain("installedModules");
        if ($installed == null) $installed = array();
        $order = self::resolveDependencies($installModules, $modules);

        foreach($order as $m) {
            if (isset($modules[$m])) {
                if (self::InstallModule($m))
                    array_push($installed, $m);
            } else {
                CStatus::pushError("Module " . $m. " not found, and therefore was not installed");
            }
        }
        CSession::setArrayPlain("installedModules", $installed);
    }
}