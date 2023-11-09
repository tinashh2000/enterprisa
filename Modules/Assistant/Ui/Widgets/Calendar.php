<?php
use Helpers\HtmlHelper;

HtmlHelper::CssInclude(["Assets/plugins/fullcalendar/fullcalendar.bundle.css", "Assets/bundle/css/calendar.css"]);
?>
        <div class="card h-100">
            <div class="card-block">
    <div id='calendar' class="widget-content overlayScrollContainer"></div>

            </div>
        </div>

<?php
HtmlHelper::uses([["Js" => ["Assets/plugins/fullcalendar/fullcalendar.bundle.js", "Assistant/Js/Calendar.js"]]]);
