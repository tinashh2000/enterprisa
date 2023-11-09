<?php

namespace Events;

use Helpers\HtmlHelper;
use Api\Mt;

HtmlHelper::CssInclude(HtmlHelper::_MAGICDIV_["Css"]);
HtmlHelper::CssInclude(["Assets/bundle/css/themify-icons.css", "Assets/bundle/css/icofont.css", "Assistant/Css/Task.css"]);
?>
<script>
    var tasksApiUrl;
    $(function () {
        tasksApiUrl = eGotoAbsLink("Assistant/Api/Task");
    });
</script>
<div class="card h-100">
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
require_once(__DIR__ . "/../../Contents/TaskMagicDiv.php") ?>
                </div>
</div>
    <?php
	require_once(__DIR__ . "/../../Contents/JSTasks.php");
HtmlHelper::uses([["Js" => ["Assets/plugins/jquery-longpress/jquery.longpress.js", "Assistant/Js/Tasks.js"]]]);
HtmlHelper::uses([["Js" => HtmlHelper::_MAGICDIV_["Js"]]]);
