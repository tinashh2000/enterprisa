var userForm;
var noContact = 0;
var isAdmin = false;

userForm = document.getElementById("newUserForm");

$(function(){
    $("#newUserFrm .form-control").on("change", (e)=>{
        XChanges.change(e.currentTarget.name, e.currentTarget.value);
    });

    $('#usersTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
        editUser(row);
    });

    initRoleSelect2("#newUserForm select[name='rolesList']");

    $("#mCountryPicker").select2({
        minimumResultsForSearch: Infinity,
        width: '100%',
        data: countriesS2
    });


    $('#newUserForm').validate({
        rules: {
        },
        messages: {
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
            submitUserForm(userForm);
        }
    });

    userForm = document.getElementById("newUserForm");

    $("#profilePicFile").change(function () { this.pId = "profilePicImg"; readPictureURL(this); });
});

function prepareUserForm(form) {
    if (form.password.value != form.cpassword.value) {
        MessageBox("Password mismatch", true);
        return false;
    }

    if (isAdmin) {
        form.privilegesList.value = $("#sprivilegesList").select2("val").toString();
        form.roles.value = $("#newUserForm select[name='rolesList']").select2("val").toString();
    }
    return true;
}

function submitUserForm(form) {

    if (!prepareUserForm(form)) return;
    if (!preparePersonForm(form)) return;

    var frmData = new FormData(form);

    formBusy(form);
    AdminAuth((auth) => {
        frmData.append("auth", auth);
        jQuery.post({
            url: eGotoAbsLink("Api/Users"),
            type: "POST",
            data: frmData,
            processData: false,
            contentType: false,
            success: (response) => {
                formBusy(form, false);
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        $('#newUserModal').modal('hide');
                        MessageBox(m["message"], false);
                        // $("#usersTable").bootstrapTable("refresh");
                        refreshXDisplay("users");
                    } else {
                        MessageBox(m["message"], true);
                    }
                } catch (e) {
                    showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                }
            }
        });
    });
    return false;
}

function getUsers(start=0, limit=20) {
    $.post(eGotoAbsLink("Api/Users"), {"r" : "fetch", "start" : start, "limit" : limit}, onGetUsers);
}

function onGetUsers(response) {
    try {
        var m = JSON.parse(response);
    }
    catch(e) {
        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
    }
}

function initUserForm(frm, u=null) {
    initPersonForm(frm, u);
    if (u == null) {
        frm.r.value = "create";
        $("#sprivilegesList").val("").trigger("change");
        $("#sprivileges").val("").trigger("change");
        frm.username.readonly=false;
        frm.sendActivationEmail.checked = true;
        $(".newUserCreateBtn span").text("Create");
        $(".newUserDeleteBtn").addClass("d-none");
        $(".newUserTitle").text("New User");
        return true;
    } else {
        frm.r.value = "edit";
        frm.userId.value=u.userId;
        frm.personUid.value=u.personUid;
        frm.comments.value=u.comments ?? "" ;
        frm.username.value=u.username ?? "";
        frm.username.readonly=true;
        frm.sendActivationEmail.checked = false;
        if (isAdmin) {
            var privilegesList = u.privilegesList.split(",");
            var roles = u.roles.split(",");

            $("#sprivilegesList").val(privilegesList).trigger("change");
            $("#rolesList").val(roles).trigger("change");
        }
        $(".newUserCreateBtn span").text("Update");
        $(".newUserDeleteBtn").removeClass("d-none");
        $(".newUserTitle").text("Edit User");
        return true;
    }
}

function editUser(ele) {
    $.post(eGotoAbsLink("Api/Users"), {"r" : "get", "username" : ele.username}, (response)=> {
        try {
            var m = JSON.parse(response);
            if (m["status"] == "OK") {
                var c = m["user"];
                if  (c.username != ele.username) return;
                initUserForm(userForm, c);
                $('#newUserModal').modal({backdrop: 'static', keyboard: false});
                $('#newUserModal').modal('show');
            }
            else {
                MessageBox(m["message"], true);
            }
        }catch(e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function editUser2() {
    $.post(eGotoAbsLink("Api/Users"), {"r" : "get", "username" : "me"}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["user"];
            $("#newUserTitleBar").addClass("d-none");
            $(".newUserDeleteBtn").addClass("d-none").removeClass("newUserDeleteBtn");  //This hides the button and prevents other functions from finding it by removing its class name
            initUserForm(userForm, c);
        }catch(e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function newUser() {
    initUserForm(userForm);
    $('#newUserModal').modal({backdrop: 'static', keyboard: false});
    $('#newUserModal').modal('show');
}

function doDeleteUser() {
    var frm = document.getElementById("newUserForm");

    AdminAuth((auth) => {
        $.post(eGotoAbsLink("Api/Users"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newUserModal').modal('hide');
                    MessageBox(m["message"], false);
                    refreshXDisplay("users");
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
            }
        });
    });
}