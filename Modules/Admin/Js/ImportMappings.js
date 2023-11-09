var importMappingsForm;
var noContact = 0;
$(function(){
    importMappingsForm = document.getElementById("newImportMappingsForm");

    $('#newImportMappingsForm').validate({
        rules: {
            name: {
                required: true,
                minlength: 2
            },
            privileges: {
                required: true
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

            form.privilegesList.value =  $("#sprivilegesList").select2("val").toString();

            AdminAuth((auth) => {
                form.auth.value = auth;
                var fData = $("#newImportMappingsForm").serializeArray();
                $.post(eGotoAbsLink("Api/ImportMappings"), fData, (response)=>{
                    try {
                        var m = JSON.parse(response);
                        if (m['status'] == "OK") {
                            $('#newImportMappingsModal').modal('hide');
                            MessageBox(m["message"], false);
                            // $("#importMappingsTable").bootstrapTable("refresh");
                            refreshXDisplay("importMappings");
                        } else {
                            MessageBox(m["message"], true);
                        }
                    } catch (e) {
                        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                    }
                });
            });
            return false;
        }
    });

    $('#importMappingsTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
        editImportMappings(row);
    });
});

function getImportMappings(start=0, limit=20) {
    $.post(eGotoAbsLink("Api/ImportMappings"), {"r" : "fetch", "start" : start, "limit" : limit}, onGetImportMappings);
}

function onGetImportMappings(response) {
    try {
        var m = JSON.parse(response);
        //showStatusDialog(response, "Error. Contact your administrator");
    }
    catch(e) {
        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
    }
}

function initForm(r=null) {
    importMappingsForm.reset();
    if (r == null) {
        importMappingsForm.r.value = "create";

        importMappingsForm.name.value="";
        importMappingsForm.description.value="";

        $(".newImportMappingsCreateBtn span").text("Create");
        $(".newImportMappingsDeleteBtn").addClass("d-none");
        $("#newImportMappingsTitle").text("New Import Mappings");
    } else {
        importMappingsForm.r.value = "edit";
        importMappingsForm.id.value = r.id;
        importMappingsForm.name.value=r.name;
        importMappingsForm.description.value=r.description;

        $(".newImportMappingsCreateBtn span").text("Update");
        $(".newImportMappingsDeleteBtn").removeClass("d-none");
        document.getElementById("newImportMappingsTitle").innerText = "Edit Import Mappings";
    }
}

function editImportMappings(ele) {
    $.post(eGotoAbsLink("Api/ImportMappings"), {"r" : "get", "id" : ele.id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["importMappings"];
//            if  (c.name != ele.importMappingsname) return;
            initForm(c);
            $('#newImportMappingsModal').modal({backdrop: 'static', keyboard: false});
            $('#newImportMappingsModal').modal('show');
        }catch(e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function newImportMappings() {
    initForm();
    $('#newImportMappingsModal').modal({backdrop: 'static', keyboard: false});
    $('#newImportMappingsModal').modal('show');
}

function doDeleteImportMappings() {
    var frm = document.getElementById("newImportMappingsForm");

    AdminAuth((auth) => {
        $.post(eGotoAbsLink("Api/ImportMappings"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newImportMappingsModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#importMappingsTable").bootstrapTable("refresh");
                    refreshXDisplay("importMappings")
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
            }

        });
    });
}