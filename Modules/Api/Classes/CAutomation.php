<?php

namespace Api\Modules\CAutomation;
use Api\Mt;
use const Modules\MODULE_DIRECTORY;

class CAutomation
{
    static function includeAutomation($module, $variant = 'Server')
    {

        if ($variant != 'Server' && $variant != "Client") return false;
        if (array_key_exists($module, MODULE_DIRECTORY)) {
            $m = MODULE_DIRECTORY[$module];
            $baseDir = Mt::$appDir . "/Modules/" . $m['Location'] . "/Api/";
            $boot = "{$baseDir}/Bootstrap.php";
            $server = "{$baseDir}/Classes/CAutomation{$variant}.php";

            echo $server;
            if (file_exists($boot) && file_exists($server)) {
                require_once($boot);
                require_once($server);

                $cmd = "namespace {$module} {
                    \$a = new CAutomationServer();
                }";
                eval($cmd);
                return $a;
            }
        }
        return false;

    }

    static function sendToAutomationServer($token, $module, $function, $data)
    {
        if ($srv = self::includeAutomation($module, 'Server')) {
            $caps = $srv::getCapabilities();
            return true;
        }
    }

    static function sendToAutomationClient($module, $function, $data)
    {

    }
}