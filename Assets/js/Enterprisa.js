$(function () {

    if (typeof $.fn.select2 !== 'undefined') {
        $(".select2").each(function( index ) {
            var s2Params = {
                // theme: 'bootstrap-5',
                width: '100%',
                minimumResultsForSearch: 10,
            };
            var thisCtrl = $(this);
            var pr = thisCtrl.parents(".modal");
            if (pr.length > 0) {
                s2Params.dropdownParent = $("#" + pr.attr("id"));
            }
            thisCtrl.select2(s2Params);
        });
    }

    if (typeof $.fn.overlayScrollbars !== 'undefined') {

        $(".overlayScrollContainer, nav .sidebar-container, .mt-main-contents").overlayScrollbars({
            className: "os-theme-dark",
            sizeAutoCapable: true,
            scrollbars: {
                autoHide: "scroll",
                clickScrolling: true
            }
        });
    }

    if (typeof $.fn.validate !== 'undefined') {

        $.validator.addMethod("dateFormat",
            function (value, element) {
                return value.match(/^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$/);
            },
            "Please enter a date in the format yyyy-mm-dd.");
    }

    if (typeof $.fn.summernote !== 'undefined') {
        $(".rich-text").summernote({width: '100%', height: 200});
    }

    if (typeof $.fn.sortable !== 'undefined') {
        $(".draggableCards").sortable({revert: true, animation: 150});
    }

    $(".mtDraggable").sortable({revert:true,animation:150});

    $('[data-resize="modal"]').on("click", (e) => {
        var p = $(e.currentTarget.parentNode).closest(".modal-dialog");
        p.toggleClass("modal-dialog-full-width");
    });

    if (typeof $.fn.magicDiv !== 'undefined') {
        $(".magicDiv").magicDiv();
    }

    var theme = $(document.body).attr("theme");

    (theme == "light" || theme == "dark" || $(document.body).attr("theme", "light"));

    $('.displayChatbox').on('click', function () {
        onShowMessages();
        $('.showChat').toggle('slide', options, 500);
    });

    $('.mt-sidebar-control').on('click', function () {
        $(".sidebar, .mt-main-contents").toggleClass("navbar-show");
    });

    $('.mt-sidebar-backdrop').on('click', function () {
        $(".sidebar").removeClass("navbar-show");
    });

    $('.displayNotificationBox[data-bs-toggle="dropdown"]').on('click', function () {
        var now = moment().format("YYYY-MM-DD HH:mm:ss");
        var next = moment(lastNotificationsCheck, "YYYY-MM-DD HH:mm:ss").add(30, "seconds").format("YYYY-MM-DD HH:mm:ss");
        if (lastNotificationsCheck == null ||  now >= next) {
            onShowNotifications();
        }
    });

    $('.displayMessagesSideBar[data-bs-toggle="collapse"]').on('click', function () {
        var now = moment().format("YYYY-MM-DD HH:mm:ss");
        var next = moment(lastMessagesCheck, "YYYY-MM-DD HH:mm:ss").add(30, "seconds").format("YYYY-MM-DD HH:mm:ss");
        if (lastMessagesCheck == null ||  now >= next) {
            onShowMessages();
        }
    });

    $('#closeMessagesSideBar').on('click', function () {
        $("#messagesSideBar").removeClass("fullscreen");
        $("#messagesIcon").removeClass("collapsed").attr("aria-expanded", "false");
    });

    $('[data-bs-toggle="fullscreen"]').on('click', function (e) {
        try {
            let ele = $($(e.currentTarget).attr("data-bs-target"));
            ele.toggleClass("fullscreen")
        } catch(e) {

        }
    });

    $(".MSpecial-Modal .cancelBtn").removeClass("d-none");

    // $(".sidebar").collapse();


    $('[data-company]').on("click", function(e){
        setCurrentCompany($(e.target).attr("data-company"));
    });
    updateCompanies(-1);

    $.fn.modal.Constructor.prototype.enforceFocus = function() {};


    $(function () {

        ConvertUTCTime(".mt-utc-time, .mt-utc-dynamic-time");

        $(".loader-bg").fadeOut();
        $("body").removeClass("loading");
        $( ()=> {
            if (window.parent) {
                let iFrame = window.parent.document.getElementById("sendEmailIframe");
                if (iFrame && typeof iFrame.loadingCompleted == "function") {
                    iFrame.loadingCompleted();
                }
            }
        });
    });

});

function ConvertUTCTime(ele) {
    $(ele).each(function(){
        $(this).html(formatDate(moment.utc($(this).attr("data-utc-time")).local().format("YYYY-MM-DD HH:mm:ss")));
    });
}

