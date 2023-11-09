'use strict';

var dropDownEl = null;

$(document).ready(function () {


    var i = 7;
    $("#newTaskButton").on("click", function () {
        var task = $('.newTaskName').val().trim();
        if (task == "") {
            alert("Please enter task name");
        } else {
            let startDate = moment().format("YYYY-MM-DD HH:mm:ss");
            let endDate = moment().add(1, "month").format("YYYY-MM-DD HH:mm:ss");

            $.post(tasksApiUrl, {
                "r": "create",
                "extraId":$(".newTaskName").attr("extraId"),
                "name": task,
                "participants": currentUser,
                "status": _TASK_STATUS_PENDING,
                "priority": "4",
                "startDate": startDate,
                "endDate": endDate,
                "description": task
            }, (response) => {

                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        MessageBox(m["message"], false);
                        refreshXDisplay("tasks");
                        $(".newTaskName").val("");
                    } else {
                        MessageBox(m["message"], true);
                    }
                } catch (e) {
                    showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                }

            });
        }
    });

});

function getTasks() {
    $("#tasksList").html('<div class=""><div class=" d-flex justify-content-center align-items-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div></div>');

    $.post(tasksApiUrl, {"r": "fetch", "start": "0", "limit": "1000"}, (response) => {
        try {
            $("#tasksList").html("");
            var m = JSON.parse(response);
            if (m["status"] == "OK") {
                var tasks = m["taskx"];

            } else {
                MessageBox(m["message"]);
            }
        } catch (e) {

        }
    });
}

function delete_todo(e) {
    if (confirm("Are you sure you want to delete this task")) {
        $.post(tasksApiUrl, {"r": "delete", "id": e}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $(this).parent().parent().parent().parent().fadeOut();
                    MessageBox(m["message"], false);
                    // $('#mtask_' + e).fadeOut();
                } else {
                    MessageBox("Item deletion failed", true);
                }
            } catch (e) {
                alert("Error while deleting task" + response);
            }
        });
    }

}

function TaskStatus(id, status) {
    $.post(tasksApiUrl, {"r": "setStatus", "id": id, "status": status}, (response) => {
        try {
            var m = JSON.parse(response);
            if (m['status'] == "OK") {
                MessageBox(m["message"], false);
                refreshXDisplay("tasks");
            } else {
                MessageBox(m["message"], true);
            }
        } catch (e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function TaskDelete(id, status) {
    $.post(tasksApiUrl, {"r": "delete", "id": id}, (response) => {
        try {
            var m = JSON.parse(response);
            if (m['status'] == "OK") {
                MessageBox(m["message"], false);
                refreshXDisplay("tasks");
            } else {
                MessageBox(m["message"], true);
            }
        } catch (e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function myTasksFormatter(item) {
    item.taskClass = "";
    if (item.status == _TASK_STATUS_PENDING) {
        item.taskClass += " task-pending";
        item.statusDescription = "Pending";
    } else if (item.status == _TASK_STATUS_STARTED) {
        item.taskClass += " task-started";
        item.statusDescription = "Started";
    } else if (item.status == _TASK_STATUS_COMPLETED) {
        item.taskClass += " task-completed";
        item.statusDescription = "Completed";
    } else if (item.status == _TASK_STATUS_SUSPENDED) {
        item.taskClass += " task-suspended";
        item.statusDescription = "Suspended";
    }
    return -1;
}