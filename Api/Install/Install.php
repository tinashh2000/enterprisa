<?php

namespace {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

namespace Api\Install {

use Api\AppDB;
use Api\Session\CSession;
use Ffw\Crypt\CCrypt8;
use Ffw\Database\Sql\SqlConfig;
use Ffw\Status\CStatus;
use Helpers\HtmlHelper;
use Api\Mt;
use Api\Install\CInstall;

require_once ("Classes/CInstall.php");

if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'Welcome':
            require_once("Welcome.php");
            break;
        case 'Basic':
            require_once("SetupBasic.php");
            break;
        case 'Database':
            require_once("SetupDatabase.php");
            break;
        case 'Done':
            require_once("SetupDone.php");
            break;
        case 'Modules':
            require_once("SetupModules.php");
            break;
        case 'Serial':
            require_once("SetupSerial.php");
            break;
        case 'Samples':
            require_once("SetupSamples.php");
            break;
        default:
            require_once(__DIR__ . "/../../404.php");
            break;
    }
    die();
}
require_once(Mt::$appDir  . "/Scripts/HtmlHelper.php");

$setupPhase = CInstall::getInstallPhase() ?? PHASE_WELCOME;
$resetTables = isset(CInstall::$dbSettings->resetDatabase) ? true : false;

//HtmlHelper::PageStart("Install Enterprisa", [], $cssFiles, false);
$phases = [ PHASE_WELCOME => "Welcome",
            PHASE_SERIAL => "Enter your serial number",
            PHASE_DATABASE => "Database Setup",
            PHASE_BASIC => "Basic Configuration",
            PHASE_MODULES => "Modules Setup",
            PHASE_SAMPLES => "Samples Initialization",
            PHASE_DONE => "Installation Done", ];

$phaseText = $phases[$setupPhase];

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Api/Install");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_DATETIMEPICKER_]);
HtmlHelper::PageStartX(
    ["title"=>"Enterprisa Installation - " . $phaseText, "description" => "Installation of Enterprisa ERP", "path" =>
        ["Install" => $phaseText]], null, HtmlHelper::FLAG_NOMENU);

switch ($setupPhase) {
    case PHASE_WELCOME:
        require_once ("Welcome.php");
        break;
    case PHASE_SERIAL:
        require_once("SetupSerial.php");
        break;
    case PHASE_DATABASE:
        require_once("SetupDatabase.php");
        break;
    case PHASE_BASIC:
        require_once("SetupBasic.php");
        break;
    case PHASE_MODULES:
        require_once("SetupModules.php");
        break;
    case PHASE_SAMPLES:
        require_once("SetupSamples.php");
        break;
    case PHASE_DONE:
        require_once ("SetupDone.php");
        break;
}

?>
<style>
    #install-proceed .spinner-border {
        vertical-align: text-top;
    }
</style>
<?php
HtmlHelper::PageFooter();
HtmlHelper::PageEndX(); ?>
<script>

    if(typeof initPhase == "function") {
        initPhase();
    }

    var btn = document.getElementById("install-proceed");
    var btnText = btn.innerHTML;
    var installerBusy = false;
    var phaseCompleted=false;

    function enableControls(enable = false) {
        if (enable) {
            $("form").find("input, textarea, select").removeAttr("readonly");
            $("form").find("select.select2").select2({disabled: false, width: '100%'});
        } else {
            $("form").find("input, textarea, select").attr("readonly",'true');
            $("form").find("select.select2").select2({disabled: true, width: '100%'});
        }
    }

    function installBusy(flag = true) {
        if (flag == installerBusy) return false;    //Don't set anything if the state will not change
        if (phaseCompleted) {
            window.location.href="Install";
            return false;
        }

        enableControls(!flag);

        installerBusy = flag;
        btn.innerHTML = flag ? "Processing ... <i class=\"spinner-border spinner-border-sm\" role=\"status\"><span class=\"sr-only\">Loading...</span></i>" : btnText;
        btn.disabled = flag;
        return true;
    }

    function installSuccessful(finalStep = false) {
        btn.innerHTML = btnText;
        btn.disabled = false;
        phaseCompleted = true;
        installerBusy = false;
        btn.innerHTML = finalStep ? "Finish Installation &nbsp;<i class=\" fas fa-arrow-circle-right\"></i></button>" : "Next Step &nbsp;<i class=\" fas fa-arrow-circle-right\"></i></button>";
    }

</script>


<?php }