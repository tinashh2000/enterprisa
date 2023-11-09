<?php
namespace Api\Install;

use Api\AppDB;
use Api\CPerson;
use Api\Mt;
use Api\Session\CSession;
use Ffw\Crypt\CCrypt8;
use Ffw\Database\Sql\SqlConfig;
use Ffw\Status\CStatus;
use Api\Forensic\CForensic;
use Api\Users\CUser;
use Api\Roles\CRole;

const PHASE_WELCOME   =   0;
const PHASE_SERIAL   =   1;
const PHASE_DATABASE   =   2;
const PHASE_BASIC   =   3;
const PHASE_MODULES   =   4;
const PHASE_SAMPLES   =   5;
const PHASE_DONE   =   6;

require_once(__DIR__ . "/../../Bootstrap.php");

class CInstall {

    public static $setupPhase;
    public static $dbSettings=null;
    static function __setup() {
        self::$setupPhase = CCrypt8::unScrambleText(CSession::get("setupPhase"));

        if (!CSession::exists("setupPhase") || !is_numeric(self::$setupPhase) || self::$setupPhase  < PHASE_WELCOME || self::$setupPhase > PHASE_DONE) {
            self::$setupPhase = PHASE_WELCOME;
            CSession::set("setupPhase", CCrypt8::scrambleText(self::$setupPhase));
        }

        if (self::$setupPhase > PHASE_DATABASE && CSession::exists("databaseSettings")) {
            self::$dbSettings = json_decode(CSession::get("databaseSettings"));

            Mt::$dbPrefix = self::$dbSettings->tablePrefix;
            Mt::$database = new SqlConfig(self::$dbSettings->databaseName, self::$dbSettings->databaseHost, self::$dbSettings->username, self::$dbSettings->password);

            if (!Mt::$database->database()->select()) {
                CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);
                return CStatus::jsonError("Database access denied");
            }

            Mt::$db = Mt::$database->database();
            AppDB::__setup();

            if (class_exists("Api\Forensic\CForensic", false))  //Reinitialize classes affected by the database changes
                CForensic::__setup();

            if (class_exists("Api\CPerson", false))
                CPerson::__setup();

            if (class_exists("Api\Users\CUser", false))
                CUser::__setup();

            if (class_exists("Api\Roles\CRole", false))
                CRole::__setup();

        }
    }

    static function verifyPhase($p) {
        if (self::$setupPhase != $p) {
            self::setInstallPhase(0);
            die("<script>window.location.href='Install';</script>");
        }
    }

    static function setInstallPhase($p) {
        if ($p == 0) {
            CSession::delete("databaseSettings");
            CSession::delete("installDetectedModules");
        }
        CSession::set("setupPhase", CCrypt8::scrambleText($p));
        CSession::set("setupPhaseData", CCrypt8::scrambleText(0));
   }

    static function getInstallPhase() {
        return self::$setupPhase = CCrypt8::unScrambleText(CSession::get("setupPhase"));
    }
    static function setPhaseData($d) {
        CSession::set("setupPhaseData", CCrypt8::scrambleText($d));
    }

    static function getPhaseData($p) {
        self::verifyPhase($p);
        return CCrypt8::unScrambleText(CSession::get("setupPhaseData"));
    }

    static function showErrors() {
        $errors = CStatus::getErrors();
        $numErrors = is_array($errors) ? count($errors) : 0;

        if ($numErrors > 0) {
            echo "<p>Unfortunately, setup encountered some errors as follows:</p>";
            foreach ($errors as $error) {
                echo "<a class=''><i class='fas fa-times fa-2x text-danger'></i> $error</a>";
            }
//        CSession::delete("setupPhase");
        } else {
            $messages = CStatus::getMessages();
            foreach ($messages as $msg) {
                echo "<div class='install-status-item'><a class=''><i class='fas fa-check bg-success'></i> $msg</a></div>";
            }
        }

    }

    static function commit() {
        $dbPrefix = Mt::$dbPrefix;
        $baseDir = Mt::$appRel; //dirname(dirname($_SERVER['PHP_SELF'],2));
        $modulesDir = "Modules";
        $absDir = $_SERVER['DOCUMENT_ROOT'] . "/" . $baseDir;

        if (isset(CInstall::$dbSettings->databaseName)) {
            $dbSet = array("database" => CInstall::$dbSettings->databaseName, "prefix" => CInstall::$dbSettings->tablePrefix, "host" => CInstall::$dbSettings->databaseHost, "username" => CInstall::$dbSettings->username, "password" => CInstall::$dbSettings->password, "productKey" => CSession::get("serial"));
            $dbJSON = CCrypt8::scrambleText(json_encode($dbSet));

            $hf = fopen($absDir . "/Api/D.php", "w");
            fwrite($hf, "<?php
namespace Api;
const parentPackage=\"Enterprisa Pro\";
const parentPath=\"$baseDir\";
const parentCapabilities=1;
const parentUiCapabilities=1;
const modulePrivilegesBase=0;
const moduleBasePath=\"$modulesDir\";
const dbPrefix = \"{$dbPrefix}\";
const AppData=\"$dbJSON\";");
            fclose($hf);
        }
    }
}
CInstall::__setup();
//CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);
