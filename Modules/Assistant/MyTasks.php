<?php

namespace Events;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\Mt;
use Helpers\HtmlHelper;
use Projects\CProject;

require_once("Scripts/CheckLogin.php");

//CPrivilege::verifyPrivilege(CEvent::EVENTS_READ);

require_once("Scripts/HtmlHelper.php");

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_SUMMERNOTE_, HtmlHelper::_MAGICDIV_]);
HtmlHelper::PageStartX(
    ["title" => "Tasks", "description" => "Tasks", "path" =>
        ["Tasks" => "Tasks"]], ["Assets/bundle/css/themify-icons.css", "Assets/bundle/css/icofont.css", "Assistant/Css/Task.css"]);
?>
<script>
    var tasksApiUrl;
    $(function () {

        tasksApiUrl = eGotoAbsLink("Assistant/Api/Task");
    });
</script>
<div class="mt-main-body">
    <div class="row">
        <div class="col-12 col-sm-6">
            <div class="card">
                <div class="card-header">
                    <h5>To Do List
                    </h5>
                </div>
                <div class="card-block">


                        <div class="form-group form-float d-inline-block" style="width: calc(100% - 5em) !important">
                            <input type="text" name="task-insert" placeholder="Enter new task name"
                                   class="form-control newTaskName"
                                   required>
                        </div>
                        <div class="d-inline-block align-self-start pl-2">
                            <button type="button"
                                    class="btn btn-success btn-icon img-circle  waves-effect waves-light"
                                    id="newTaskButton">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>

<?php
$GLOBALS['taskMagicDivSource'] = Mt::$appRelDir."/Helpers/FetchMyTasks";
require_once("Contents/TaskMagicDiv.php") ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once("Contents/JSTasks.php");
HtmlHelper::PageFooter(["Assets/plugins/jquery-longpress/jquery.longpress.js", "Assistant/Js/Tasks.js"]);
HtmlHelper::PageEndX() ?>
