<?php

use Api\CTask;
?>
<div
        id="tasksMagicDiv"
        class="magicDiv mt-3"
        data-magicDiv-paginate="true"
        data-magicDiv-source="<?= $GLOBALS['taskMagicDivSource']?>"
        data-magicDiv-formatter="myTasksFormatter"
        data-magicDiv-numRows="2">
    <div class="magicDivTemplate">
        <div class="row col-12" id="mtask_{id}">
            <div class="ml-2 mt-1 mb-1">
                <div class="checkbox-fade fade-in-primary"><a class=""></a>
                    <ul class="d-inline-block p-0 m-0">
                        <li class="nav-item dropdown mr-3">
                            <a class="" href="#" id="taskMenuIcon{id}" data-bs-toggle="dropdown"
                               aria-expanded="false"><i class="fas fa-ellipsis-v ml-2 mr-2"></i></a>
                            <div class="dropdown-menu shadow-sm" data-dropdown-in="fadeIn"
                                 data-dropdown-out="fadeOut" aria-labelledby="taskMenuIcon{id}">
                                <ul>
                                    <li class="nav-item"
                                        onclick="TaskStatus({id},'<?= CTask::_STATUS_PENDING ?>');">
                                        <i class="fas fa-running"></i> &nbsp; Pending
                                    </li>
                                    <li class="nav-item"
                                        onclick="TaskStatus({id},'<?= CTask::_STATUS_STARTED ?>');">
                                        <i class="fas fa-running"></i> &nbsp; Started
                                    </li>
                                    <li class="nav-item"
                                        onclick="TaskStatus({id},'<?= CTask::_STATUS_COMPLETED ?>');">
                                        <i class="fas fa-clock"></i> &nbsp; Finished
                                    </li>
                                    <li class="nav-item"
                                        onclick="TaskStatus({id},'<?= CTask::_STATUS_SUSPENDED ?>');">
                                        <i class="fas fa-stop"></i> &nbsp; Suspended
                                    </li>
                                    <li class="nav-item" onclick="TaskDelete({id});"><i class="fas fa-trash"></i> &nbsp;
                                        Delete
                                    </li>
                                </ul>

                            </div>
                        </li>
                    </ul>

                    <label class="check-task Tasks {taskClass}"><input type="checkbox"
                                                                       onclick="check_label({id})"
                                                                       id="checkbox{id}"> &nbsp; <span
                                class="task-title-sp">{name} (<i>{statusDescription}</i>)</span>
                        <div class="float-right hidden-phone"><i
                                    class="icofont icofont-ui-delete delete_todo"
                                    onclick="DeleteTask({id});"></i></div>
                    </label></div>
            </div>
        </div>
    </div>
</div>