var entityForm;

$(function(){
    entityForm = document.getElementById("newEntityForm");

    initEntitySelect2("#mEntitiesPicker");

    $('#newEntityForm').validate({
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
            var fData = $("#newEntityForm").serializeArray();
            $.post(eGotoLink("Api/Entity"), fData, (response)=>{
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        $('#newEntityModal').modal('hide');
                        MessageBox(m["message"], false);
                        typeof isWrapped != "undefined" && isWrapped ? window.close() : refreshXDisplay("entities"); // $("#entitiesTable").bootstrapTable("refresh");
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
    // $("#entitiesBody").on("dblclick", function(e){
    //     if (e.target.tagName.toUpperCase() == "TD") {
    //         var p = e.target.parentNode;
    //         var id = parseInt(p.getElementsByClassName("id-column")[0].innerText.trim());
    //         editEntity(id);
    //     }
    // });

    $('#entitiesTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
        editEntity(row);
    });
});

function getEntities(start=0, limit=20) {
    $.post(eGotoLink("Api/Entity"), {"r" : "fetch", "start" : start, "limit" : limit}, onGetEntities);
}

function onGetEntities(response) {
    try {
        var m = JSON.parse(response);
        showStatusDialog(response, "Error. Contact your administrator");
    }
    catch(e) {
        showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
    }
}

function initEntityForm(e=null) {
    var frm = document.getElementById("newEntityForm");
    if (e == null) {
        frm.reset();
        $("#newEntityFrm .newEntityCreateBtn span").text("Create");
        $("#newEntityFrm .newEntityDeleteBtn").addClass("d-none");
        $("#newEntityFrm  .newEntityTitle").text("Create Entity");
    } else {
        frm.r.value = "edit";
        frm.id.value = e.id;
        frm.name.value = e.name;
        $(frm.module).val(e.module).trigger("change");
        frm.classification.value=e.classification;
        frm.description.value = e.description;
        $("#newEntityFrm .newEntityCreateBtn span").text("Update");
        $("#newEntityFrm .newEntityDeleteBtn").removeClass("d-none");
        $("#newEntityFrm  .newEntityTitle").text("Edit Entity");
    }
}

function editEntity(ele) {
    var id = ele.id;
    $.post(eGotoLink("Api/Entity"), {"r" : "get", "id" : id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var e = m["entity"];

            initEntityForm(e);
            $('#newEntityModal').modal({backdrop: 'static', keyboard: false});
            $('#newEntityModal').modal('show');
        }catch(e) {
            showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
        }
    });
}

function newEntity() {
    initEntityForm();
    $('#newEntityModal').modal({backdrop: 'static', keyboard: false});
    $('#newEntityModal').modal('show');
}

function doDeleteEntity() {
    var frm = document.getElementById("newEntityForm");

    AdminAuth((auth) => {
        $.post(eGotoLink("Api/Entities"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newEntityModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#entitiesTable").bootstrapTable("refresh");
                    refreshXDisplay("entities");
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack + " ::: " + response, "Error. Contact your administrator");
            }

        });
    });
}