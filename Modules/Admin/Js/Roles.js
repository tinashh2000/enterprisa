var roleForm;
var noContact = 0;
$(function(){
    roleForm = document.getElementById("newRoleForm");

    $('#newRoleForm').validate({
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
                var fData = $("#newRoleForm").serializeArray();
                $.post(eGotoAbsLink("Api/Roles"), fData, (response)=>{
                    try {
                        var m = JSON.parse(response);
                        if (m['status'] == "OK") {
                            $('#newRoleModal').modal('hide');
                            MessageBox(m["message"], false);
                            // $("#rolesTable").bootstrapTable("refresh");
                            refreshXDisplay("roles");
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

    $('#rolesTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
        editRole(row);
    });
});

function getRoles(start=0, limit=20) {
    $.post(eGotoAbsLink("Api/Roles"), {"r" : "fetch", "start" : start, "limit" : limit}, onGetRoles);
}

function onGetRoles(response) {
    try {
        var m = JSON.parse(response);
        //showStatusDialog(response, "Error. Contact your administrator");
    }
    catch(e) {
        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
    }
}

function initForm(r=null) {
    if (r == null) {
        roleForm.r.value = "create";

        roleForm.name.value="";
        roleForm.description.value="";

        $("#sprivilegesList").val("").trigger("change");
        $("#sprivileges").val("").trigger("change");

        // document.getElementById("newRoleCreateBtn").getElementsByTagName("span")[0].innerText = "Create";
        // document.getElementById("newRoleDeleteBtn").style.display = "none";
        $(".newRoleCreateBtn span").text("Create");
        $(".newRoleDeleteBtn").addClass("d-none");
        $("#newRoleTitle").text("New Role");
    } else {
        roleForm.r.value = "edit";
        roleForm.id.value = r.id;
        var privilegesList = r.privilegesList.split(",");
        var privileges = r.privileges.split(",");
        $("#newRoleForm #sprivilegesList").val(privilegesList).trigger("change");
        $("#newRoleForm #sprivileges").val(privileges).trigger("change");

        roleForm.name.value=r.name;
        roleForm.description.value=r.description;

        $(".newRoleCreateBtn span").text("Update");
        $(".newRoleDeleteBtn").removeClass("d-none");
        document.getElementById("newRoleTitle").innerText = "Edit Role";
    }
}

function editRole(ele) {
    $.post(eGotoAbsLink("Api/Roles"), {"r" : "get", "id" : ele.id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["role"];
//            if  (c.name != ele.rolename) return;
            initForm(c);
            $('#newRoleModal').modal({backdrop: 'static', keyboard: false});
            $('#newRoleModal').modal('show');
        }catch(e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function newRole() {
    initForm();
    $('#newRoleModal').modal({backdrop: 'static', keyboard: false});
    $('#newRoleModal').modal('show');
}

function doDeleteRole() {
    var frm = document.getElementById("newRoleForm");

    AdminAuth((auth) => {
        $.post(eGotoAbsLink("Api/Roles"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newRoleModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#rolesTable").bootstrapTable("refresh");
                    refreshXDisplay("roles")
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
            }

        });
    });
}