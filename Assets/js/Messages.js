var _F_FIRST_TIME = 0;
var _F_IN_PROGRESS = 1;
var _F_IN_DONE = 2;
var _F_IN_READY = 3;

var messagesFetched = _F_FIRST_TIME;
var notificationsFetched = _F_FIRST_TIME;
var lastNotificationsCheck = "1980-01-01 00:00:00";
var lastMessagesCheck = null;

function onShowMessages() {

    if (messagesFetched == _F_IN_PROGRESS || messagesFetched == _F_IN_DONE) return;

    if (messagesFetched == _F_FIRST_TIME) {
    } else if (messagesFetched == _F_IN_READY) {

    }

    $("#messages-loader").html('<div class=" d-flex justify-content-center align-items-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
    $.post(eGotoAbsLink("Assistant/Api/LastUpdate"), {"r": "getMessages", "ts":lastMessagesCheck}, function (response){
        try {
            $("#messages-loader").html("");
            var m = JSON.parse(response);
            onGetMessages(m);
            lastMessagesCheck = moment().format("YYYY-MM-DD HH:mm:ss");
        } catch {
        }
    });
}

function onShowNotifications() {

    if (notificationsFetched == _F_IN_PROGRESS || notificationsFetched == _F_IN_DONE) return;

    if (notificationsFetched == _F_FIRST_TIME) {
    } else if (notificationsFetched == _F_IN_READY) {

    }

    $("#notifications-loader").html('<div class=" d-flex justify-content-center align-items-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
    $.post(eGotoAbsLink("Assistant/Api/LastUpdate"), {"r": "getNotifications", "ts":lastNotificationsCheck}, function (response){
        try {
            $("#notifications-loader").html("");
            //showStatusDialog(response, "Error. Contact your administrator");
            var m = JSON.parse(response);
            onGetNotifications(m);
            lastNotificationsCheck = moment().format("YYYY-MM-DD HH:mm:ss");
        } catch {
        }
    });

}

var notificationsCache = [];

function processPeer(p) {
    var name = p;
    var username = p;
    var peerId = peer;
    var peerPic = eGotoAbsLink("/users/"+msg['peer']+"/pic");
    if (username.indexOf(",") > 0) {
        name = "Message Broadcast #" + msg['id'];
        username = p;
        peerId = "";
        peerPic = eGotoAbsLink("Assets/img/group.png");
    }
    return {name: name, username: username, id: peerId, pic: peerPic};
}

function onGetMessages(m) {

    if (typeof m["messages"] != "undefined") {
        var e = document.getElementById("chatFirstMessage");
        $("#numMessages").text("");

        for(const msg of m["messages"]) {

            var divEl = $("#chatMessagesList div[data-peer-id='" + msg['peer'] + "']");
            if (divEl.length > 0) {
                divEl.remove();
            }


            var peerData = processPeer(msg['peer']);

            divEl = $('<div class="col-12 p-0 media userlist-box waves-effect waves-light" onclick="showUserChat(this)" data-peer-id="'+peerData.id+'"></div>');

            divEl.html('<div class="align-middle m-b-25 m-0 col-12">' +
                '<img src="' + peerData.pic  + '" alt="user image" class="img-radius img-60 align-top float-left m-r-15">' +
                '<div class="d-block col-12">' +
                '<a href="">' +  peerData.name  + '</a>' +
                '<div class="text-small col-12"><b>' + msg['subject'].replace(/<[^>]+>/g, '') + '</b> ' + msg['message'].replace(/<[^>]+>/g, '') + '</div>' +
                '<span class="status active"></span>' +
                '</div></div>');
            e.insertAdjacentElement('afterend', divEl.get(0));
            e = divEl.get(0);
            numMessages++;
        }
    }
}

function onGetNotifications(m) {
    if (typeof m["notifications"] != "undefined") {
        var e = document.getElementById("notificationsFirstItem");

        $("#numNotifications").text("");

        for(const not of m["notifications"]) {
            var liEl = $('#notificationsMenu li[data-notification-id="' + not.id + '"]');

            if (liEl.length > 0) {
                liEl.remove();
            }

            liEl = $('<li data-notification-date="' + not.date + '" data-notification-id="' + not.id + '"></li>');
            liEl.html('<div class="media" onClick="openLink(\'' + not.link+ '\')">\n' +
                '<img class="img-radius" src="'+ eGotoAbsLink("users/"+not['sender']+"/pic") + '">\n' +
                '<div class="media-body">\n' +
                '<h5 class="notification-user"><b>'+not['sender']+'</b></h5>\n' +
                '<p class="notification-msg">\n' +
                not['message'] +
                '</p>\n' +
                '<span class="notification-time">' + formatDate(not['date']) + '</span>\n' +
                '</div>\n' +
                '</div></a>');
            e.insertAdjacentElement('afterend', liEl.get(0));
            e = liEl.get(0);
            numNotifications++;
        }
    }
}
