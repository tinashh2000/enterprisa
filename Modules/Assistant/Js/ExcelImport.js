var excelImportForm;

$(async function () {
    excelImportForm = document.getElementById("newExcelImportForm");
    initValidate("#newExcelImportForm", submitExcelImportForm);
    $('#uploadExcelFile').on('click', function() {
        excelImportForm.approved.value="0";
        submitForm(excelImportForm);
    });

    $("#approveExcelFile").on('click', function() {
        excelImportForm.approved.value="1";
        submitForm(excelImportForm);
    });

    $("#viewImportGuidelines").on("click", function() {
        startWrapper('ShowImportGuidelines');
    });

    excelImportForm.approved.value=0;
    excelImportForm.reset();
});

function submitExcelImportForm(form) {
    var frmData = new FormData(form);
    modalBusy('#newExcelImportModal', true);
    AdminAuth((auth) => {
        frmData.append("auth", auth);
        jQuery.post({
            url: apiExcelImportUrl, type: "POST", data: frmData, processData: false, contentType: false,
            success: (response) => {
               modalBusy("#newExcelImportModal", false);
                try {
                    var m = JSON.parse(response);
                    if (m['status'] == "OK") {
                        MessageBox(m["message"], false);
                        if (excelImportForm.approved.value!="1") {
                            let records = m['records'];
                            let title = m['title'];
                            let tlength = title.length;
                            let defaultRecords = m['defaultRows'];
                            let importTable = document.getElementById("excelImportBody");
                            let importTableHeading = document.getElementById("excelImportHeading");

                            let tr = "";
                            for (var cc = 0; cc < tlength; cc++) {
                                tr += "<th>" + title[cc] + "<br><span class='text-primary'>" + (defaultRecords[cc] ?? "") + "</span></th>";
                            }
                            importTableHeading.innerHTML = tr;
                            importTable.innerHTML = "";

                            for (const record in records) {
                                rec = {}; //Reset record
                                tr = "<tr>";
                                for (var cc = 0; cc < tlength; cc++) {
                                    rec = records[record][cc] ?? null;
                                    tr += "<td>" + rec + "</td>";
                                }
                                tr += "</tr>";
                                importTable.innerHTML += tr;
                            }

                            $("#approveExcelFile").removeClass("d-none");
                        } else {
                            $("#approveExcelFile").addClass("d-none");
                        }
                        excelImportForm.approved.value!="0";
                    } else {
                        MessageBox(m["message"], true);
                        $('#newExcelImportModal').modal('hide');
                        typeof isWrapped != "undefined" && isWrapped ? window.close() : refreshXDisplay("excelImport");
                    }
                } catch (e) {
                    showStatusDialog(e.stack + " ::: " + e.message + "::" + response, "Error. Contact your administrator");
                }
            }
        });
    });
    return false;
}

function initImportForm(c = null) {
    var frm = document.getElementById("newExcelImportForm");
    frm.reset();
    if (c == null) {
        frm.r.value = "create";
        frm.id.value = '0';
        mModalTitleX("new", "ExcelImport", "Create", "Delete", "Close");
    }
}

function excelImport() {
    initImportForm();
}
