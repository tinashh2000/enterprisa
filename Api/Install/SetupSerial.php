<?php
namespace Api\Install;

use Api\AppDB;
use Api\CPersonEntity;
use Api\Install\Setup;
use Api\Mt;
use Api\Session\CSession;
use Api\Users\CUser;
use Ffw\Crypt\CCrypt8;
use Ffw\Status\CStatus;
use Api\CPrivilege;
use Api\CPerson;

    require_once("Classes/CInstall.php");
    require_once("Classes/CSerial.php");

    $pd = CInstall::getPhaseData(PHASE_SERIAL);
    $resetTables = isset(CInstall::$dbSettings->resetDatabase) ? true : false;
    $hf = fopen(Mt::$dataPath . "/installation.dat", "a");

    if ($pd == 0 && isset($_POST['serial'])) {
        require_once("Classes/Setup.php");

        if (!file_exists(Mt::$dbRootPath))
            mkdir(Mt::$dbRootPath, 0777, true);

        AppDB::disableLogs();

        if (CSerial::grandValidate($_POST['serial'])) {
            CSession::set("serial", $_POST['serial']);
            CInstall::setInstallPhase(PHASE_SERIAL + 1);


            fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "SerialComplete\n");
            fclose($hf);

            die('{"status":"OK", "message" : "Product Key Verified", "errors" : ' . json_encode(CStatus::getErrors()) . '}');
        }
        die('{"status":"Error", "message" : "Product key is invalid"}');
    }

fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "SerialStart\n");
fclose($hf);
?>

<section class="content">
    <div class="container-fluid v-align">
        <div class="row  d-flex align-self-center justify-content-center">
            <div class='col-md-6 col-sm-10 p-0 m-5'>
                <div class="card">
    <h3 class="col-12 bg-primary pl-3 p-2">Enter your serial number</h3>
                <div class="p-3">
                    <div class="logo d-flex align-self-center justify-content-end mt-0 p-0 mb-3"><a href="home"><img src='<?php echo \Helpers\HtmlHelper::link("Assets/img/hlogo"); ?>' height='40px'/></a></div>

                    <form onsubmit="return false;" method="post" id="serialForm">

                    <input type='hidden' name='adminInfo' value='1'/>

                        <div class="row">
                            <div class="col-12">

                    <label>Product Key</label>
                    <div class="form-group input-group mb-3">
                        <input type="text" name='serial' class="form-control" placeholder="Product Key">
                    </div>
                            </div></div>

                        <div class="row col-12 m-0 p-0">
                            <div class="col-6 d-flex justify-content-start m-0 p-0"><button id="install-proceed" type="submit"
                                                                                            class="btn btn-primary pl-2 pr-2">Proceed
                                    &nbsp;<i class=" fas fa-arrow-circle-right"></i></button></div>
                            <div class="col-6 d-flex justify-content-end m-0 p-0"><a href="<?php echo Mt::$appRelDir ?>"
                                                                                     class="btn btn-warning"
                                                                                     id="install-cancel">Exit Installation&nbsp;<i
                                            class=" fas fa-times-circle"></i></a></div>
                        </div>

                    </form>


                </div></div></div></div></div></section>


<script>

    var initPhase =  function () {

        $('#serialForm').validate({
            rules: {
                serial: {
                    required: true
                }
            },
            messages: {
                serial: {
                    required: "Please enter the product's serial number",
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                var fData = $("#serialForm").serializeArray();
                if (installBusy(true) == false) return false;

                $.post("Install?page=Serial", fData, (response) => {
                    try {
                        var m = JSON.parse(response);
                        if (m["status"] == "OK") {
                            MessageBox(m["message"], false);
                            installSuccessful(false);
//                            window.location.href="Install";
                        } else if (m["status"] == "Error") {
                            MessageBox(m["message"], true);
                            installSuccessful();
                        } else {
                            showStatusDialog(response, "Error. Contact your administrator");
                            installBusy(false);
                        }
                    } catch (e) {
                        installBusy(false);
                        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                    }
                });
            }
        });

    }

</script>
