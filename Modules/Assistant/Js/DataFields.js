function textControlField(field, controlType='text') {
    return '<div class="col-md-'+field.size+' col-12"><label>' + field.name + '</label><div class="form-group input-group"><input type="text" class="form-control" data-fields-form-id="'+field.id+'" placeholder="' + field.name + '" value=' + field.value + '></div></div>';
}

function listControlField(field, controlType='text') {
    let options =  '<div class="col-md-3 col-12"><label>' + field.name + '</label><div class="form-group input-group"><select  data-fields-form-id="'+field.id+'" class="select2 w-100">';
    for(var c=0; c < field.values.length; c++) {
        var item = field.values[c];
        options += "<option " + (item == field.value ? 'selected' : '') + "value='" + item + "'>" + item +"</option>";
    }

    options += '</select></div></div>';
    return options;
}

function checkControlField(field, controlType='text') {
    let options =  '<div class="col-md-3 col-12"><label>' + field.name + '</label><div class="form-group input-group">' +
        '<div class="col-6">\n' +
        '<div class="form-group mb-0">\n' +
        '<div class="custom-control custom-checkbox">\n' +
        '<input type="checkbox" data-fields-form-id="'+field.id+'" checked=' + field.value + ' class="custom-control-input">\n' +
        '<label class="custom-control-label" for="rememberme">'+field.name +'</label>\n' +
        '</div>\n' +
        '</div>\n' +
        '</div></div></div>';
    return options;
}

function renderDataFields(el) {
    el = $(el);
    el.html("");
    for(var c=0;c< currentDataFields.fields.length; c++) {
        let field = currentDataFields.fields[c] ?? null;
        if (!field) continue;

        field.value = (currentDataFields.values[c] ? currentDataFields.values[c] :"");
        field.id = c;
        if (field.type != "undefined") {
            switch(field.type) {
                case 'longstr':
                    field.size = 12;
                    el.append(textControlField(field, currentDataFields.values[c] ?? '',));
                    break;
                case 'str':
                    field.size = 6;
                    el.append(textControlField(field, currentDataFields.values[c] ?? '',));
                    break;
                case 'int':
                    field.size = 3;
                    el.append(textControlField(field, currentDataFields.values[c] ?? '0', 'number'));
                    break;
                case 'dec':
                    field.size = 3;
                    el.append(textControlField(field, currentDataFields.values[c] ?? '0.0','number'));
                    break;
                case 'list':
                case 'marital':
                case 'gender':
                    field.size = 6;
                    el.append(listControlField(field, currentDataFields.values[c]));
                    break;
                case 'check':
                    el.append(checkControlField(field, currentDataFields.values[c] ?? false));
                    break;
            }
        }
    }

    if (el.html() == "") {
        $(".hideOnNoSaleData").addClass("d-none");
    } else {
        $(".hideOnNoSaleData").removeClass("d-none");
    }
}