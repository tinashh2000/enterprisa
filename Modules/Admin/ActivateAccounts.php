<?php
namespace Users;


use Ffw\Crypt\CCrypt8;
use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Session\CSession;
use Api\CPrivilege;
use Api\CPriv;
use Admin\CAdmin;
use Api\Mt;
use Api\Users\CUser;
use Ffw\Status\CStatus;
require_once("Scripts/CheckLogin.php");

CPrivilege::isAdmin();

require_once("Scripts/HtmlHelper.php");

if (isset($_POST['activate'])) {
    $a = CCrypt8::unScrambleText($_POST['activate']);

    $thatTime = strtotime($a);
    $curTime = strtotime("now");
    $delay = $curTime - $thatTime;
    if ($delay > 60) {
        echo "Operation has failed. Please try again";
        die("<script>setTimeout(()=>{window.location.href='ActivateAccounts';}, 5000);</script>");
    } else {
        CUser::activateAccounts();
        $errors = CStatus::getErrors();
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        die("This operation has been completed. Click <a href=''>here</a> to continue");
    }
}

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Admin");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_]);
HtmlHelper::PageStartX(
    ["title" => "User Admin", "description" => "Users Administration", "path" =>
        ["Users" => "Activation Link"]], ['Admin/Css/Users.css']);

?>
<section class="content col-12">
    <div class='row d-flex align-items-center justify-content-center'>
        <div class="col-12 vh-100">
            <div class="d-flex align-items-center justify-content-center">
                <!-- jquery validation -->
                <div class="col-md-6 p-3 mt-4 pt-4 pb-4  card">
                    <div>
                        <h3>Activate User Accounts</h3>
                    </div>
                    <form  method='post'>

                        <p>If you wish to activate all user accounts, and send an access link to the users, click the button below.</p>
                        <p>Each user will be prompted to enter a password of his/her choice, which can be subsequently used to login</p>

                    <input type="hidden" name="activate" value="<?php  echo CCrypt8::scrambleText(gmdate("Y-m-d H:i:s")); ?>" />
                    <div><button class="btn btn-primary" type="submit">Continue</button></div>
                    </form></div></div></div></div></section>
<?php
HtmlHelper::PageFooter(array("Assets/js/countries-select2.js"));
HtmlHelper::includeJS('Admin/Js/Users');

$isAdmin = true;

HtmlHelper::newModalX("User", __DIR__ . "/Contents", "modal-xl"); ?>

?>
<script>isAdmin = true;</script>
<?php HtmlHelper::PageEndX();

