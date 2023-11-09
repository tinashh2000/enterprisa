<?php
namespace Api\Install;
use Api\Mt;
use Api\Session\CSession;
use Ffw\Status\CStatus;
use Api\Install\CInstall;

require_once("Classes/CInstall.php");

$hf = fopen(Mt::$dataPath . "/installation.dat", "a");

if (isset($_POST['WelcomeProceed'])) {
    CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);
    CInstall::setInstallPhase(PHASE_WELCOME + 1);

    fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "WelcomeComplete\n");
    fclose($hf);

    CStatus::jsonSuccess();


    die();
}

CSession::delete(array("name", "email", "username", "regdate", "lastUpdated", "privileges", "privilegesList", "logged", "reload_f", "serial", "installedModules", "installDetectedModules"));

fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "WelcomeStart\n");
fclose($hf);
?><section class="content">
    <div class="container-fluid v-align">
        <div class="row  d-flex align-self-center justify-content-center">
            <div class='col-md-6 col-sm-10 p-0 m-5'>
                <div class="card">
                <h3 class="col-12 bg-primary pl-3 p-2">Welcome</h3>
                <div class="logo d-flex align-self-end justify-content-end mt-0 mb-3 mr-4"><a href="home"><img src='<?php echo \Helpers\HtmlHelper::link("Assets/img/hlogo"); ?>' height='40px'/></a></div>

                <form id="welcomeForm" onsubmit="return false" method="post">
                    <input name="WelcomeProceed" type="hidden"/>
                    <div class="p-3">
                        <p>Welcome to EnterprisaPro. This page is going to take you through the installation process.
                            You
                            are advised to thoroughly review and confirm all initialization processes since this process
                            could potentially erase existing information from your system. Some initialization settings
                            may
                            not be changed in future and ought to be set as desired during this process. If you are not
                            sure
                            of the settings, kindly consult an IT specialist or contact our support line.</p>
                        <p>Please be advised that registration information of this software will be transmitted to us
                            for
                            verification and validation. No private, personal or corporate records will be
                            transmitted.</p>

                        <p>Lastly, be sure to create a backup copy of the existing system before erasing it.</p>
                        <div class="row col-12 m-0 p-0">
                            <div class="col-6 d-flex justify-content-start m-0 p-0">
                                <button id="install-proceed" type="submit" class="btn btn-primary pl-2 pr-2">Proceed
                                    &nbsp;<i
                                            class=" fas fa-arrow-circle-right"></i></button>
                            </div>

                            <div class="col-6 d-flex justify-content-end m-0 p-0">
                                <a href="<?php echo Mt::$appRelDir ?>" class="btn btn-warning" id="install-cancel">Cancel
                                    &nbsp;<i class=" fas fa-times-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </form>
                </div></div></div></div></section>

<script>

    var initPhase = function () {
        $('#welcomeForm').validate({
            submitHandler: function (form) {
                $.post("Install?page=Welcome", {"WelcomeProceed": 1}, (response) => {
                    try {
                        var m = JSON.parse(response);
                        if (m["status"] == "OK") {
                            window.location.href = "Install";
                        } else {
                            MessageBox(m["message"], true);
                        }
                    } catch (e) {
                        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                        MessageBox("Error", true);
                    }
                });
            }
        });

    }

</script>
