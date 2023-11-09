<?php
namespace Api\Install;
use Api\Authentication\CAuth;
use Api\Install\CInstall;
use Api\Mt;
use Api\Session\CSession;
use Ffw\Status\CStatus;

require_once("Classes/CInstall.php");

//$pd = CInstall::getPhaseData(PHASE_DONE);
//CInstall::setInstallPhase(0);

$hf = fopen(Mt::$dataPath . "/installation.dat", "a");
fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "SetupComplete\n");
fclose($hf);

CSession::delete(array("name", "email", "username", "regdate", "lastUpdated", "privileges", "privilegesList", "logged", "reload_f", "serial", "installedModules", "installDetectedModules"));
CAuth::logOut();

$dt = gmdate("Ymd His");
@mkdir(Mt::$appDir . "/Data/logs/", 0777, true);
@unlink(Mt::$appDir . "/Data/logs/installation{$dt}.log");
@rename(Mt::$dataPath . "/installation.dat", Mt::$appDir . "/Data/logs/installation{$dt}.log");

$packageName = defined('Api/parentPackage') ? parentPackage : "Enterprisa Pro";
?>
<section class="content">
    <div class="container-fluid v-align">
        <div class="row  d-flex align-self-center justify-content-center">
            <div class='col-md-6 col-sm-10 p-0 m-5'>
                <div class="card">
                <h3 class="col-12 bg-primary pl-3 p-2">Congratulations!</h3>
                <div class="logo d-flex align-self-end justify-content-end mt-0 mb-3 mr-4"><a href="home"><img src='<?php echo \Helpers\HtmlHelper::link("Assets/img/hlogo"); ?>' height='40px'/></a></div>

                <form id="welcomeForm" onsubmit="return false" method="post">
                    <input name="WelcomeProceed" type="hidden"/>
                    <div class="p-3">
                        <p>
                            Congratulations. You have successfully installed <?= $packageName ?>.
                        </p>
                        <div class="row col-12 m-0 p-0">
                            <div class="col-6 d-flex justify-content-end m-0 p-0">
                                <a href="/<?php echo Mt::$appRel ?>" class="btn btn-success" id="install-cancel">Finish
                                    &nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </form>
                </div></div></div></div></section>

<script>

    var initPhase = function () {
        $('#welcomeForm').validate({
            submitHandler: function (form) {
                $.post("Welcome", {"WelcomeProceed": 1}, (response) => {
                    try {
                        var m = JSON.parse(response);
                        if (m["status"] == "OK") {
                            window.location.href = "Install";
                        } else {
                            MessageBox(m["message"], true);
                        }
                    } catch (e) {
                        MessageBox("Error", true);
                    }
                });
            }
        });

    }

</script>
