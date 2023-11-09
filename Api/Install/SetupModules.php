<?php

namespace {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

namespace Api\Install {

    use Api\AppDB;
    use Api\Mt;
    use Api\Session\CSession;
    use Ffw\Crypt\CCrypt8;
    use Ffw\Database\Sql\SqlConfig;
    use Ffw\Status\CStatus;
    use Helpers\HtmlHelper;

    require_once("Classes/CInstall.php");
    require_once("Classes/CModules.php");

    $modules = [];

    function addModuleToList(&$mList, $module, $level=0, &$intent=null) {

        if (is_string($module)) {
            $module = $GLOBALS['modules'][$module];
        }
        $m = $module['label'];

        if ($intent != null) {
            if (in_array($m, $intent)) return;   //Have we tried to add this module to a list before, if so, do not continue
        } else {
            $intent = array();
        }

        array_push($intent, $m);

        $dependencies = explode(",", trim($module['dependencies']));
        foreach($dependencies as $dependency) {
            if ($dependency != "") {
                addModuleToList($mList, trim($dependency), $level++, $intent);
            }
        }
        
        array_pop($intent);

        $pos = array_search($m, $mList);
        if ($pos === FALSE) {
            array_push($mList, $m);
        }
    }

    $pd = CInstall::getPhaseData(PHASE_MODULES);
    $resetTables = isset(CInstall::$dbSettings->resetDatabase) ? true : false;
    $hf = fopen(Mt::$dataPath . "/installation.dat", "a");
    if ($pd == 0) {
        CInstall::setPhaseData(1);
        $modules = CModules::searchModules();
        $GLOBALS['modules'] = $modules;

        $mList = array();

        foreach($modules as $key => $module) {
            addModuleToList($mList, $module);
        }

        CSession::set("installDetectedModules", json_encode($modules));
        CSession::set("installDetectedModulesO", json_encode($mList));

    } else if ($pd == 1) {
        if (isset($_POST['setModules']) && isset($_POST['modules'])) {
            CStatus::set(0);
            $installList = explode(",", $_POST['modules']);
            CModules::installModules($installList, $resetTables);
            $errors = CStatus::getErrors();
            if (!is_array($errors) || count($errors) == 0 ) {
                fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "ModulesInstall Success: {$_POST['modules']}\n");
                if (isset($_POST['lastModule'])) {
                    CInstall::commit();
                    CInstall::setInstallPhase(PHASE_MODULES + 1);
                    fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "ModulesComplete\n");
                }
                fclose($hf);
                die('{"status":"OK", "messages" : ' . json_encode(CStatus::getMessages()) . ', "errors" : ' . json_encode(CStatus::getErrors()) . '}');
            }
            fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "ModulesInstall Failed: {$_POST['modules']}\n");
            fclose($hf);
            die('{"status":"Error", "messages" : ' . json_encode(CStatus::getErrors()) . '}');
        }
    } else {
        die();
    }

    $modules = json_decode(CSession::get("installDetectedModules"), true);
    $mList = json_decode(CSession::get("installDetectedModulesO"), true);
    $installedModules = CSession::getArrayPlain("installedModules") ?? array();
    fwrite($hf, gmdate("[Y-m-d H:i:s] ") . "ModulesStart\n");
    fclose($hf);

    ?>
    <section class="content">
        <div class="container-fluid v-align">
            <div class="row  d-flex align-self-center justify-content-center">
                <div class='col-md-6 col-sm-10 p-0 m-5'>
                    <div class="card">
                    <h3 class="col-12 bg-primary pl-3 p-2">Setup Modules</h3>
                    <div class="p-3">
                        <form onsubmit="return false;" method="post" id="modulesForm">
                            <div class="card-body p-0 m-0">
                                <div class="logo d-flex align-self-center justify-content-end mt-0 p-0 mb-3"><a
                                            href="home"><img
                                                src='<?php echo \Helpers\HtmlHelper::link("Assets/img/hlogo"); ?>'
                                                height='20px'/></a></div>
                                <input type='hidden' name='setModules' value='1'/>
                                <div class="row">
                                    <?php
                                    foreach ($mList as $key) {
                                        $module = $modules[$key];
                                        ?>
                                        <div class="col-md-6 col-sm-12">
                                            <div class="form-group mb-0">
                                                <div class="icheck-primary icheck-inline">
                                                    <input type="checkbox" label="<?= $module['description'] ?>" <?php
                                                        if (!in_array($key, $installedModules)) echo "checked";
                                                    ?> id='<?php echo $key ?>'
                                                           name="<?php echo $key ?>">
                                                    <label for="<?php echo $key ?>"><?php echo $module['description'] ?>
                                                        (<?php echo $module['label'] ?>)</label>
                                                </div>
                                            </div>
                                        </div>
<?php
                                    } ?>
                                </div>
                                <div class="row">
                                    <div class="col-12" id="installProgressText">

                                    </div>
                                </div>

                                <div class="row col-12 m-0 mt-3 p-0">
                                    <div class="col-6 d-flex justify-content-start m-0 p-0">
                                        <button id="install-proceed" type="submit"
                                                class="btn btn-success pl-2 pr-2">Install Modules
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
        var frm = document.getElementById("modulesForm");
        var initPhase = function () {
            $('#modulesForm').validate({
                rules: {
                },
                messages: {
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
                    var fData = $("#modulesForm").serializeArray();
                    if (installBusy(true) == false) return false;

                    (async function() {
                        let modules = [];
                        $("#modulesForm input[type='checkbox']").each(function(e){
                            if(this.checked) {
                                modules[modules.length] = {name: this.name ,label: $(this).attr("label")};
                            }
                        });

                        let breakInstallation = false;
                        for(var c=0;c< modules.length;c++) {
                            let currentModule = modules[c];
                            $("#installProgressText").text("Installing module: " + currentModule.label);

                            let param = {"r": "installModules", "setModules" : "1", "modules" : currentModule.name};
                            if (c == (modules.length -1)) param.lastModule = true;
                            await $.post("Install?page=Modules", param, (response)=>{
                                    try {
                                        var m = JSON.parse(response);
                                        if (m["status"] == "OK") {
                                            document.getElementById(currentModule.name).checked = false;
                                            //installSuccessful(false);
                                            if (c == (modules.length -1)) installSuccessful(false);
                                        } else {
                                            showStatusDialog(response, "Error. Contact your administrator");
                                            installBusy(false);
                                            $("#installProgressText").text("Error on module: " + currentModule.label);
                                            breakInstallation = true;
                                        }
                                    } catch (e) {
                                        installBusy(false);
                                        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                                        $("#installProgressText").text("Error on module: " + currentModule.label);
                                        breakInstallation = true;
                                    }
                            });

                            if (breakInstallation) break;
                        }
                    })();
                }
            });

        }

    </script>

    <?php

}