<?php

namespace Enterprisa;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\CAssistant;
use Api\CSettings;
use Ffw\Crypt\CCrypt9;
use Helpers\HtmlHelper;
use Api\Mt;

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Admin");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_SUMMERNOTE_]);
HtmlHelper::PageStartX(
    ["title" => "Automailer Settings", "description" => "Automailer Settings", "path" =>
        ["EmailSettings" => "Automailer Settings"]], ['Admin/Css/Messages.css']);
?>
    <script>
        let emailSettingsApiUrl;
        $(function () {
            emailSettingsApiUrl = eGotoLink("Api/EmailSettings")
        });
    </script>
    <div class="mt-main-body">
        <div class="row">
            <div class="col-12">
                <div class="d-flex">
                    <div class="card col-12 col-lg-8 p-3">
                        <div class="card-header pl-0">
                            <h5>Automailer Settings</h5>

                        </div>
                        <div class="">
                            <?php
                            $data = CSettings::get("Automailer");
                            require_once(__DIR__ . "/../Assistant/Contents/EmailSettingsContent.php") ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $("#incomingSecurityPicker").val("<?= $data['incomingSecurity']  ?? ""?>").trigger("change");
            $("#outgoingSecurityPicker").val("<?= $data['outgoingSecurity']  ?? ""?>").trigger("change");
            $("#incomingDeliveryPicker").val("<?= $data['incomingDelivery']  ?? ""?>").trigger("change");
            $("#outgoingDeliveryPicker").val("<?= $data['outgoingDelivery']  ?? ""?>").trigger("change");
            $("#serviceProviderPicker").val("<?= $data['serviceProvider']  ?? ""?>").trigger("change");
        });
    </script>
    <?php
HtmlHelper::PageFooter(['Assistant/Js/EmailSettings.js']);
HtmlHelper::PageEndX();