function noEscape(markup) {
    return markup;
}

function startWrapper(link, onclose = null) {
    var wn = window.open(homeLink + "/Wrapper?wrapper=" + link, '1590346735602', 'width=700,height=500,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');
    wn.addEventListener("beforeunload", function (event) {
        if (typeof onclose == "function") onclose();
    });
    return false;
}

function readPictureURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#' + input.pId).attr('src', e.target.result).fadeIn('slow');
        }
        reader.readAsDataURL(input.files[0]);
    }
}


var XChanges = {
    _fields: {},
    change: function (name, value) {
        this._fields[name] = value;
    },

    get: function () {
        return this._fields;
    },

    reset: function () {
        this._fields = {};
    },

    exists: function (field) {
        return typeof this._fields[field] != "undefined";
    }
}

function formatDateSpan(value) {
    var dt = moment(value, "YYYY-MM-DD HH:mm:ss"); //.add(1, "day");
    if (dt.format("YYYY-MM-DD") == moment().format("YYYY-MM-DD")) { //If same date just return the time
        return "<span><a class='mt-date-times'>" + dt.format(" HH:mm") + "</a></span>" ;
    } else if (dt.format("YYYY-MM") == moment().format("YYYY-MM")) { //If same date just return the time
        return "<span><a class='mt-date-dates'>" + dt.format("DD MMM") + "</a> <a class='mt-date-times'>" + dt.format(" HH:mm") + "</a></span>" ;
    } else if (dt.format("YYYY") == moment().format("YYYY")) { //If same year
        return "<span><a class='mt-date-dates'>" + dt.format("DD MMM") + "</a> <a class='mt-date-times'>" + dt.format(" HH:mm") + "</a></span>" ;
    }

    return "<span><a class='mt-date-dates'>" + dt.format("DD MMM YYYY") + "</a> <a class='mt-date-times'>" + dt.format(" HH:mm") + "</a></span>" ;
    return "<a class='text-primary'>" + dt.format("HH:mm") + "</a><br>" + dt.format("DD MMM YYYY");
}

function formatDate(value) {
    var dt = moment(value, "YYYY-MM-DD HH:mm:ss"); //.add(1, "day");
    if (dt.format("YYYY-MM-DD") == moment().format("YYYY-MM-DD")) { //If same date just return the time
        return "<div class='mt-date-container'><a class='mt-date-timec'>" + dt.format(" HH:mm") + "</a></div>" ;
    } else if (dt.format("YYYY-MM") == moment().format("YYYY-MM")) { //If same date just return the time
        return "<div class='mt-date-container'><a class='mt-date-datec'>" + dt.format("DD MMM") + "</a> &nbsp; <a class='mt-date-timec'>" + dt.format(" HH:mm") + "</a></div>" ;
    } else if (dt.format("YYYY") == moment().format("YYYY")) { //If same year
        // return "<a class='text-primary'>" + dt.format("HH:mm")+"</a><br>" + dt.format("D MMM");
        return "<div class='mt-date-container'><a class='mt-date-datec'>" + dt.format("DD MMM") + "</a> &nbsp; <a class='mt-date-timec'>" + dt.format(" HH:mm") + "</a></div>" ;
    }

    return "<div class='mt-date-container'><span class='mt-date-datec'>" + dt.format("DD MMM YYYY") + "</span><a class='mt-date-timec'>" + dt.format(" HH:mm") + "</a></div>" ;
    return "<a class='text-primary'>" + dt.format("HH:mm") + "</a><br>" + dt.format("DD MMM YYYY");
}

function dateFormatter(value, row, index) {
    var dt = moment.utc(value).local().format("YYYY-MM-DD HH:mm:ss"); //.add(1, "day");
    return formatDate(dt);
}


function amountFormatter(value, row, index) {
    var v;
    try {
        v=new Big(value).abs();
    } catch (e) {
        v="";
    }
    return "<a class=" + (value >= 0 ? 'text-positive' : 'text-negative') + ">" + v + "</a>";
}

function emailFormatter(value, row, index) {
    return "<a class='email'>" + value + "</a>";
}

function phoneFormatter(value, row, index) {
    return "<a class='phone-column'>" + value + "</a>";
}

function numberFormatter(value, row, index) {
    return "<a class=" + (value >= 0 ? 'text-positive' : 'text-negative') + ">" + value + "</a>";
}

