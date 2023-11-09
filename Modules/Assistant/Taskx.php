<?php

namespace Events;

require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\Mt;
use Helpers\HtmlHelper;

require_once("Scripts/CheckLogin.php");

//CPrivilege::verifyPrivilege(CEvent::EVENTS_READ);

require_once("Scripts/HtmlHelper.php");

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_SUMMERNOTE_, HtmlHelper::_MAGICDIV_]);
HtmlHelper::PageStartX(
    ["title" => "Tasks", "description" => "Tasks", "path" =>
        ["Tasks" => "Tasks"]], ["Assets/bundle/css/themify-icons.css", "Assets/bundle/css/icofont.css", "Assistant/Css/Task.css"]);
?>

    <div class="mt-main-body">
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>To Do List
                                </h5>
                                <label class="label label-success">Today</label>
                            </div>
                            <div class="card-block">
                                <div class="form-material">
                                    <div class="right-icon-control">
                                        <form class="form-material">
                                            <div class="form-group form-primary">
                                                <input type="text" name="task-insert" class="form-control add_task_todo"
                                                       required="">
                                                <span class="form-bar"></span>
                                                <label class="float-label">New Task</label>
                                            </div>
                                        </form>
                                        <div class="form-icon ">
                                            <button type="button"
                                                    class="btn btn-success btn-icon  waves-effect waves-light"
                                                    id="add-btn">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    id="tasksMagicDiv"
                                    class="magicDiv mt-3"
                                    data-magicDiv-paginate="true"
                                    data-magicDiv-renderer="renderTasks"
                                    data-magicDiv-source="<?php echo Mt::$appRelDir ?>/Helpers/FetchProjects?e=task"
                                    data-magicDiv-numRows="2">
                                    <div class="magicDivTemplate">
                                        <div class="row col-12" id="mtask_{id}">
                                            <div class="col-auto d-flex align-items-center pointer-event" style="width:1em"><i class="fas fa-ellipsis-v"></i></div>
                                            <div class="col to-do-label mt-1 mb-1">
                                                <div class="checkbox-fade fade-in-primary"><label
                                                            class="check-task"><span></span><input type="checkbox"
                                                                                                   onclick="check_label({id})"
                                                                                                   id="checkbox{id}"><span
                                                                class="cr"><i
                                                                    class="cr-icon icofont icofont-ui-check txt-primary"></i></span><span
                                                                class="task-title-sp">{name} ({progress}%)</span>
                                                        <div class="float-right hidden-phone"><i
                                                                    class="icofont icofont-ui-delete delete_todo"
                                                                    onclick="delete_todo({id});"></i></div>
                                                    </label></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



<?php
HtmlHelper::PageFooter(["Assets/plugins/jquery-longpress/jquery.longpress.js", "Assets/bundle/js/modernizr.js", "Assets/bundle/js/todo.js", "Assistant/Js/Tasks.js"]);
HtmlHelper::PageEndX();
