var currencyForm;

$(function(){
    currencyForm = document.getElementById("newCurrencyForm");

    $('#newCurrencyForm').validate({
        rules: {
            name: {
                required: true,
                minlength: 2
            },
            ratio: {
                required: true,
            }
        },
        messages: {
            name: {
                required: "Please enter a name",
                minlength: "Minimum length is 5 characters"
            },
            ratio: {
                required: "Enter a value for this currency",
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
            form.flags.value = form.setDefault.checked ? "1" : "0";
            var fData = $("#newCurrencyForm").serializeArray();
            $.post(eGotoLink("Api/Currencies"), fData, (response)=>{
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        $('#newCurrencyModal').modal('hide');
                        MessageBox(m["message"], false);
                        // $("#currenciesTable").bootstrapTable("refresh");
                        refreshXDisplay("currencies");
                    } else {
                        MessageBox(m["message"], true);
                    }
                }catch(e) {
                    showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
                }
            });
            return false;
        }
    });

    $("#currenciesBody").on("dblclick", function(e){
        if (e.target.tagName.toUpperCase() == "TD") {
            var p = e.target.parentNode;
            var id = parseInt(p.getElementsByClassName("id-column")[0].innerText.trim());
            editCurrency(id);
        }
    });
});

function getCurrencies(start=0, limit=20) {
    $.post(eGotoLink("Api/Account"), {"r" : "fetch", "start" : start, "limit" : limit}, onGetCurrencies);
}

function onGetCurrencies(response) {
    try {
        var m = JSON.parse(response);
        showStatusDialog(response, "Error. Contact your administrator");
    }
    catch(e) {
        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
    }
}

function initForm(id=0, name="", ratio="1.0", description="", isDefault=false, edit=false) {
    var frm = document.getElementById("newCurrencyForm");
    frm.r.value = edit ? "edit" : "create";
    frm.id.value=id;
    frm.name.value=name;
    frm.ratio.value=ratio;
    frm.description.value=description;
    frm.setDefault.checked = isDefault;
    document.getElementById("newCurrencyCreateBtn").getElementsByTagName("span")[0].innerText = edit ? "Update" : "Create";
    document.getElementById("newCurrencyDeleteBtn").style.display = edit ? "inline" : "none";
    document.getElementById("newCurrencyTitle").innerText = edit ? "Edit Currency" : "New Currency";
}

function editCurrency(id) {
    $.post(eGotoLink("Api/Currencies"), {"r" : "get", "id" : id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["currency"];

            initForm(id, c.name, c.ratio, c.description, c.flags & 1, true);
            $('#newCurrencyModal').modal({backdrop: 'static', keyboard: false});
            $('#newCurrencyModal').modal('show');
        }catch(e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function newCurrency() {
    initForm();
    $('#newCurrencyModal').modal({backdrop: 'static', keyboard: false});
    $('#newCurrencyModal').modal('show');
}

function doDeleteCurrency() {
    var frm = document.getElementById("newCurrencyForm");

    AdminAuth((auth) => {
        $.post(eGotoLink("Api/Currencies"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newCurrencyModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#currenciesTable").bootstrapTable("refresh");
                    refreshXDisplay("currencies");
                } else {

                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
            }

        });
    });
}