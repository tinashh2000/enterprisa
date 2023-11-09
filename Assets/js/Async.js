function openLink(lnk) {
    if (lnk == "") return;
    window.location.href = eGotoAbsLink(lnk);
}

var latestNotifications = " ";
var latestMessages = " ";
var curDelay = 10000;
var minDelay = 15000;
var maxDelay = 60000;
var nAsyncResponses = 0;
var notifications=null;
var messages=null;

var numMessages = 0;
var numNotifications = 0;

function getUpdates() {
    $.post(eGotoAbsLink("Assistant/Api/LastUpdate"), {"r": "getStats"}, (response) => {
        // showStatusDialog(response, "Error. Contact your administrator");
        try {
            if (response == "") {
                if (curDelay < maxDelay) curDelay = (curDelay + 5000) < maxDelay ? curDelay + 5000 : maxDelay;
            } else {
                let m = JSON.parse(response);
                if (m['status'] == "OK") {

                    if (curDelay >= minDelay) curDelay = ((curDelay - 5000) < minDelay) ? minDelay : curDelay - 5000;

                    var nMsg = m['numMessages'];
                    var nNot = m['numNotifications'];

                    if (nMsg != numMessages)
                        $("#numMessages").text((nMsg > 0 ? nMsg : ""));

                    if (nNot != numNotifications)
                        $("#numNotifications").text((nNot > 0 ? nNot : ""));

                    numMessages = nMsg;
                    numNotifications = nNot;

//                    console.log(response);

                    if (latestMessages < m["latestMessageDate"] && latestMessages != " ") {
                        playSound(eGotoAbsLink("Assets/Audio/NewMessage.m4a"));
                    } else if (latestNotifications < m["latestNotificationsDate"] && latestNotifications != " ") {
                        playSound(eGotoAbsLink("Assets/Audio/NewNotification.m4a"));
                    }

//                    console.log("(Old", latestMessages, "New", m["latestMessageDate"], ")", moment().format("YYYY-MM-DD HH:mm:ss").toString());
//                    console.log("(NOld", latestNotifications, "New", m["latestNotificationsDate"], ")");

                    latestMessages = m["latestMessageDate"];
                    latestNotifications = m["latestNotificationsDate"];
                }
            }
            setTimeout(getUpdates, minDelay);
        } catch (e) {
            console.log("Error " + e + ">>>" + response);
            setTimeout(getUpdates, minDelay);
        }
    });
}

function showUserChat(e) {
    var username = $(e).attr("data-peer-id");
    var curUsername = $(".userChatArea").attr("data-current-user");
    if (username != curUsername) {

        peerData = processPeer(username);

        console.log("Username: " + peerData.name);
        var chatMessages = $(".userChatArea #userChatMessages");
        chatMessages.html('<div class="v-align"><div class=" d-flex justify-content-center align-items-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div></div>');
        $(".userChatArea .chatUserFullname").html("<a href='"+ peerData.username+ "'>" + peerData.username + "</a>");
        $(".userChatArea .chatUsername").html(username);
        $(".userChatArea .chatUserPic").attr( "src", peerData.pic);
        $.post(eGotoAbsLink("Assistant/Api/Message"), {"r" : "getUserMessages", "user" : peerData.username }, (response)=> {
            try {
                $(".chat-reply-box").removeClass("d-none");
                chatMessages.html("");
                var m = JSON.parse(response);
                var messages = m["messages"];
                for(const message of messages) {
                    var peer;
                    var inbox = true;
                    if (message.box == "in") {
                        peer = message.sender;
                    } else {
                        peer = message.recipient;
                        inbox=false;
                    }

                    chatMessages.append(inbox ? '<div class="media chat-messages">' +
                        '<a class="media-left photo-table" href="">' +
                        '    <img class="media-object img-radius img-radius m-t-5" src="' + eGotoAbsLink('users/'+message.peer+'/pic') + '">' +
                        '</a>' +
                        '<div class="media-body chat-menu-content">' +
                        '    <div class="">' +
                        '        <div class="chat-cont">' + message.message + '</div>' +
                        '    </div>' +
                        '    <p class="chat-time">' + formatDate(message.date) + '</p>' +
                        '</div>' +
                        '</div>': '<div class="media chat-messages">\n'+
                        '    <div class="media-body chat-menu-reply">\n' +
                        '        <div class="">\n' +
                        '           <div class="chat-cont">' + message.message + '</div>\n' +
                        '        </div>\n' +
                        '        <p class="chat-time">' + formatDate(message.date) + '</p>\n' +
                        '    </div>' +
                        '<a class="media-right photo-table" href="">' +
                        '    <img class="media-object img-radius img-radius m-t-5" src="' + eGotoAbsLink('users/me/pic') + '">' +
                        '</a>'+
                        '</div>');
                }
            }catch(e) {
                alert(e + ">>>" + response);
            }
        });
    }

    var my_val = $('.pcoded').attr('vertical-placement');
    if (my_val == 'right') {
        var options = {direction: 'left'};
    } else {
        var options = {direction: 'right'};
    }
    $('.userChatArea').toggle('slide', options, 500);

}

$(function () {
    //getUpdates();
});