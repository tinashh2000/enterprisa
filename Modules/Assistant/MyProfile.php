<?php
namespace Enterprisa;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Helpers\HtmlHelper;
use Api\Users\CurrentUser;
use Api\Users\CUser;
use Api\Mt;

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_JQUERY_STEPS_]);
HtmlHelper::PageStartX(
    ["title" => "Profile", "description" => "User Profile", "path" =>
        ["Home" => "User Profile"]], null);

HtmlHelper::includeJS("Assets/js/countries-select2.js"); ?>

<!-- section start -->
<section class="section-big-pt-space b-g-light">
    <div class="collection-wrapper">
        <div class="custom-container">
            <div class="row">
                <div class="col-12 p-4">
                    <?php require_once(__DIR__ . "/../Admin/Contents/NewUserContent.php"); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
HtmlHelper::PageFooter(array("Assets/js/countries-select2.js", "Assets/js/Person.js", "Admin/Js/Users.js"));
HtmlHelper::PageEndX(); ?>
<script>
    $(() => {
        editUser2();
    });

    initFW("#newUserForm", function () {
        submitUserForm(userForm);
    });
</script>