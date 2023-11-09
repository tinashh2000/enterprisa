<?php

namespace {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

namespace Api\Install {

    use Api\AppDB;
    use Api\Install\CInstall;
    use Api\Mt;
    use Api\Session\CSession;
    use Ffw\Crypt\CCrypt8;
    use Ffw\Database\Sql\SqlConfig;
    use Ffw\Status\CStatus;
    use Helpers\HtmlHelper;

    require_once ("Classes/CInstall.php");

    $setupPhase = CInstall::getInstallPhase();
    $pd = CInstall::getPhaseData(PHASE_DATABASE);

    CStatus::set(CStatus::FLAG_JSON | CStatus::FLAG_DIEONCOMPLETE);

    $hf = fopen(Mt::$dataPath . "/installation.dat", "a");

    if (!$hf) return CStatus::jsonError("File opening error");

    if (isset($_POST['databaseName']) && isset($_POST['tablePrefix'])) {
        CSession::set("databaseSettings", json_encode($_POST));

        $databaseName = $_POST['databaseName'];

        Mt::$database = new SqlConfig($_POST['databaseName'], $_POST['databaseHost'], $_POST['username'], $_POST['password']);

        if (!Mt::$database->server()->connect()) {
            fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "Database access denied\n");
            fclose($hf);
            return CStatus::jsonError("Database access denied1");
        }

        Mt::$db = Mt::$database->database();
        AppDB::__setup();
        CStatus::pushStatus("Connection successful");
        if (isset($_POST['backupDatabase'])) {
            if (!$fName = AppDB::db()->backup()) {
                fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "Database backup failed\n");
                fclose($hf);

                return CStatus::jsonError("Error while backing up database");
            }
            $backUpDir = Mt::$appDir . "/Data/Backup";
            $dataDir = Mt::$appDir . "/Data";

            $c = 0;
            $dt = gmdate("Y-m-d H.i.s", strtotime("now"));
            while (file_exists($backUpDir . "/b$dt")) {
                $dt = gmdate("YmdHis", strtotime("now")) . "_" . $c;
                $c++;
            }
            $d = $backUpDir . "/b$dt/Data";
            mkdir ($d . "/Private", 0777, true);
            require_once(__DIR__ . "/../Procs/RecursiveCopy.php");
            if (!file_exists("$dataDir/Private")) mkdir ("$dataDir/Private", 0777, true);
            if (is_file($fName)) rename($fName, $d . "/$fName");
            if (is_dir($dataDir)) {
                recursiveCopy("$dataDir/Private", "$d");

                $nb = gmdate("Y-m-d H.i.s", strtotime("now"));
                $c = 0;
                while (file_exists("$dataDir/Private.$nb")) {
                    $nb = gmdate("Y-m-d H.i.s", strtotime("now"));
                    if (file_exists("$dataDir/Private.$nb"))
                    {
                        $nb .= "_" . $c;
                        $c++;
                    }
                }

                try {
                    rename("$dataDir/Private", "$dataDir/Private.$nb");
                } catch(Exception $e) {
                    recursiveCopy("$dataDir/Private", "$dataDir/Private.$nb", true);
                }
            }
            fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "Database backup completed\n");
            fclose($hf);

            CStatus::pushStatus("Backup Done");
        }
        if (isset($_POST['resetDatabase'])) {
            Mt::$database->server()->query("DROP DATABASE IF EXISTS $databaseName");
        }
        Mt::$database->server()->query("CREATE DATABASE IF NOT EXISTS " . Mt::$database->databaseName());
        CInstall::setInstallPhase(PHASE_DATABASE + 1);

        fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "DatabaseComplete\n");
        fclose($hf);

        echo '{"status":"OK", "messages" : ' . json_encode(CStatus::getMessages()) . ', "errors" : ' . json_encode(CStatus::getErrors()) . '}';
        die();
    }

    fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "DatabaseStart\n");
    fclose($hf);

    ?>
    <section class="content">
        <div class="container-fluid v-align">
            <div class="row  d-flex align-self-center justify-content-center">
                <div class='col-md-6 col-sm-10 p-0'>
                    <div class="card">
                        <h3 class="col-12 bg-primary pl-3 p-2">Setup Database</h3>
                        <div class="p-3">
                            <form onsubmit="return false;" method="post" id="databaseForm">
                                <div class="card-body p-0 m-0">
                                    <div class="logo d-flex align-self-center justify-content-end mt-0 p-0 mb-3"><a href="home"><img src='<?php echo \Helpers\HtmlHelper::link("Assets/img/hlogo"); ?>' height='40px'/></a></div>
                                    <input type='hidden' name='r' value='database' />

                                    <label>Database Name</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='databaseName' class="form-control" placeholder="Database Name">
                                    </div>

                                    <label>Database Tables Prefix</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='tablePrefix' class="form-control" placeholder="Table Prefix">
                                    </div>

                                    <label>Host Name</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='databaseHost' class="form-control" placeholder="Host name">
                                    </div>

                                    <label>Username</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='username' class="form-control" placeholder="Username">
                                    </div>

                                    <label>Password</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="password" name='password' class="form-control" placeholder="Password">
                                    </div>

                                    <div class="form-group mb-0 ">
                                        <div class="icheck-primary icheck-inline">
                                            <input type="checkbox" id='backupDatabase' name="backupDatabase">
                                            <label for="backupDatabase">Back up database</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0 ">
                                        <div class="icheck-primary icheck-inline">
                                            <input type="checkbox" id="resetDatabase" name="resetDatabase">
                                            <label for="resetDatabase">Reset database tables</label>
                                        </div>
                                    </div>



                                    <div class="row col-12 mt-3">
                                        <div class="col-6 d-flex justify-content-start m-0"><button type="submit"
                                                                                                    id="install-proceed"
                                                                                                    class="btn btn-primary pl-2 pr-2">Proceed
                                                &nbsp;<i class=" fas fa-arrow-circle-right"></i></button></div>
                                        <div class="col-6 d-flex justify-content-end m-0 p-0"><a href="<?php echo Mt::$appRelDir ?>"
                                                                                                 class="btn btn-warning"
                                                                                                 id="install-cancel">Cancel &nbsp;<i
                                                        class=" fas fa-times-circle"></i></a></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div></div></div></div></section>

    <script>

        var initPhase =  function () {
            $('#databaseForm').validate({
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
                    var fData = $("#databaseForm").serializeArray();
                    if (installBusy(true) == false) return false;

                    $.post("Install?page=Database", fData, (response) => {
                        try {
                            var m = JSON.parse(response);
                            if (m["status"] == "OK") {
//                            window.location.href="Install";
                                installSuccessful(false);
                            } else {
                                alert(m["message"]);
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
