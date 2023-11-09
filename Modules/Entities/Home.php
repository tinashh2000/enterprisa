<?php
namespace Entities;

use Api\CPersonEntity;
use Api\CPrivilege;
use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Session\CSession;
use Api\Mt;
use Entities\CEntity;

require_once("Scripts/CheckLogin.php");

CPrivilege::verifyPrivilege(CEntities::ENTITY_READ);

require_once("Scripts/HtmlHelper.php");

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Entities");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_MAGICDIV_, HtmlHelper::_SUMMERNOTE_]);
HtmlHelper::PageStartX(
    ["title"=>"Entities", "description" => "Entities", "path" =>
        ["Home" => "Entities"]], ['Entities/Css/Inventory.css'] );

//CEntity::init(true);
//CPersonEntity::init(true);

?>

    <div class="mt-main-body">

        <div class="row">
            <div class="col-12">
                <table class='col-12'>
                    <tr>
                        <td>
                            <div id='entityData'>

                                <div id="mEntityToolbar">
                                    <div class="d-flex align-items-center h-100">
                                        <div class="mr-5"><a href="#" onclick='newEntity()'><i class='fas fa-plus'></i> &nbsp;New Entity</a></div>
<!--                                        <div style="width:20em"><select id="mEntitiesPicker" data-placeholder="Select an entity"></select> </div>-->
                                        <div style="width:20em">
                                            <select class="select2" name='module'>
                                                <option value="*">All</option>
                                                <?php
                                                $m = CModule::getModulesInfo();
                                                foreach($m as $k=>$mdl)
                                                    echo "<option value='$k'>{$mdl['Name']}</option>";
                                                ?>
                                            </select>
                                        </div>
                                        <div> <button class="btn" id="refreshEntitiesBtn" ><i class="fas fa-sync"></i></button></div>
                                    </div>
                                </div>
                                <table
                                        id="entitiesTable"
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
                                        data-toolbar="#mEntityToolbar"
                                        data-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchEntities">
                                    <thead>
                                    <tr>
                                        <th data-field="creationDate" data-sortable="true" data-formatter="dateFormatter" class='date-column'>Creation Date</th>
                                        <th data-field="name" data-sortable="true">Name</th>
                                        <th data-field="classification" data-sortable="true">Classification</th>
                                        <th data-field="module" data-sortable="true">Module</th>
                                        <th data-field="description" data-sortable="true" class='description-column'>Description</th>
                                    </tr>
                                    </thead>
                                    <tbody id='entitiesBody'>

                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div></div></div>
<?php
HtmlHelper::PageFooter(['Entities/Js/Entities.js']);
HtmlHelper::newModal("Entity", __DIR__ . "/Contents", "modal-lg");
HtmlHelper::PageEndX();