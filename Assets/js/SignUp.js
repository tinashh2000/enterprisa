function doSignIn(form, onResponse) {
    var fData = $("#loginForm").serializeArray();
    jQuery.post('Api/Authentication', fData, onResponse);
}

function doSignUp(form, onResponse) {
    jQuery.post('Api/Authentication', {"r":"signUp"}, onResponse);
}

var frm = document.getElementById("loginForm");

function onLogin(response) {
    try {
        var m = JSON.parse(response)
        // alert(response);
        if (m["status"] == "OK") {
            window.location.href = retAddr;
            MessageBox(m["message"], false);
        } else {
            MessageBox(m["message"], true);

            frm.email.value = "";
            frm.password.value = "";
        }
    }catch (e) {
        showStatusDialog(e + "" + response, "Login error. Consult your system administrator");
    }
}

$(document).ready(function () {
    // $.validator.setDefaults({
    //     submitHandler: function () {
    //     }
    // });

    $("#loginBtn").on('click', function () {
        submitForm("#loginForm");
    });

    $('#loginForm').validate({
        rules: {
            username: {
                required: true,
            },
            email: {
                required: true,
            },
            password: {
                required: true,
                minlength: 5
            }
        },
        messages: {
            username: {
                required: "Please enter a email address",
                email: "Please enter a valid email address"
            },
            password: {
                required: "Please provide a password",
                minlength: "Your password must be at least 5 characters long"
            },
            terms: "Please accept our terms"
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
            doSignIn(frm, onLogin);
            return false;
        }
    });


    initValidate("#signUpForm", function(form){
        try {
            var frmData = new FormData(form);

            AdminAuth((auth) => {
                frmData.append("auth", auth);
                frmData.append("r", "signUp");
                jQuery.post({
                    url: eGotoAbsLink("Api/Authentication"),
                    type: "POST",
                    data: frmData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        try {
                            var m = JSON.parse(response);
                            if (m['status'] == "OK") {
                                MessageBox(m["message"], false);
                                window.location.href="Login";
                            } else {
                                MessageBox(m["message"], true);
                            }
                        } catch (e) {
                            showStatusDialog(response, "Error. Contact your administrator");
                        }
                    }
                });
            });
        } catch(e) {
            alert(e);
        }

    },{},{});

    $('#userDob').daterangepicker({singleDatePicker:true, locale: {format: 'YYYY/MM/DD'}});

});

var returnAddr;
