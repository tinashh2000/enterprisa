<?php
namespace Modules;

use Api\CPrivilege;
use Api\Mt;
use Api\Session\CSession;

require_once("ModuleVars.php");
require_once("D.php");

class CModule {
    protected static $allowedModules = null;

    static function use_module($moduleName) {
        if (array_key_exists(  $moduleName, MODULE_DIRECTORY)) {
            $m = MODULE_DIRECTORY[$moduleName];
            $f = Mt::$appDir . "/Modules/" . $m['Location']  . "/Api/Bootstrap.php";
            if (file_exists($f)) {
                require_once($f);
                return true;
            }
        }
        die("Module $moduleName not found");
        return;
    }

    static function getWidgets($area) {

    }

    static function getModulesInfo() {
        $moduleList = array();
        foreach(MODULE_DIRECTORY as $k=>$m)  {
            $moduleList[$k] = $m;
        }
        return $moduleList;
    }

    static function getModuleNames() {
        $moduleList = array();
        foreach(MODULE_DIRECTORY as $m)  {
            array_push($moduleList, $m["Name"]);
        }
        return $moduleList;
    }

    static function getModuleDir($moduleName) {
        if (array_key_exists(  $moduleName, MODULE_DIRECTORY)) {
            $m = MODULE_DIRECTORY[$moduleName];
            $f = Mt::$appDir . "/Modules/" . $m['Location'];
            if (file_exists($f)) {
                return $f;
            }
        }
        return null;
    }

    static function getAllowedModules() {
        if (self::$allowedModules == null) {
            $allowedModules = [];
            foreach (MODULE_DIRECTORY as $k => $m) {
                if (!isset($m['AutoPrivilege']) || $m['AutoPrivilege'] == false) {
                    array_push($allowedModules, $k);
                } else {
                    if (CPrivilege::checkBase($m['PrivilegesBase']))
                        array_push($allowedModules, $k);
                }
            }
        }
        return $allowedModules;
    }

    static function hasModulePermissions($moduleName) {
        if (array_key_exists(  $moduleName, MODULE_DIRECTORY)) {
            $m = MODULE_DIRECTORY[$moduleName];
            return CPrivilege::checkBase($m['PrivilegesBase']);
        }
        return null;
    }

    static function searchModuleFiles()
    {
        $baseModuleDir = Mt::$appDir . "/Modules";
        if ($handle = opendir($baseModuleDir)) {
            $array = [];
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && substr($entry,0,1) != "." && is_dir($baseModuleDir . "/$entry")) {
                    $moduleDir = Mt::$appDir . "/Modules/$entry";
                    if (!is_file($moduleDir ."/enterprisa")) continue;
                    if ($hnd = opendir($moduleDir)) {
                        while (false !== ($file = readdir($hnd))) {
                            if ($file != "." && $file != ".." && substr($file, 0, 1) != "." && !is_dir($moduleDir . "/$file")) {
                                //echo basename($file, ".php") . " [$entry]<br>";
//                                echo $file;
                                if ($file == 'redir.info') {
                                    $redirFile = fopen("$moduleDir/$file", 'r');
                                    while (!feof($redirFile)) {
                                        $ln = trim(fgets($redirFile));
                                        if (substr($ln, 0, 1) != '#') {
                                            $lns = explode(",", $ln);
                                            if (count($lns) == 2) {
                                                $array[$lns[0]] = ['module' => $entry, 'path' => $entry, 'redir' => $lns[1]];
                                            }

//                                            print_r($lns);

                                        }
                                    }
                                }

                                if (!isset(pathinfo($file)['extension']) || pathinfo($file)['extension'] !='php')
                                    continue;

                                $f = basename($file, ".php");
                                if ($f == "index" || strtolower($f) == "home" || strtolower($f) == "redir") continue;

                                if (isset($array[$f])) {
                                    echo "Conflict on $f [$entry] and {$array[$f]['module']}<br>";
                                }
                                $array[$f] = ['module' => $entry, 'path' => $entry];
                            }
                        }
                    }
                }
            }
            closedir($handle);
            return $array;
        }
        return null;
    }

    static function initModuleRoutes() {
        $array = self::searchModuleFiles();
        $hf = fopen(Mt::$appDir . "/Modules/Routes.php", "w");
        fwrite($hf, "<?php\r\nnamespace Modules;\r\nuse Api\Mt;\r\n");
        fwrite($hf, "/* THIS FILE WAS AUTOMATICALLY GENERATED AND SHOULD NOT BE EDITED */\n");
        fwrite($hf, "\$GLOBALS['modmap'] = [");
        foreach($array as $k=>$f) {
            fwrite($hf, "'$k' => ['module' => \"{$f['module']}\", 'path' => \"{$f['path']}\"". (isset($f['redir']) ? ", 'redir' => \"{$f['redir']}\"" : "") . "],\r\n");
        }
        fwrite($hf, "];");
        fclose($hf);
    }

    static function loadModuleRoutes() {
        $f = Mt::$appDir . "/Modules/Routes.php";
        if (!is_file($f))
            self::initModuleRoutes();

        require_once($f);
        $allowed = CModule::getAllowedModules();
        $routes = [];

        foreach($GLOBALS['modmap'] as $k=>$m) {
            if (array_search($m['module'], $allowed) !== FALSE)
                $routes[$k] = $m;
        }
        $routes['time'] = gmdate("H:i:s");
        return $routes;
    }

    static function getMappedRoutes() {
        $mapped = null; //CSession::getArrayPlain("mappedModuleRoutes");
        if ($mapped == null) {
            $mapped = self::loadModuleRoutes();
            CSession::setArrayPlain( "mappedModuleRoutes", $mapped);
        }
        return $mapped;
    }

}

CModule::initModuleRoutes();