function usernameFormatter(value, row, index) {
    if (typeof row.uid != "undefined")
        return "<span class='img-username'><img class='img-thumbnail' src='" + homeLink + "/people/" + row.uid + "/pics/profile' />" + value + "</span>";


    return "<span class='img-username'><img class='img-thumbnail' src='" + homeLink + "/people/" + value + "/pics/profile' />" + value + "</span>";

}

function userNameFormatter(value, row, index) {
    return "<span class='img-username'> <img class='img-thumbnail' src='" + homeLink + "/people/" + row.uid + "/pics/profile' />" + row.name + "</span>";
}

function personFormatter(value, row, index) {
    return "<span class='img-username'> <img class='img-thumbnail' src='" + homeLink + "/people/" + row.uid + "/pics/profile' />" + row.name + "</span>";
}

function eGotoLink(lnk, mDir = null) {
    return (mDir == null ? bDir : (homeLink + "/" + mDir + "/")) + lnk;
}

function eGotoHelper(lnk) {
    return homeLink + "/Helpers/" + lnk;
}

function eGotoAbsLink(lnk) {
    return homeLink +"/"+ lnk;
}

async function buildSelect(params, data) {
    try {
        if (typeof params.link == "string" && params.link != "" && !params.ajax) {
            await $.post(params.link, {}, (response) => {data = response});

            try {
                var m = JSON.parse(data);
                if (typeof params.topItems == "string") {
                    m["results"] = JSON.parse(params.topItems).concat(m["results"]);
                }
                if (typeof params.bottomItems == "string")
                    m["results"] = m["results"].concat(JSON.parse(params.bottomItems));

                const defaultItem = typeof m['default'] != "undefined" ? m["default"] : (typeof params.selected != "undefined" ? params.selected : null);

                data = m['results'];
                params.defaultItem = defaultItem;
            } catch(e) {
                data = [];
            }

        }

        $(params.ctrl).each(function( index ) {
            var thisCtrl = $(this);

            var s2Params = {
                // theme: 'bootstrap-5',
                width: params.width || '100%',
                minimumResultsForSearch: 10,
            };

            if (data) {
                $(this).empty().trigger("change");
                s2Params.data = data;
            }

            if (typeof params.link == "string" && params.link != "" && params.ajax) {
                s2Params.ajax = {
                    url: params.link,
                    dataType: 'json',
                    cache: true,
                    data: function (params) { // page is the one-based page number tracked by Select2
                        return {
                            q: params.term || "",
                            type: 'select',
                            page: params.page || 0
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 0;
                        return {
                            results: data.results,
                            pagination: {
                                more: ( (params.page+1) * 10) < data.count
                            }
                        };
                    },
                };
            }

            var pr = thisCtrl.parents(".modal");
            if (pr.length > 0) {
                s2Params.dropdownParent = $("#" + pr.attr("id"));
            }

            var s2 = thisCtrl.select2(s2Params);
            if (typeof params.onOpen != "undefined") {
                var nElem = document.createElement("div");
                nElem.className = "mt-2 p-1";
                nElem.innerHTML = "<a href='#'> &nbsp;" + params.onOpen.text + "</a>" + (params.onOpen.showRefresh ? "<span class='float-right'><a href='#'><i class='fas fa-sync'></i> Refresh  &nbsp;</a> </span>" : "");
                var elems = nElem.getElementsByTagName("a");
                if (params.onOpen.link != "") {
                    elems[0].onclick = function () { //If the first item is clicked
                        startWrapper(params.onOpen.link, () => {
                            if (params.onOpen.showRefresh) {
                                initSelect2(params);
                            }
                        });
                    };
                }

                if (elems.length > 1) { //If
                    elems[1].onclick = function () {
                        initSelect2(params);
                    }
                }

                s2.on('select2:open', () => {
                    $(".select2-results:not(:has(a))").append(nElem);
                });
            }

            if (params.defaultItem != null) {
                s2.val("").trigger("change").val(params.defaultItem).trigger("change");

            }
        });
        return true;

    } catch (e) {
        alert(e + "::::");
    }
}

async function initSelect2(params) {
    if (typeof params.format == "undefined") params.format = "json";
    data = params.data ?? null;
    return await buildSelect(params, data);
}

async function initXSelect2(item) {
    var params = {
        ctrl: item.el,
        width: '100%',
        link: item.url  ? item.url : (item.names ? eGotoHelper("Fetch" + item.names + "?type=select" + (typeof item.params == "string" ? "&" + item.params : "")): null),
        onOpen: {
            text: "<i class='fas fa-plus'></i> New " + (typeof item.text != "undefined" ? item.text : item.name),
            link: "new" + item.name,
            refreshOnClose: true,
            showRefresh: true
        },
        tags: item.tags ?? null,
        topItems: item.topItems ?? null,
        bottomItems: item.bottomItems ?? null,
        selected: item.selected ?? null
    };
    return await initSelect2(params);
}

function initCurrencySelect2(el) {
    initXSelect2({el: el, name: "Currency", names: "Currencies"});
}

function initEntitySelect2(el, entity = "", classification = "") {
    initXSelect2({
        el: el,
        name: "Entity",
        names: "Entities",
        params: "entity=" + entity + "&classification=" + classification
    });
}

function initEItemSelect2(el, entity = "", classification = "") {
    initXSelect2({
        el: el,
        name: "EntityItem",
        names: "Entities",
        params: "entity=" + entity + "&classification=" + classification
    });
}

function initAccountSelect2(el) {
    initXSelect2({el: el, name: "Account", names: "Accounts"});
}

function initRoleSelect2(el) {
    initXSelect2({el: el, name: "Role", names: "Roles"});
}

function savePDF(element, filename, options = {}) {
    var doc = new jsPDF(options);

// We'll make our own renderer to skip this editor
    var specialElementHandlers = {
        '#savePDF': function (element, renderer) {
            return true;
        },
        '.controls': function (element, renderer) {
            return true;
        }
    };

// All units are in the set measurement for the document
// This can be changed to "pt" (points), "mm" (Default), "cm", "in"
    doc.fromHTML(element.get(0), 15, 15, {
        'width': 270,
        'elementHandlers': specialElementHandlers
    });

    doc.save(filename);
}

function verifyForm(formId) {
    var f = $(formId).validate().checkForm();
    if (!f) {
        $(formId)
            .validate()
            .showErrors();
        return false;
    }
    else {
        return true;
    }
}

function submitForm(formId) {
    var f = $(formId).validate().checkForm();
    if (!f)
        $(formId)
            .validate()
            .showErrors();
    else {
        $(formId)
            .validate()
            .settings.submitHandler($(formId).get(0));
    }
}

String.prototype.supplant = function (o) {
    return this.replace(/{([^{}]*)}/g,
        function (a, b) {
            if (b.substr(0,2) == "=>") {
                console.log(b);
                const fun = b.substr(2);
                const val = eval("if (typeof " + fun +" == 'function') {" + fun + "(o);}");
                return (typeof val == "undefined") ? "(NaF)" : val;
            }
            var r = o[b];
            return typeof r === 'string' || typeof r === 'number' ? r : a;
        }
    );
};

$.fn.serializeJSON = function(id) {
    var data={};
    $(this).serializeArray().map(function(x){data[x.name] = x.value;});
    return JSON.stringify(data);
}

$.fn.serializeAssoc = function(id) {
    var data={};
    $(this).serializeArray().map(function(x){data[x.name] = x.value;});
    return data;
}

function defaultQueryParams(params) {
    return params
}

function defaultResponseHandler(res) {
    return res
}

function playSound(filename) {
    let audio = new Audio(filename);
    audio.play();
}

function formBusy(form, busy = true) {

    if ($(form).prev('.m-content-status').length == 0) {
        $(form).before('<div class="m-content-status"><div class=" d-flex justify-content-center align-items-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div></div>');
    }

    if (busy) {
        $(form).prev(".m-content-status").addClass("m-content-busy");
        $(form).addClass("d-none");
    } else {
        $(form).prev(".m-content-status").removeClass("m-content-busy");
        $(form).removeClass("d-none");
    }

}

function modalBusy(element, busy) {

   (busy && $(element).addClass("modal-busy")) || $(element).removeClass("modal-busy");

}

function showStatusDialog(debugMsg, msg) {
    console.log((typeof debugMsg =="object" && typeof debugMsg.stack == "string" ? (debugMsg.message + "\n" + debugMsg.stack) : debugMsg));
    MessageBox(msg, true);
}

function refreshXDisplay(item) {
    if (typeof $.fn.bootstrapTable !== 'undefined')
        $("#" + item + "Table").bootstrapTable("refresh");

    if (typeof $.fn.magicDiv !== 'undefined')
        $("#" + item + "MagicDiv").magicDiv("refresh");
}

function setSelect2 () {
console.log("select2 Init>>>>");
    if (typeof $.fn.select2 !== 'undefined') {
        //Initialize Select2. Elements
        $('.select2,.needSelect2').select2({
            width: '100%',
            minimumResultsForSearch: Infinity
        });

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap',
            minimumResultsForSearch: Infinity
        });

        // $(".select2[readonly]").select2({disabled: true});
        $('.needSelect2').removeClass('needSelect2');
    }
}


