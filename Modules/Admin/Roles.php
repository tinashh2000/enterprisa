<?php
namespace Roles;


use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Session\CSession;
use Api\CPrivilege;
use Api\CPriv;
use Admin\CAdmin;
use Api\Mt;

require_once("Scripts/CheckLogin.php");

CPrivilege::isAdmin();

require_once("Scripts/HtmlHelper.php");

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Admin");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_]);
HtmlHelper::PageStartX(
    ["title" => "User Roles", "description" => "Users Roles", "path" =>
        ["Roles" => "Users Roles"]], ['Admin/Css/Roles.css']);

?>

    <div class="mt-main-body">

        <div class="row">
            <div class="col-12">
                            <div id='roleData'>

                                <div id="mRoleToolbar">
                                    <div class="form-inline" role="form">
                                        <div>
                                            <a href="#" onclick='newRole()'><i class='fas fa-plus'></i> New Role</a>
                                        </div>
                                        <!--            <button id="ok" type="submit" class="btn btn-primary"></button>-->
                                    </div>
                                </div>
                                <table
                                    id="rolesTable"
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
                                    data-editable-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchRoles"
                                    data-toolbar="#mRoleToolbar"
                                    data-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchRoles">
                                    <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true" class='id-column'>Id</th>
                                        <th data-field="name" data-sortable="true">Name</th>
<!--                                        <th data-field="name" data-sortable="true">Name</th>-->
<!--                                        <th data-field="name" data-sortable="true">Name</th>-->
                                    </tr>
                                    </thead>
                                    <tbody id='rolesBody'>
                                    </tbody>
                                </table>
                            </div></div></div></div>
<?php
HtmlHelper::PageFooter();
HtmlHelper::newModalX("Role", __DIR__ . "/Contents", "modal-lg");
HtmlHelper::PageEndX();
