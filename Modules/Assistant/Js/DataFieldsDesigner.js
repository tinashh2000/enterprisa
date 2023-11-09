let dataFieldsForm = document.getElementById("newDataFieldsDesignerForm");

$(function() {
    $(".saveFieldChanges").on("click", saveFieldChanges);
    $(".addNewPossibleValue").on("click", addFieldValue);
    $(".removePossibleValue").on("click", removeFieldValue);
    $(".addNewField").on("click", addNewField);

    $(dataFieldsForm.type).on('select2:select', fieldTypeChanged);
    $(dataFieldsForm.fieldNames).on('change', fieldNameChanged)
    fieldTypeChanged();
    fieldNameChanged();
});

function addNewField() {
    if (currentDataFields.fields.length > numDataFields) {
        alert("You have reached your data fields limit. Consult your system administrator for assistance");
        return;
    }
    selectedDataField = currentDataFields.fields.push({"name" : dataFieldsForm.name.value, "type" : $(dataFieldsForm.type).val()}) - 1;
    dataFieldsForm.fieldNames.innerHTML += "<option value='" + (selectedDataField + 1) + "'>" + dataFieldsForm.name.value + "</option>";
    saveCurrentDataField();
}

function addFieldValue() {
    var option = $("<option>").attr("value", dataFieldsForm.addValue.value).text(dataFieldsForm.addValue.value).appendTo(dataFieldsForm.valuesList);
}

function removeFieldValue() {
    if (dataFieldsForm.valuesList.selectedIndex == -1) return;
    var option = dataFieldsForm.valuesList.options[dataFieldsForm.valuesList.selectedIndex];
    dataFieldsForm.valuesList.removeChild(option);
}

function saveCurrentDataField() {
    dataFieldsForm.fieldNames.options[selectedDataField].innerText = dataFieldsForm.name.value;
    currentDataFields.fields[selectedDataField] = {"name" : dataFieldsForm.name.value, "type" : $(dataFieldsForm.type).val()};
    if (dataFieldsForm.type.value == "list" || dataFieldsForm.type.value == "gender" || dataFieldsForm.type.value == "marital") {

        let values = [];
        let nOptions = dataFieldsForm.valuesList.options.length
        for(var c=0;c<nOptions;c++) {
            let ele = dataFieldsForm.valuesList.options[c];
            values[c] = encodeURI(ele.innerText);
        }
        currentDataFields.fields[selectedDataField].values = values;
    }
}

function renderCurrentDataField() {
    $(dataFieldsForm.valuesList).html("");
    if (typeof currentDataFields.fields[selectedDataField] == "undefined") {
        currentDataFields.fields[selectedDataField] = {name : "Field " + (selectedDataField+1), type : "str"};
    }
    dataFieldsForm.name.value = currentDataFields.fields[selectedDataField]?.name ??  "Field " + (selectedDataField+1);
    $(dataFieldsForm.type).val(currentDataFields.fields[selectedDataField]?.type ?? "str").trigger("change");;
    if (currentDataFields.fields[selectedDataField].type == "list" || currentDataFields.fields[selectedDataField].type == "gender" || currentDataFields.fields[selectedDataField].type == "marital") {
        let options = "";
        let nOptions =  currentDataFields.fields[selectedDataField]?.values.length;
        for (var c=0;c< nOptions;c++) {
            let item = currentDataFields.fields[selectedDataField]?.values[c];
            options += "<option value='"+item+"'>"+item+"</option>";
        }
        $(dataFieldsForm.valuesList).html(options);
    }
}

function initDataFields() {
    $(dataFieldsForm.fieldNames).html("");
    let options = "";
    let nOptions =  currentDataFields.fields?.length;
    for (var c=0;c< nOptions;c++) {
        let item = currentDataFields.fields[c];
        options += "<option value='" + (c + 1) + "'>" + item.name + "</option>";
    }
    $(dataFieldsForm.fieldNames).html(options);

    if (dataFieldsForm.fieldNames.options.length > 0)
        dataFieldsForm.fieldNames.selectedIndex = 0;

    selectedDataField = -1;
    fieldNameChanged();
}

function fieldNameChanged() {
    if (dataFieldsForm.fieldNames.selectedIndex == -1) return;
    let index = parseInt(dataFieldsForm.fieldNames.value);
    if (index > 0 && index <= 20) {
        if (selectedDataField != -1) {
            saveCurrentDataField();
        }
        selectedDataField = index - 1;
        renderCurrentDataField();
    }
}

function fieldTypeChanged() {
    $(dataFieldsForm.valuesList).html("");
    switch (dataFieldsForm.type.value) {
        case "gender":
            $("<option value='1'>Male</option><option value='2'>Female</option><option value='2'>Other</option>").appendTo(dataFieldsForm.valuesList);
            $(dataFieldsForm.valuesList).attr("disabled", false);
            break;
        case "list":
            $(dataFieldsForm.valuesList).attr("disabled", false);
            break;
        case "marital":
            $(dataFieldsForm.valuesList).attr("disabled", false);
            $("<option value='1'>Single</option><option value='2'>Married</option><option value='3'>Divorced</option><option value='4'>Widow(er)</option><option value='5'>Other</option>").appendTo(dataFieldsForm.valuesList);
            break;
        default:
            $(dataFieldsForm.valuesList).html("").attr("disabled", true);
            break;
    }
}

function saveFieldChanges() {
    $.post(dataFieldsApiUrl, {"r" : "update", "data" : JSON.stringify(currentDataFields)}, (response)=> {
        try {
            var m = JSON.parse(response);
            if (m["status"] == "OK") {
                MessageBox(m["message"], false);
            } else {
                MessageBox("An error occured", true);
            }
        }catch(e) {
            alert(e);
        }
    });
}