//If a button = null, it should be invisible, if = "" it should not be changed, otherwise it should be visible
function mModalTitleX(typeOrTitle, name, createBtn, deleteBtn=null, cancelBtn="Close") {

    a=10,b=20;

    if (createBtn == null)
        $("#new"+name+"CreateBtn, .new"+name+"CreateBtn").addClass("d-none");
    else if (createBtn != "")
        $("#new"+name+"CreateBtn, .new"+name+"CreateBtn").removeClass("d-none") + $("#new"+name+"CreateBtn span, .new"+name+"CreateBtn span").text(createBtn);

    if (deleteBtn == null)
        $("#new"+name+"DeleteBtn, .new"+name+"DeleteBtn").addClass("d-none");
    else if (deleteBtn != "")
        $("#new"+name+"CreateBtn, .new"+name+"CreateBtn").removeClass("d-none") + $("#new"+name+"DeleteBtn span, .new"+name+"DeleteBtn span").text(deleteBtn);

    if (cancelBtn == null)
        $("#new"+name+"CancelBtn, .new"+name+"CancelBtn").addClass("d-none");
    else if (cancelBtn != "")
        $("#new"+name+"CancelBtn, .new"+name+"CancelBtn").removeClass("d-none") + $("#new"+name+"CancelBtn span, .new"+name+"CancelBtn span").text(cancelBtn);

    if (typeOrTitle == "new")
        document.getElementById("new"+name+"Title").innerHTML = "<i class='far fa-file-alt'></i> &nbsp; New " + name;
    else if (typeOrTitle == "edit")
        return document.getElementById("new"+name+"Title").innerHTML = "<i class='far fa-edit'></i> &nbsp; Edit " + name;
    else
        return document.getElementById("new"+name+"Title").innerHTML =
            "<i class='far " + (typeOrTitle.substr(0,3) == "New" ? "fa-file-alt" : "fa-edit") + "'></i> &nbsp; " + typeOrTitle;
}

