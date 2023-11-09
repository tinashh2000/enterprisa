<?php

namespace {
    set_time_limit(0); //Unlimited max execution time
//    error_reporting(0);
//    ini_set("display_errors", 0);
}

namespace Api\Install {

    use Api\AppDB;
    use Api\Mt;
    use Api\Session\CSession;
    use Ffw\Crypt\CCrypt8;
    use Ffw\Database\Sql\SqlConfig;
    use Ffw\Status\CStatus;
    use Helpers\HtmlHelper;
    use Api\Install\CSample;

    require_once("Classes/CInstall.php");
    require_once("Classes/CSample.php");

    $hf = fopen(Mt::$dataPath . "/installation.dat", "a");

    if (isset($_POST['initializeSamples']) || isset($_GET['m'])) {
        if (isset($_POST['installSamples']) || isset($_GET['m'])) {
//            $pd = CInstall::getPhaseData(PHASE_SAMPLES);
            CStatus::set(0);
            CSample::initSamples();
        }
        CInstall::setInstallPhase(PHASE_SAMPLES + 1);

        fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "SamplesComplete\n");
        fclose($hf);

        die('{"status":"OK", "messages" : "Successful"}');

    }

    fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "SamplesStart\n");
    fclose($hf);
    ?>
    <section class="content">
        <div class="container-fluid v-align">
            <div class="row  d-flex align-self-center justify-content-center">
                <div class='col-md-6 col-sm-10 p-0 m-5'>
                    <div class="card">
                    <h3 class="col-12 bg-primary pl-3 p-2">Create Sample Data</h3>
                    <div class="p-3">
                        <form onsubmit="return false;" method="post" id="samplesForm">
                            <div class="card-body p-0 m-0">
                                <div class="logo d-flex align-self-center justify-content-end mt-0 p-0 mb-3"><a
                                            href="home"><img
                                                src='<?php echo \Helpers\HtmlHelper::link("Assets/img/hlogo"); ?>'
                                                height='20px'/></a></div>
                                <input type='hidden' name='initializeSamples' value='1'/>
                                <div class="row">
                                    <div class="form-group mb-0 ">
                                        <div class="icheck-primary icheck-inline">
                                            <input type="checkbox" id='installSamples' name="installSamples">
                                            <label for="installSamples">Create Sample Data</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row col-12 m-0 mt-3 p-0">
                                    <div class="col-6 d-flex justify-content-start m-0 p-0">
                                        <button id="install-proceed" type="submit"
                                                class="btn btn-success pl-2 pr-2">Proceed
                                            &nbsp;<i class=" fas fa-arrow-circle-right"></i></button>
                                    </div>
                                    <div class="col-6 d-flex justify-content-end m-0 p-0"><a
                                                href="<?php echo Mt::$appRelDir ?>"
                                                class="btn btn-danger"
                                                id="install-cancel">Exit Installation &nbsp;<i
                                                    class=" fas fa-times-circle"></i></a></div>
                                </div>

                            </div>
                        </form>
                    </div></div></div></div></div></section>

    <script>

        var initPhase = function () {
            $('#samplesForm').validate({
                rules: {
                    databaseName: {
                        required: true,
                    },
                    databaseHost: {
                        required: true,
                    },
                    username: {
                        required: true,
                    }
                },
                messages: {
                    databaseName: {
                        required: "Please enter a valid database name",
                    },
                    databaseHost: {
                        required: "Please provide a hostname",
                    },
                    username: {
                        required: "Please provide a username"
                    },
                    terms: "Please accept our terms"
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
                    var fData = $("#samplesForm").serializeArray();
                    if (installBusy(true) == false) return false;
                    $.post("Install?page=Samples", fData, (response) => {
                        try {
                            var m = JSON.parse(response);
                            if (m["status"] == "OK") {
                                installSuccessful(false);
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

    <?php

}