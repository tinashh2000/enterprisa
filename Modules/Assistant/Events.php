<?php

namespace Events;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\CAssistant;
use Api\CPrivilege;
use Api\Mt;
use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Session\CSession;
use Api\CEvent;

require_once("Scripts/CheckLogin.php");

CPrivilege::verifyPrivilege(CAssistant::EVENTS_BASIC);

require_once("Scripts/HtmlHelper.php");

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_SUMMERNOTE_]);
HtmlHelper::PageStartX(
    ["title"=>"Events", "description" => "Events", "path" =>
        ["Events" => "Events"]], null );

?>

    <div class="mt-main-body">

                <div class="row">

                    <div class="col-12">

                        <div id='eventData'>

                            <div id="mEventToolbar">
                                <div class="form-inline" role="form">
                                    <div>
                                        <a href="#" onclick='newEvent()'><i class='fas fa-plus'></i> &nbsp;&nbsp; New Event</a>
                                    </div>
                                    <!--            <button id="ok" type="submit" class="btn btn-primary"></button>-->
                                </div>
                            </div>
                            <table
                                id="eventsTable"
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
                                data-editable-url="Helper/FetchEvents"
                                data-toolbar="#mEventToolbar"
                                data-url="<?php echo Mt::$appRelDir ?>/Helpers/FetchEvents">
                                <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true" data-visible="false" class='id-column'>Id</th>
                                    <th data-field="creationDate" data-sortable="true"  data-visible="false" data-formatter="dateFormatter" class='date-column'>Creation Date</th>
                                    <th data-field="startDate" data-sortable="true" data-formatter="dateFormatter" class='date-column'>Event Date</th>
                                    <th data-field="endDate" data-sortable="true" data-formatter="dateFormatter" class='date-column'>Event Date</th>
                                    <th data-field="name" data-sortable="true">Name</th>
                                    <th data-field="description" data-sortable="true" class='description-column'>Description</th>
                                </tr>
                                </thead>
                                <tbody id='eventsBody'>

                                </tbody>
                            </table>
                        </div></div></div></div>
<?php
HtmlHelper::newModalX("Event", __DIR__ . "/Contents");
HtmlHelper::PageFooter(["Assets/plugins/jquery-longpress/jquery.longpress.js", 'Assistant/Js/Events.js']);
HtmlHelper::PageEndX();
