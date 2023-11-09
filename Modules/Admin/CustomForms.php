<?php

namespace Enterprisa;

use Admin\CAdmin;
use Api\CDataFields;
use Api\CPrivilege;
use Helpers\HtmlHelper;
use Api\Mt;
use ImportMappings\CImportMapping;

require_once(__DIR__ . "/Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");

CPrivilege::verifyPrivilege(CPrivilege::checkList(CAdmin::BASIC_PERMISSION));

require_once("Scripts/HtmlHelper.php");


HtmlHelper::uses([["Css" => ["Assets/plugins/dragula/dragula.min.css"]]]);
HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Admin");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_MAGICDIV_, HtmlHelper::_SUMMERNOTE_]);
HtmlHelper::PageStartX(
    ["title" => "Data Fields
    ", "description" => "Data Fields", "path" =>
        ["Admin/DataFields" => "Data Fields"]], [], 0);

$GLOBALS['dataFieldsConfig'] = ["name" => "DataFields", "fields" => []];

//require("Api/Classes/CDataFields.php");
//require("Api/Classes/CImportMapping.php");
//CDataFields::init(true);
//CImportMapping::init(true);
?>
<style>
    .task-list-items:before {
        content: "No items" !important;
    }
</style>

    <div class="mt-main-body">
        <div class="row">
            <div class="col-12">
                <div id='dataFieldsData'>

                    <div id="mRoleToolbar">
                        <div class="form-inline" role="form">
                            <div>
                                <a href="#" onclick='newDataFields()'><i class='fas fa-plus'></i> Create Custom Data Fields</a>
                            </div>
                            <!--            <button id="ok" type="submit" class="btn btn-primary"></button>-->
                        </div>
                    </div>
                    <table
                            id="dataFieldsTable"
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
                            data-editable-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchDataFields"
                            data-toolbar="#mRoleToolbar"
                            data-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchDataFields">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" class='id-column'>Id</th>
                            <th data-field="name" data-sortable="true">Name</th>
                        </tr>
                        </thead>
                        <tbody id='dataFieldsBody'>
                        </tbody>
                    </table>
                </div></div></div></div>
<?php
HtmlHelper::newModalX("DataFields", __DIR__ . "/Contents");
HtmlHelper::PageFooter();
HtmlHelper::PageEndX();
