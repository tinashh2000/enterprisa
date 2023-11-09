<?php

namespace Api\Modules;
use Api\CPrivilege;
use Api\Mt;
use const Modules\MODULE_DIRECTORY;
class CWidget
{
    protected static $widgets;

    static function loadAllWidgets() {
        if (self::$widgets == null) {
            self::$widgets = [];
            foreach (MODULE_DIRECTORY as $k => $m) {
                self::getModuleWidgets($k);
            }
        }
        return self::$widgets;
    }

    static function getModuleWidgets($module) {
        if (array_key_exists($module, MODULE_DIRECTORY)) {
            $m = MODULE_DIRECTORY[$module];
            $baseDir = Mt::$appDir . "/Modules/" . $m['Location'];
            $boot = "{$baseDir}/Api/Bootstrap.php";
            $server = "{$baseDir}/Ui/Widgets/Widgets.php";

            if (file_exists($boot) && file_exists($server)) {
                $getModuleWidgetsValue = require_once($server);
                if (isset($getModuleWidgetsValue) && is_array($getModuleWidgetsValue)) {
                    self::$widgets[$module] = $getModuleWidgetsValue;
                    return $getModuleWidgetsValue;
                }

                return null;
            }
        }
    }

    static function getWidget($path) {
        $pt = explode($path);
        if (count($pt) < 2) return;
        $module = $pt[0];
    }

}