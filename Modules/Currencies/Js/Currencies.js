var currencyForm;

$(function(){
    currencyForm = document.getElementById("newCurrencyForm");
    initXSelect2({el: ".companyPicker", name: "Company", names: "Companies", topItems: '[{"id":"*", "text":"All"}]'});
//    initXSelect2({el: ".accountPicker", name: "Account", names: "Accounts", topItems: '[{"id":"0", "text":"None"}]'});

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
            var fData = $("#newCurrencyForm").serializeArray();
            $.post(eGotoLink("Currencies/Api/Currencies"), fData, (response)=>{
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        $('#newCurrencyModal').modal('hide');
                        MessageBox(m["message"], false);
                        typeof isWrapped != "undefined" && isWrapped ? window.close() : refreshXDisplay("currencies");// $("#currenciesTable").bootstrapTable("refresh");
                    } else {
                        MessageBox(m["message"], true);
                    }
                }catch(e) {
                    showStatusDialog(e.stack, response);
                }
            });
            return false;
        }
    });


    $('#currenciesTable').on('dbl-click-row.bs.table', function (e, row, element, field) {

            editCurrency(row.id);

    });

    // $("#currenciesBody").on("dblclick", function(e){
    //     if (e.target.tagName.toUpperCase() == "TD") {
    //         var p = e.target.parentNode;
    //         var id = parseInt(p.getElementsByClassName("id-column")[0].innerText.trim());
    //         editCurrency(id);
    //     }
    // });
});

function onGetCurrencies(response) {
    try {
        var m = JSON.parse(response);
        showStatusDialog(response, "Error. Contact your administrator");
    }
    catch(e) {
        showStatusDialog(e.stack, response);
    }
}

function initForm(c=null) {

    var frm = document.getElementById("newCurrencyForm");
    frm.reset();
    if (c == null) {
        frm.r.value = "create";
        frm.id.value = 0;
        frm.name.value = "";
        frm.ratio.value = "1.00";
        frm.description.value = "";
        frm.setDefault.checked = false;
        document.getElementById("newCurrencyCreateBtn").getElementsByTagName("span")[0].innerText = "Create";
        $("#newCurrencyDeleteBtn").addClass("d-none");
//        mModalTitleNew("Currency");
    } else {
        frm.r.value = "edit";
        frm.id.value = c.id;
        frm.name.value = c.name;
        frm.ratio.value = c.ratio;
        frm.description.value = c.description;
        frm.setDefault.checked = c.flags & 1;
        document.getElementById("newCurrencyCreateBtn").getElementsByTagName("span")[0].innerText = "Update";
        $(".newCurrencyDeleteBtn").removeClass("d-none");
//        mModalTitleEdit("Currency");
    }
}

function editCurrency(id) {
    $.post(eGotoLink("Currencies/Api/Currencies"), {"r" : "get", "id" : id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["currency"];

            initForm(c);
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
    if (!confirm("Are you sure you want to delete this currency?")) return;
    var frm = document.getElementById("newCurrencyForm");

    AdminAuth((auth) => {
        $.post(eGotoLink("Currencies/Api/Currencies"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
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
                showStatusDialog(e.stack, response);
            }

        });
    });
}