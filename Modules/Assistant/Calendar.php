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
use Api\Authentication\CAuth;

require_once("Scripts/CheckLogin.php");

//CPrivilege::verifyPrivilege(CEvent::EVENTS_READ);

require_once("Scripts/HtmlHelper.php");

CAssistant::init(false);

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_SUMMERNOTE_]);
HtmlHelper::PageStartX(
    ["title"=>"Events Calendar", "description" => "Events", "path" =>
        ["Events" => "Events Calendar"]], ["Assets/plugins/fullcalendar/fullcalendar.bundle.css"]);

$canReadEvents = CPrivilege::checkList(CAssistant::EVENTS_READ);
$canWriteEvents = CPrivilege::checkList(CAssistant::EVENTS_WRITE);
?>

    <div class="mt-main-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">

                                <div class="float-right d-inline-block">
                                    <?php if ($canReadEvents) {?>
                                    <a href="#" onclick='newEvent()'><span class='fas fa-plus d-inline'></span>&nbsp; New Event</a>
                                    <?php } ?>
                                </div>
                                <h5>Events Calendar</h5>
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    <div>

                                    </div>
                                    <div class="col-12">
                                        <div id='calendar'></div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div></div></div>


<?php

if ($canWriteEvents) {
    HtmlHelper::addJsFile("Assistant/Js/Events.js");
}
HtmlHelper::addJsFile("Assistant/Js/Calendar.js");
HtmlHelper::PageFooter(["Assets/plugins/fullcalendar/fullcalendar.bundle.js"]);

if ($canWriteEvents)
    HtmlHelper::newModalX("Event", __DIR__ . "/Contents"); ?>
<?php HtmlHelper::PageEndX();
