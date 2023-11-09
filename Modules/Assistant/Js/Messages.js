var currentBox ="in";
var qBox = currentBox;
var boxStart = 0;
var boxLimit = 20;
var msgs = null;    //JSON tree of messages displayed
var contactList = null;

function showMessagesList() {
    $("#messagesBox").removeClass("d-none");
    $("#readMessage").addClass("d-none");
}

function showMessage() {
    $("#messagesBox").addClass("d-none");
    $("#readMessage").removeClass("d-none");
}

function getMessages(box, handler=onGetMsgs) {
    if(typeof handler != "function") return;
    if (box != currentBox) {
        boxStart = 0;
        boxLimit=20;
    }

    if (box != "inbox" && box != "sent" && box != "compact") return;
    qBox = box;

    var messagesList = document.getElementById("messagesList");

    $("#messagesList").attr("data-magicDiv-source", homeLink + "/Helpers/FetchMessages?box=" + qBox);
    $("#messagesList").magicDiv("refresh");

    return true;
}

function onGetMsgs(response) {

    try {
        var m = JSON.parse(response);
        msgs = m["messages"];
        var messagesList = document.getElementById("messagesList");
        messagesList.innerHTML = "";

        window.history.pushState({}, (qBox == 'sent' ? "Sent messages" : "Inbox"), eGotoAbsLink("Assistant/Messages#" + qBox));

        if (msgs.length == 0) {
            messagesList.innerHTML = "<tr colspan='6' class='mailbox-nothing'><td><h2><a>Nothing to show</a></h2></td></tr>";
        }

        var subjectEl = document.createElement("div");
        var msgEl = document.createElement("div");

        for (var c = 0; c < msgs.length; c++) {
            var msg = msgs[c];
            var peer = msg.peer;

            subjectEl.innerHTML = msg.subject;
            msgEl.innerHTML = msg.message.substring(0,30);

            var subject = subjectEl.innerText;
            var message = msgEl.innerText;

            subject = subject.trim() == "" ? "(no subject)" : subject;

            var idx = (msg.id * 82) + 72;

            messagesList.innerHTML += "<tr mail-data='" + idx + "' " + ((msg.flags & 1) > 0 ? "class='unread-message'" : "'read-message'" ) + " onclick='openMessage(this)'>" +
                "<td class='mailbox-check'>" +
                "  <div class='icheck-primary'>" +
                "    <input type='checkbox' value='' id='check" + c + "'>" +
                "    <label for='check" + c + "'></label>" +
                "  </div>" +
                "</td>" +
                "<td class='mailbox-star'><a href='#'><i class='fas fa-star text-warning'></i></a></td>" +
                "<td class='mailbox-name'><a href='users/" + peer + "'>" + peer + "</a></td>" +
                "<td class='mailbox-subject'><b>" + subject + "</b> - " + message + "</td>" +
                "<td class='mailbox-attachment'></td>" +
                "<td class='mailbox-date'><a class='mt-utc-dynamic-time' data-utc-time='"+msg.date+"'></a></td>" +
                "</tr>";
        }
        currentBox = qBox;
    } catch (e) {
        alert(e + " :: " + response);
    }
}

function openMessage(e) {
    var v;
    v = (typeof e == "object") ? e.getAttribute("mail-data") : parseInt(e);
    var oidx = (v - 72) / 82;

    document.getElementById("messageSubject").innerHTML = "";
    document.getElementById("messageEmail").innerHTML = "";
    document.getElementById("messageDate").innerHTML = "";
    document.getElementById("messageContents").innerHTML = '<div class="mail-loader"><div class="mail-loader-inner" class="col-12 d-flex justify-content-center align-self-center p-5"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div></div>';
    showMessage();

    $.post(eGotoLink("Api/Message"), {"r": "get", "id": oidx, "box" : currentBox}, (response) => {
        try {
            var m = JSON.parse(response)["message"];
            var peer = m.peer;
            var fullName = typeof m.fullName != "undefined" && m.fullName != null && m.fullName != "" ? m.fullName + " (<a href='"+eGotoAbsLink("users/"+peer)+"'>" + peer + "</a>)" : peer;
            document.getElementById("messageSubject").innerHTML = m.subject;
            document.getElementById("messageEmail").innerHTML = fullName;
            document.getElementById("messageDate").innerHTML = m.date;
            document.getElementById("messageContents").innerHTML = m.message;

            window.history.pushState({}, 'View Message', eGotoAbsLink("messages/" + qBox + "/" + v));

        } catch (e) {
            showStatusDialog(e.stack, response);
        }
    });
    return true;
}