function mModalTitleNew(name) {
    return document.getElementById("new"+name+"Title").innerHTML = "<i class='far fa-file-alt'></i> &nbsp; New " + name;
}

function mModalTitleEdit(name) {
    return document.getElementById("new"+name+"Title").innerHTML = "<i class='far fa-edit'></i> &nbsp; Edit " + name;
}

function updateCompanies(companyId=-1) {
    if (companyId==-1) {
        companyId = getCurrentCompany();
        if (companyId == -1) return;
    }

    $('[data-company]').removeClass("currentCompany").removeClass("text-primary");
    $('[data-company="'+companyId+'"]').addClass("currentCompany").addClass("text-primary");
    localStorage.setItem("currentCompany", companyId);
}

function getCurrentCompany() {
    if (!(companyId = localStorage.getItem("currentCompany"))) {
        var c = $('[data-company]');
        if (c.length == 0) return -1;
        return $(c.get(0)).attr("data-company");
    }
    return companyId;
}

function setCurrentCompany(companyId) {
    $.post(eGotoAbsLink("Api/Settings"),{r:"setCurrentCompany", companyId: companyId}, (response)=>{
        try {
            m = JSON.parse(response);
            if (m["status"] == "OK") {
                MessageBox(m["message"], false);
                updateCompanies(companyId);
            } else if (m["status"] == "Error") {
                MessageBox(m["message"], true);
            }
        } catch (e) {
            showStatusDialog(e.stack + "|||" + e + " ::: " + response, "Error. Contact your administrator");
        }

    });
}


function getCurrentTheme() {
    const k = "currentTheme";
    var pj = localStorage.getItem(k);
    if (pj == null)
        return setCurrentTheme({navbar:'light', header: 'light', subItem:'light', logo: 'light', layoutType:'light' });

    return JSON.parse(pj);
}

function setCurrentTheme(value) {
    try {
        const k = "currentTheme";

        localStorage.setItem(k, JSON.stringify(value));
        currentTheme = getCurrentTheme();
        if (currentTheme != null)
            return currentTheme;
    } catch {
    }
    return null;
}

function setCurrentThemeItem(entity, value) {
    if (entity =="") return false;
    try {
        const k = "currentTheme";
        let ct = getCurrentTheme();
        ct[entity] = value;

        localStorage.setItem(k, JSON.stringify(ct));
        currentTheme = getCurrentTheme();
        if (currentTheme != null) {
            applyTheme();
            return currentTheme;
        }
    } catch {
    }
    return null;
}

let currentTheme = getCurrentTheme();