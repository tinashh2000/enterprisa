<?php

namespace Assistant;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Ffw\Crypt\CCrypt9;
use Helpers\HtmlHelper;
use Api\Mt;

$subject = "";
$message = "";
$recipients = "";

if (isset($_GET['inline'])) {
    $attachment = CCrypt9::unScrambleText($_GET['inline']);
    $p = json_decode($attachment, true);

    if (isset($p['subject']) && isset($p['message']) && isset($p['recipients'])) {
        $subject = $p['subject'];
        $message = $p['message'];
        $recipients = json_encode($p['recipients']);
    } else {
        echo $p['subject'] . $p['message'] . $p['recipients'];
    }
}


HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_VALIDATE_, HtmlHelper::_SUMMERNOTE_, HtmlHelper::_SELECT2_]);
HtmlHelper::PageStartX(
    ["title" => "Messages", "description" => "Messages", "path" =>
        ["Messages" => "Messages"]], array('Assistant/Css/Messages.css'), HtmlHelper::FLAG_NOMENU);
?>

<div class="mt-main-body">
<div class="row">
                <?php
//                print_r($recipients);
                require_once(__DIR__ . "/Contents/NewMessageContent.php"); ?>
            </div>
        </div>

<?php if ($recipients != "") { ?>
<script>
    let extraRecipients = JSON.parse('<?= $recipients ?>');
</script>
<?php } ?>

<?php
HtmlHelper::PageFooter(array('Assistant/Js/Messages.js'));
HtmlHelper::PageEndX(); ?>

<script>
    $(function (){
        $('#compose-textarea').summernote({height: 140, width: '100%'})
    });
</script>