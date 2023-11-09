var taxForm;

$(function(){
    taxForm = document.getElementById("newTaxForm");

    $('#newTaxForm').validate({
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
            var fData = $("#newTaxForm").serializeArray();
            $.post(eGotoLink("Api/Tax"), fData, (response)=>{
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        $('#newTaxModal').modal('hide');
                        MessageBox(m["message"], false);
                        typeof isWrapped != "undefined" && isWrapped ? window.close() : refreshXDisplay("taxes"); //$("#taxesTable").bootstrapTable("refresh");
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
    //
    // $("#taxesBody").on("dblclick", function(e){
    //     if (e.target.tagName.toUpperCase() == "TD") {
    //         var p = e.target.parentNode;
    //         var id = parseInt(p.getElementsByClassName("id-column")[0].innerText.trim());
    //         editTax(id);
    //     }
    // });

    $('#taxesTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
        editTax(row);
    });
});

function getTaxes(start=0, limit=20) {
    $.post(eGotoLink("Api/Tax"), {"r" : "fetch", "start" : start, "limit" : limit}, onGetTaxes);
}

function onGetTaxes(response) {
    try {
        var m = JSON.parse(response);
        showStatusDialog(response, "Error. Contact your administrator");
    }
    catch(e) {
        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
    }
}

function initForm(id=0, name="", description="", isDefault=false, edit=false) {
    var frm = document.getElementById("newTaxForm");
    frm.r.value = edit ? "edit" : "create";
    frm.id.value=id;
    frm.name.value=name;
    frm.description.value=description;
    document.getElementById("newTaxCreateBtn").getElementsByTagName("span")[0].innerText = edit ? "Update" : "Create";
    document.getElementById("newTaxDeleteBtn").style.display = edit ? "inline" : "none";
    document.getElementById("newTaxTitle").innerText = edit ? "Edit Tax" : "New Tax";
}

function editTax(ele) {
    var id = ele.id;
    $.post("Api/Tax", {"r" : "get", "id" : id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["tax"];

            initForm(id, c.name, c.description, c.flags & 1, true);
            $('#newTaxModal').modal({backdrop: 'static', keyboard: false});
            $('#newTaxModal').modal('show');
        }catch(e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function newTax() {
    initForm();
    $('#newTaxModal').modal({backdrop: 'static', keyboard: false});
    $('#newTaxModal').modal('show');
}

function doDeleteTax() {
    var frm = document.getElementById("newTaxForm");

    AdminAuth((auth) => {
        $.post("Api/Taxes", {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newTaxModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#taxesTable").bootstrapTable("refresh");
                    refreshXDisplay("taxes");
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
            }

        });
    });
}