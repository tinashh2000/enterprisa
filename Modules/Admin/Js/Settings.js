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

    initRoleSelect2("#newUserFrm select[name='rolesList']");

    $("#mCountryPicker").select2({
        minimumResultsForSearch: Infinity,
        width: '100%',
        data: countriesS2
    });

    $("#defaultLogoFile").change(function () { this.pId = "defaultLogoImg"; readPictureURL(this); });
    $("#headerLogoFile").change(function () { this.pId = "headerLogoImg"; readPictureURL(this); });

    $("form a.updateBtn").on("click", function (e){
        submitForm($(e.currentTarget).closest("form").get(0));
    });

    $('#settingsForms form').each(function(){
        $(this).validate({
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

                var frmData = new FormData(form);
                frmData.append("r", "setSetting");
                AdminAuth((auth) => {
                    frmData.append("auth", auth);
                    jQuery.post({
                        url: eGotoAbsLink("Api/Settings"),
                        type: "POST",
                        data: frmData,
                        processData: false,
                        contentType: false,
                        success: (response) => {
                            try {
                                var m = JSON.parse(response);
                                if (m['status'] == "OK") {
                                    $('#settingsBusyModal').modal('hide');
                                    MessageBox(m["message"], false);
                                    // $("#companiesTable").bootstrapTable("refresh");
                                    // refreshXDisplay("companies");
                                } else {
                                    MessageBox(m["message"], true);
                                }
                            } catch (e) {
                                alert(e + "..... " + response);
                            }
                        }
                    });
                });
            }
        });
    });

});
