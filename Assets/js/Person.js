$(function () {
    $("#profilePicFile").change(function () {
        this.pId = "profilePicImg";
        readPictureURL(this);
    });

    $("#mCountryPicker").select2({
        minimumResultsForSearch: 10,
        data: countriesS2,
        width: '100%'
    });

    $("#mCountryPicker").val("Zimbabwe").trigger("change");

});

function personEmailChanged(frm, whatChanged) {
    $("#personFormEmail").off("change");

    personEnableControls(frm, false);

    $("#personSearchStatus").html('<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></span>');

    var frmData = new FormData();
    frmData.append("r", whatChanged == frm.email ? "getByEmail" : "getByName");
    frmData.append("query", whatChanged == frm.email ? frm.email.value : frm.name.value);

    jQuery.post({
        url: eGotoAbsLink("Api/Person"),
        type: "POST",
        data: frmData,
        processData: false,
        contentType: false,
        success: (response) => {
            try {
                var m = JSON.parse(response);

                if (m["status"] == "OK") {
                    var p = m["person"];

                    // $("#personSearchStatus").html("<a class='text-danger'>already exists</a>");
                    $(frm).find(".person-container select.select2").select2({disabled: false, width: '100%'});
                    initPersonForm(frm, p);
//                    $(frm).find(".person-container select.select2").select2({disabled: true, width: '100%'});
                    frm.r.value="create";
                    $("#personSearchStatus").html("");
                    return;
                } else {
                    // $("#personSearchStatus").html("<a class='text-primary'>(available for use)</a>");
                }

            }catch (e) {
                $("#personSearchStatus").html("<a class='text-danger'>Could not verify email</a>");
            }

            personEnableControls(frm);

            $("#personSearchStatus").html("");

            $("#personFormEmail, #personFormName").on("change", (e) => {
                personEmailChanged(frm, e.target);
            });

        }
    });
}

function personEnableControls(frm, enable=true) {
    if (enable) {
        $(frm).find(".person-container input, .person-container textarea, .person-container select").removeAttr("readonly");
        $(frm).find(".person-container select.select2").prop("disabled", false); //select2({disabled: false, width: '100%'});
    } else {
        $(frm).find(".person-container input, .person-container textarea, .person-container select").attr("readonly",'false');
        $(frm).find(".person-container select.select2").prop("disabled", true); //select2({disabled: false, width: '100%'});
    }
}

function initPersonForm(frm, p=null) {
    frm.reset();
    if (p==null)
        personEnableControls(frm, true);

    $("#personSearchStatus").html("");

    if (p == null) {
        frm.r.value = "create";
        frm.personId.value = "0";
        frm.uid.value = "";

        if (typeof frm.profile != "undefined")
            frm.profile.value="";

        frm.address.value="";

        $(frm.country).val("Zimbabwe").trigger("change");

        if(typeof frm.pic != "undefined") frm.pic.src = eGotoAbsLink('Assets/img/placeholder.jpg');
        if(typeof frm.typeList != "undefined") $(frm.typeList).val("").trigger("change");
        if(typeof frm.categoriesList != "undefined") $(frm.categoriesList).val("").trigger("change");
        if(typeof frm.visibilityList != "undefined") $(frm.visibilityList).val("*").trigger("change");
        $("#personFormEmail, .personFormEmail").on("change", (e) => {
            personEmailChanged(frm, e.target);
        });

    } else {
        frm.r.value =  "edit";
        frm.uid.value =  p.uid;
        frm.personId.value = typeof p.personId != "undefined" ?  p.personId :  p.id;
        frm.title.value = p.title;
        frm.name.value = p.name;
        frm.email.value = p.email;
        frm.phone.value = p.phone;
        frm.mobilePhone.value =p.mobilePhone;
        frm.website.value =p.website;
        var addrs = p.address.split("\n");
        frm.address1.value= addrs.length > 0 ? addrs[0] : "";
        frm.address2.value= addrs.length > 1 ? addrs[1] : "";
        frm.city.value =p.city;
        frm.postalCode.value =p.postalCode;
        frm.fax.value =p.fax;
        if(typeof frm.personNotes != "undefined") frm.personNotes.value = p.personNotes;
        if(typeof frm.dob != "undefined") frm.dob.value=p.dob;
        if(typeof frm.idNumber != "undefined") frm.idNumber.value=p.idNumber;
        if(typeof frm.pic != "undefined") frm.pic.src=homeLink  + "/people/" + p.uid + "/pic";
        $(frm.country).val(p.country).trigger("change");

        if(typeof frm.typeList != "undefined") $(frm.typeList).val(p.type.split(",")).trigger("change");
        if(typeof frm.categoriesList != "undefined") $(frm.categoriesList).val(p.categories.split(",")).trigger("change");
        if(typeof frm.visibilityList != "undefined") $(frm.visibilityList).val(p.visibility).trigger("change");

        if(typeof frm.maritalStatus != "undefined") $(frm.maritalStatus).val(p.maritalStatus).trigger("change");
        if(typeof frm.gender != "undefined") $(frm.gender).val(p.gender).trigger("change");
        $("#personFormEmail").off("change");
    }
}

var noContact=0;

function preparePersonForm(frm) {
    if (frm.email.value.trim() == "" && frm.phone.value.trim() == "" && noContact < 2) {
        MessageBox("Please enter either a phone number or email", true);
        noContact++;
        return;
    }

    noContact = 0;
    var profileItems = {};
    var c = $("[data-entity]");
    c.each(function(){
        profileItems[$(this)[0].name] = $(this)[0].value;
    });

    if (typeof frm.profile != "undefined")
        frm.profile.value = JSON.stringify(profileItems);

    if (typeof frm.address != "undefined" && typeof frm.address1 != "undefined" && typeof frm.address2 != "undefined")
        frm.address.value = frm.address1.value + "\n" + frm.address2.value;

    if (typeof frm.categoriesList != "undefined")
        frm.categories.value =  $(frm.categoriesList).select2("val").toString();

    if (typeof frm.attributesList != "undefined")
        frm.attributes.value =  $(frm.attributesList).select2("val").toString();

    if (typeof frm.typeList != "undefined")
        frm.type.value= $(frm.typeList).select2("val").toString();

    if (typeof frm.visibilityList != "undefined")
        frm.visibility.value= $(frm.visibilityList).select2("val").toString();

    return true;
}