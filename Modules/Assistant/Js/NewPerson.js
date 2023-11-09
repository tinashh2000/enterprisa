var personForm;
var noPerson = 0;

$(function() {
    personForm = document.getElementById("newPersonForm");
    $("#profilePicFile").change(function () { this.pId = "profilePicImg"; readPictureURL(this); });
    $('#newPersonForm').validate({
        rules: {
            name: {
                required: true,
                minlength: 2
            },
        },
        messages: {
            name: {
                required: "Please enter a name",
                minlength: "Minimum length is 5 characters"
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

            if (!preparePersonForm(form)) return false;

            var frmData = new FormData(form);
            formBusy(form, true);

            AdminAuth((auth) => {
                frmData.append("auth", auth);
                jQuery.post({
                    url: eGotoAbsLink("Api/Person"),
                    type: "POST",
                    data: frmData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        formBusy(form, false);
                        try {
                            var m = JSON.parse(response);
                            if (m['status'] == "OK") {
                                $('#newPersonModal').modal('hide');
                                MessageBox(m["message"], false);
                                refreshXDisplay("people");
                            } else {
                                MessageBox(m["message"], true);
                            }
                        } catch (e) {
                            showStatusDialog(response, "Error. Contact your administrator");
                        }
                    }
                });
            });
            return false;
        }
    });

    $('#peopleTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
        editPerson(row);
    });

    if (typeof custData != "undefined") {
        custData =window.atob(window.atob(custData).split("").reverse().join(""));
        initForm(JSON.parse(custData));
    }
});

function initPersonXForm(frm, c=null) {
    initPersonForm(frm, c);
    if (c == null) {
        $("#newPersonCreateBtn span").text("Create");
        $(".newPersonDeleteBtn").addClass("d-none");
        $("#newPersonTitle").text("New Person");
    } else {
        $("#newPersonCreateBtn span").text("Update");
        $(".newPersonDeleteBtn").removeClass("d-none");
        $("#newPersonTitle").text("Edit Person");
    }
}

function editPerson(ele) {
    var id = ele.id;
    $.post(eGotoLink("Api/Person"), {"r" : "get", "id" : id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["person"];
            initPersonXForm(personForm, c);
            $('#newPersonModal').modal({backdrop: 'static', keyboard: false});
            $('#newPersonModal').modal('show');
        }catch(e) {
            showStatusDialog(e.stack  + " .. " + response, "Something went wrong");
        }
    });
}

function newPerson() {
    initPersonXForm(personForm);
    $('#newPersonModal').modal({backdrop: 'static', keyboard: false});
    $('#newPersonModal').modal('show');
}

function doDeletePerson() {
    var frm = document.getElementById("newPersonForm");

    if (!confirm("Are you sure you want to delete this record?")) return;

    AdminAuth((auth) => {
        $.post(eGotoLink("Api/Person"), {"r": "delete", "auth": auth, "id": frm.personId.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newPersonModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#peopleTable").bootstrapTable("refresh");
                    refreshXDisplay("people");
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                alert(e);
            }

        });
    });
}

function personFormatter(value, row, index) {
    return "<a href='" + homeLink + "/people/" + row.id + "'><span class='img-username'><img class='img-thumbnail' src='"+homeLink+"/people/" + row.id+ "/pic' />"+row.name+"</span></a>";
}

function supplierFormatter(value, row, index) {
    return "<a href='" + homeLink + "/suppliers/" + row.id + "'><span class='img-username'><img class='img-thumbnail' src='"+homeLink+"/suppliers/" + row.id+ "/pic' />"+row.name+"</span></a>";
}

function personDblClick(ele, item) {
    editPerson(item);
}
