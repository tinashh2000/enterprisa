'use strict';
$(document).ready(function () {
    $("#basic-forms").steps({headerTag: "h3", bodyTag: "fieldset", transitionEffect: "slideLeft", autoFocus: true});
    $("#verticle-wizard").steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "slide",
        stepsOrientation: "vertical",
        enableAllSteps: true,
        autoFocus: true
    });

    $("#design-wizard").steps({headerTag: "h3", bodyTag: "fieldset", transitionEffect: "slideLeft", autoFocus: true});

});

var customOnFinished = [];

function setOnFinished(id, f) {
    customOnFinished[id] = f;
}

function onFinished(form) {
    if (typeof customOnFinished[id] == "function") {
        customOnFinished[id]();
    }
}

function initFW(id, onFinish) {
    var form = $(id).show();
    form.steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "slideLeft",
        enableAllSteps: true,
        showFinishButtonAlways: true,
        // enablePagination: false,
        labels: {
            cancel: "Cancel",
            current: "current step:",
            pagination: "Pagination",
            finish: "Save",
            next: "Next",
            previous: "Previous",
            loading: "Loading ..."
        },
        onStepChanging: function (event, currentIndex, newIndex) {
            console.log("Changing Steps");
            if (currentIndex > newIndex) {
                return true;
            }
            if (newIndex === 3 && Number($("#age-2").val()) < 18) {
                return false;
            }
            if (currentIndex < newIndex) {
                form.find(".body:eq(" + newIndex + ") label.error").remove();
                form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
            }
            form.validate().settings.ignore = ":disabled,:hidden";
            return form.valid();
        },
        onStepChanged: function (event, currentIndex, priorIndex) {
            if (currentIndex === 2 && Number($("#age-2").val()) >= 18) {
                form.steps("next");
            }
            if (currentIndex === 2 && priorIndex === 3) {
                form.steps("previous");
            }
        },
        onFinishing: function (event, currentIndex) {
            form.validate().settings.ignore = ":disabled";
            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            return onFinish(event, currentIndex);
        }
    }).validate({
        errorPlacement: function errorPlacement(error, element) {
            element.before(error);
        }, rules: {confirm: {equalTo: "#password"}}
    });

}