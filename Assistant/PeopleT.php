<?php

namespace Assistant;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Helpers\HtmlHelper;
use Api\Mt;

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_MAGICDIV_]);
HtmlHelper::PageStartX(
    ["title" => "People", "description" => "People", "path" =>
        ["People" => "People"]], null);

?>
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-12">

                        <div id='contactData'>
                            <div id="mContactToolbar">
                                <div class="form-inline" role="form">
                                    <div>
                                        <a href="#" onclick='newPerson()'><i class='fas fa-plus'></i>&nbsp;New Person</a>
                                    </div>
                                    <div class="ml-4">
                                        <a href="People"><i class='fas fa-sync'></i>&nbsp;Alternate View</a>
                                    </div>
                                    <!--            <button id="ok" type="submit" class="btn btn-primary"></button>-->
                                </div>
                            </div>
                            <table
                                    id="contactsTable"
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
                                    data-sort-order="ASC"
                                    data-sort-name="startDate"
                                    data-editable-emptytext="Default empty text."
                                    data-editable-url="Helper/FetchPeople"
                                    data-toolbar="#mContactToolbar"
                                    data-url="../Helpers/FetchPeople">
                                <thead>
                                <tr>
                                    <th data-field="name" data-formatter="personFormatter" data-sortable="true">Name</th>
                                    <th data-field="phone" data-sortable="true">Phone</th>
                                    <th data-field="email" data-sortable="true">Email</th>
                                    <th data-field="address" data-sortable="true">Address</th>
<!--                                    <th data-field="description" data-sortable="true" class='description-column'>Description</th>-->
                                </tr>
                                </thead>
                                <tbody id='contactsBody'>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
<?php
HtmlHelper::newModal("Person", __DIR__ . "/Contents", "modal-xl");
HtmlHelper::PageFooter(array("Assets/js/countries-select2.js", "Assets/js/Person.js"));
?>

<script src='Js/NewPerson.js'></script>

<?php
HtmlHelper::PageEndX();

