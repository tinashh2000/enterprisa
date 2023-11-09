let dataFieldsApiUrl;
let numDataFields = 30;

$(function(){
    dataFieldsApiUrl = eGotoAbsLink("Admin/Api/DataFields");
});

var DataFieldsForm;
//let productListDetailsFormName  = "newDataFieldsForm";
$(function() {
    initValidate('#newDataFieldsForm', submitDataFields);
    $('#dataFieldsTable').on('dbl-click-row.bs.table', function (e, row, element, field) {
        editDataFields(row);
    });
});

function initDataFieldsForm(p=null) {
    var frm = document.getElementById("newDataFieldsDesignerForm");
    frm.reset();

    if (p==null) {
        frm.r.value = "create";
        frm.id.value = 0;
        frm.entityId.value = 0;
        frm.entityName.value = 0;

        currentDataFields = {id : 0, entityId : 0, fields: [], values:[]};
        initDataFields();

        $("#newDataFieldsCreateBtn span").text("Create");
        $(".newDataFieldsDeleteBtn").addClass("d-none");
        $("#newDataFieldsTitle").text("New DataFields");
        mModalTitleX("new", "DataFields", "Create", "Delete", "Close");
    } else {
        frm.r.value = "edit";
        frm.id.value = p.id;
        frm.name.value = p.name;

        var dataFieldsFormat;

        try {
            dataFieldsFormat = JSON.parse(p.dataFieldsFormat);
        } catch(e) {
            dataFieldsFormat = [];
        }

        if (dataFieldsFormat == null) dataFieldsFormat = [];
        currentDataFields = {id : 0, entityId : p.id, fields: dataFieldsFormat, values:[]};
        initDataFields();

        mModalTitleX("edit", "DataFields", null, "Delete", "Close");
        $(".hideDuringCreation").removeClass("d-none");
    }
}

function editDataFields(ele) {
    var id = ele.id;
    $.post(eGotoLink("Api/DataFields"), {"r" : "get", "id" : id}, (response)=> {
        try {
            var m = JSON.parse(response);
            var c = m["dataFields"];
            initDataFieldsForm(c);
            $('#newDataFieldsModal').modal({backdrop: 'static', keyboard: false});
            $('#newDataFieldsModal').modal('show');
        }catch(e) {
            alert(e);
        }
    });
}

function newDataFields() {
    initDataFieldsForm();
    $('#newDataFieldsModal').modal({backdrop: 'static', keyboard: false});
    $('#newDataFieldsModal').modal('show');
}

function doDeleteDataFields() {
    var frm = document.getElementById("newDataFieldsForm");

    AdminAuth((auth) => {
        $.post(eGotoLink("Api/DataFields"), {"r": "delete", "auth": auth, "id": frm.id.value}, (response) => {
            try {
                var m = JSON.parse(response);
                if (m["status"] == "OK") {
                    $('#newDataFieldsModal').modal('hide');
                    MessageBox(m["message"], false);
                    // $("#dataFieldsTable").bootstrapTable("refresh");
                    refreshXDisplay("dataFieldss");
                } else {
                    MessageBox("Item deletion failed", true);
                }
            }catch (e) {
                showStatusDialog(e.stack, response);
            }

        });
    });
}

function submitDataFields(form) {
    var fData = $("#newDataFieldsForm").serializeArray();

    if (curProductList.details.numItems == 0) {
        MessageBox("DataFields should have at least one product", true);
        return false;
    }

    curProductList.details.discount = {"discount" : productListDetailsForm.discount.value, "total" :productListDetailsForm.discountTotal.value };
    curProductList.details.tax = {"tax" : productListDetailsForm.tax.value, "total" : productListDetailsForm.taxTotal.value};
    curProductList.total = productListDetailsForm.total.value;

    let items = {"items" : curProductList.items, "details" : curProductList.details, "subTotal" :  curProductList.subTotal, "total" :  curProductList.total};
    fData.push({name:'description', value: document.getElementById("dataFieldsDescription").value});
    fData.push({name:'specifications', value: document.getElementById("dataFieldspecifications").value});
    fData.push({name:'products', value: JSON.stringify(items)});

    $.post(eGotoLink("Api/DataFields"), fData, (response)=>{
        try {
            var m = JSON.parse(response);
            if (m['status'] == "OK") {
                $('#newDataFieldsModal').modal('hide');
                MessageBox(m["message"], false);
                typeof isWrapped != "undefined" && isWrapped ? window.close() : refreshXDisplay("dataFields"); // $("#dataFieldsTable").bootstrapTable("refresh");
            } else {
                MessageBox(m["message"], true);
            }
        }catch(e) {
            alert(e);
        }
    });
    return false;
}