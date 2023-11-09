var eventForm;

$(function(){
    eventForm = document.getElementById("newEventForm");
    $('#newEventForm').validate({
        rules: {
            name: {
                required: true,
                minlength: 2
            },
            startDateC: {
                required: true
            },
            startTimeC: {
                required: true
            },
            startDateC: {
                required: true
            },
            startTimeC: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please enter a name",
                minlength: "Minimum length is 5 characters"
            },
            openingDateC: {
                required: "Enter the starting date",
            },
            openingTimeC: {
                required: "Enter the starting time",
            },
            closingDateC: {
                required: "Enter the ending date",
            },
            closingTimeC: {
                required: "Enter the ending time",
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            form.startDate.value = moment($(form.startDateC).val() + " " + $(form.startTimeC).val(), "YYYY-MM-DD HH:mm").format("YYYY-MM-DD HH:mm");
            form.endDate.value = moment($(form.endDateC).val() + " " + $(form.endTimeC).val(), "YYYY-MM-DD HH:mm").format("YYYY-MM-DD HH:mm");
            var fData = $("#newEventForm").serializeArray();
            $.post(eGotoLink("Api/Event"), fData, (response) => {
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        $('#newEventModal').modal('hide');
                        MessageBox(m["message"], false);
                        typeof isWrapped != "undefined" && isWrapped ? window.close() : refreshXDisplay("events"); //$("#eventsTable").bootstrapTable("refresh");
                        if (typeof loadEvents == "function") {
                            window.location.reload(true);
                        }
                    } else {
                        MessageBox(m["message"], true);
                    }
                } catch(e) {
                    showStatusDialog(e.stack, response);
                }
            });
            return false;
        }
    });


    $('#eventsTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
            editEvent(row.id);
    });
});

function onGetEvents(response) {
    try {
        var m = JSON.parse(response);
        showStatusDialog(response, "Error. Contact your administrator");
    }
    catch(e) {
        showStatusDialog(e.stack, response);
    }
}

function initEventsForm(c=null) {

    var frm = document.getElementById("newEventForm");
    frm.reset();

    if (c == null) {
        frm.r.value = "create";
        frm.id.value = 0;
        frm.name.value = "";

        $(frm.visibility).val("*");
        $(frm.message).summernote("code", "");
        $(frm.notes).summernote("code", "");

        $("#newEventCreateBtn span").text("Create");
        $(".newEventDeleteBtn").addClass("d-none");
        $("#newEventTitle").text("New Event");
    } else {
        frm.r.value = "edit";
        frm.id.value = c.id;
        frm.name.value = c.name;
        frm.venue.value = c.venue;
        frm.location.value = c.location;
        frm.description.value = c.description;
        $(frm.message).summernote("code", c.message);
        $(frm.notes).summernote("code", c.notes);
        $(frm.visibility).val(c.visibility);

        frm.startDateC.value = moment(c.startDate, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD");
        frm.startTimeC.value = moment(c.startDate, "YYYY-MM-DD HH:mm:ss").format("HH:mm:ss");
        frm.endDateC.value = moment(c.endDate, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD");
        frm.endTimeC.value = moment(c.endDate, "YYYY-MM-DD HH:mm:ss").format("HH:mm:ss");

        $("#newEventCreateBtn span").text("Update");
        $(".newEventDeleteBtn").removeClass("d-none");
        $("#newEventTitle").text("Edit Event");
    }
}

function editEvent(id) {
    $.post(eGotoLink("Api/Event"), {"r" : "get", "id" : id}, (response)=> {
        try {
            var m = JSON.parse(response);
            if (m["status"] == "OK") {
                var c = m["event"];
                initEventsForm(c);
                $('#newEventModal').modal({backdrop: 'static', keyboard: false});
                $('#newEventModal').modal('show');
            } else {
                MessageBox(m["message"], true);
            }
        }catch(e) {
            alert( e+ " ::::  " + response);
        }
    });
}

function newEvent() {
    initEventsForm();
    $('#newEventModal').modal({backdrop: 'static', keyboard: false});
    $('#newEventModal').modal('show');
}

function doDeleteEvent() {
    var frm = document.getElementById("newEventForm");

    AdminAuth((auth) => {
        $.post(eGotoLink("Api/Event"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newEventModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#eventsTable").bootstrapTable("refresh");
                    refreshXDisplay("events");
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack, response);
            }

        });
    });
}