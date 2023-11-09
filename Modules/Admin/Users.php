<?php
namespace Users;

use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Session\CSession;
use Api\CPrivilege;
use Api\CPriv;
use Admin\CAdmin;
use Api\Mt;

require_once("Scripts/CheckLogin.php");

if (!CPrivilege::isAdmin()) {
    require(Mt::$appDir . "/404.php");
    die();
}

require_once("Scripts/HtmlHelper.php");

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Admin");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_JQUERY_STEPS_]);
HtmlHelper::PageStartX(
    ["title" => "User Admin", "description" => "Users Administration", "path" =>
        ["Users" => "Users Administration"]], ["Admin/Css/Users.css"]);

$isAdmin = true;

?>

<div class="ms_index_wrapper common_pages_space">
    <div class="row">
        <div class="col-12">
            <div id='userData'>
                <div id="mUserToolbar">
                    <div class="form-inline" role="form">
                        <div>
                            <a href="#" onclick='newUser()'><i class='fas fa-plus'></i> New User</a>
                        </div>
                        <!--            <button id="ok" type="submit" class="btn btn-primary"></button>-->
                    </div>
                </div>
                <table  id="usersTable"
                        data-show-columns="true"
                        data-search="true"
                        data-show-toggle="true"
                        data-pagination="true"
                        data-virtual-scroll="true"
                        data-toggle="table"
                        data-side-pagination="server"
                        data-server-sort="true"
                        data-query-params="defaultQueryParams"
                        data-response-handler="defaultResponseHandler"
                        data-resizable="true"
                        data-remember-order="true"
                        data-editable-emptytext="Default empty text."
                        data-editable-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchUsers"
                        data-toolbar="#mUserToolbar"
                        data-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchUsers">
                    <thead>
                    <tr>
                        <th data-field="id" data-sortable="true" data-visible="false" class='id-column'>Id</th>
                        <th data-field="name" data-sortable="true" data-formatter="userNameFormatter">Name</th>
                        <th data-field="phone" data-sortable="true" class="phone-column">Phone</th>
                        <th data-field="email" data-sortable="true" class="email-column">Email</th>
                        <th data-field="address" data-sortable="true" class="">Address</th>
                    </tr>
                    </thead>
                    <tbody id='usersBody'>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php

HtmlHelper::PageFooter(array("Assets/js/countries-select2.js", "Assets/js/Person.js", "Admin/Js/Users.js"));
HtmlHelper::newModalX("User", __DIR__ . "/Contents", "modal-xl");
HtmlHelper::PageEndX(); ?>
<script>isAdmin = <?= CPrivilege::isAdmin() ? "true" : "false" ?>;
    // initFW("#newUserForm", function() {
    //     submitUserForm(userForm);
    // });
</script>
