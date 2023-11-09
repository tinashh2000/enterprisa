<?php

namespace Enterprisa;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\CAssistant;
use Ffw\Crypt\CCrypt8;
use Ffw\Crypt\CCrypt9;
use Helpers\HtmlHelper;
use Api\Mt;

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_SUMMERNOTE_]);
HtmlHelper::PageStartX(
    ["title" => "Email Settings", "description" => "Email Settings", "path" =>
        ["Messaging" => "Messages", "Email Settings" => "EmailSettings"]], ["Assistant/Css/Messages.css"]);
?>
<script>
    let emailSettingsApiUrl;
    $(function () {
            emailSettingsApiUrl = eGotoLink("Api/Message")
        });
</script>
    <div class="mt-main-body">
                    <div class="row">
                        <div class="container-fluid v-align">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="card col-12 col-md-8 p-3">
                                    <div class="card-header pl-0">
                                        <h5>Email Settings</h5>
                                    </div>
                                    <div class="">
                                    <?php

                                    $emailSettings = CAssistant::getEmailSettings();
                                    $data = json_decode(CCrypt9::unScrambleText($emailSettings['data']), true);
                                    $data['password'] = '';

                                    require_once(__DIR__ . "/Contents/EmailSettingsContent.php") ?>
                                    </div>
                                </div>
                            </div>
                        </div></div></div>

<script>

</script>
<?php
HtmlHelper::PageFooter("Assistant/Js/EmailSettings.js");
HtmlHelper::PageEndX();