$(function () {

        if ($("#newMessageModal")) {
            $("#newMessageTitleBar").removeClass("d-none");
        }

        $('.checkbox-toggle').click(function () {
            var clicks = $(this).data('clicks')
            if (clicks) {
                //Uncheck all checkboxes
                $('.mailbox-messages input[type=\'checkbox\']').prop('checked', false)
                $('.checkbox-toggle .far.fa-check-square').removeClass('fa-check-square').addClass('fa-square')
            } else {
                //Check all checkboxes
                $('.mailbox-messages input[type=\'checkbox\']').prop('checked', true)
                $('.checkbox-toggle .far.fa-square').removeClass('fa-square').addClass('fa-check-square')
            }
            $(this).data('clicks', !clicks)
        });

        //Handle starring for glyphicon and font awesome
        $('.mailbox-star').click(function (e) {
            e.preventDefault()
            //detect type
            var $this = $(this).find('a > i')
            var glyph = $this.hasClass('glyphicon')
            var fa = $this.hasClass('fa')

            //Switch states
            if (glyph) {
                $this.toggleClass('glyphicon-star')
                $this.toggleClass('glyphicon-star-empty')
            }

            if (fa) {
                $this.toggleClass('fa-star')
                $this.toggleClass('fa-star-o')
            }
        });

        $('#composeButton').click(function (e) {
            $('#newMessageModal').modal('show');

            $("#newMessageModal").on("hide.bs.modal", function () {
//            saveDraft(contacts, message);
            });
        });

        $('#inbox').click(function (e) {
            getMessages("inbox");
        });

        $('#sentBox').click(function (e) {
            getMessages("sent");
        });

        $('#draftBox').click(function (e) {
            getMessages("drafts");
        });

        $("#moreOptions").click(function (e) {
            $("#advancedOptions").toggleClass("d-none");
            if ($("#advancedOptions").hasClass("d-none")) {
                $("#moreOptions").text("More Options >>");
            } else {
                $("#moreOptions").text("<< Less Options");
            }
        });

        initValidate('#newMessageForm', sendMessage);

        $.post(eGotoAbsLink("Api/Users"), {"r": "fetch", "start": 0, "limit": 10000}, (response) => {
            try {
                var m = JSON.parse(response);
                contactList = m["users"];
                var e = document.getElementById("messageRecipient");

                e.innerHTML = "<optgroup label='Internal Users'>";
                for (var c = 0; c < contactList.length; c++) {
                    var item = contactList[c];
                    if (item.username != currentUser)
                        e.innerHTML += "<option value='" + item.username + "'>" + item.fullName + "</option>";
                }

                let selected = [];
                if (typeof extraRecipients != "undefined") {
                    e.innerHTML += "<optgroup label='Contacts'>";
                    for (const recipient of extraRecipients) {

                        if (recipient.contact != currentUser)
                            e.innerHTML += "<option value='" + recipient.contact + "'>" + recipient.name + "</option>";

                        selected[selected.length] = recipient.contact;
                    }
                }
                //$("#messageRecipient").select2({width: '100%', tags: true}).val(selected).trigger("change");;
                initXSelect2({el: "#messageRecipient", name: 'Recipient', tags:true, selected: selected});
            } catch (e) {
                showStatusDialog(e.stack, response);
            }
        });
});

function sendMessage (form) {

    var recipients = $("#messageRecipient").val();
    var categories = $("#recipientCategories").val(); //.join(",");
    var types = $("#recipientTypes").val(); //.join(",")
    if (recipients == "" && categories == "" && types == "") {
        MessageBox("Enter valid recipients", true);
        return;
    } else if (form.message.value == "") {
        MessageBox("Blank message not allowed", true);
        return;
    }

    form.recipients.value=recipients.join(",");
    form.categories.value = categories; //.join(",");
    form.types.value = types; //.join(",");
    form.r.value = "create";
    var fData = $("#newMessageForm").serializeArray();
    $.post(eGotoLink("Api/Message"), fData, (response)=>{
        try {
            var m = JSON.parse(response);
            if (m['status'] == "OK") {
                $('#newMessageModal').modal('hide');
                MessageBox(m["message"], false);
            } else {
                MessageBox(m["message"], true);
            }
        }catch(e) {
            showStatusDialog(response, "Error. Contact your administrator");
        }
    });
    return false;
}

function onBeforeMessagesRendered() {
    $("#messagesList").addClass("d-none");
}

function onMessagesRendered() {
    ConvertUTCTime("#messagesList .magicDivContainer .mt-msg-time");
    $("#messagesList").removeClass("d-none");
}