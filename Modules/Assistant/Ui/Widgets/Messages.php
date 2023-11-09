<?php

use Api\Mt;
use Helpers\HtmlHelper;

HtmlHelper::CssInclude("Assistant/Css/Chat.css");
//HtmlHelper::uses([["Js" => ["Assistant/Js/Messages.js"]]]);

?>
    <script>
        function peerFormatter(e) {
            return "Nothing";
        }

        function chatFormatter(e) {
            e.peerImage = peerImageFormatter(e);
            e.message = new DOMParser().parseFromString(e.message, "text/html").body.innerText;
        }

        function peerImageFormatter(e) {
            var peer = e.peer;
            if (peer.indexOf(",") != -1)
                return "<?= Mt::$appRelDir ?>/Assets/plugins/fontawesome-free/svgs/solid/users.svg";

            return "<?= Mt::$appRelDir ?>/people/" + peer + "/pics/profile";
        }
    </script>
    <div class="card h-100">
        <div class="card-block p-0">

            <div class="">
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#msgWidgetMessages"
                                                    data-bs-toggle="tab"><i class="fas fa-envelope"></i> &nbsp; Messages</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="#msgWidgetCustomers" data-bs-toggle="tab"><i
                                            class="fas fa-user"></i> &nbsp; Customers</a></li>
                            <li class="nav-item"><a class="nav-link" href="#msgWidgetNotifications"
                                                    data-bs-toggle="tab"><i class="fas fa-bell"></i> &nbsp;
                                    Notifications</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane show active" id="msgWidgetMessages">
                        <div class="widget-content overlayScrollContainer">

                            <div id="peopleMagicDiv"
                                 class="magicDiv mt-3"
                                 data-magicDiv-paginate="true"
                                 data-magicDiv-renderer="renderPerson"
                                 data-double-click="personDblClick"
                                 data-magicDiv-source="<?php echo Mt::$appRelDir ?>/Helpers/FetchMessages?uni=1&limit=100"
                                 data-magicDiv-formatter="chatFormatter"
                                 data-magicDiv-toolbar="#mdivToolbar"
                                 data-magicDiv-numRows="2">

                                <div class="magicDivTemplate">

                                    <div class="media">
                                        <div>
                                            <div class="img-thumbnail ui-widget-shadow float-left p-0 mr-1"><img class="msg-profile-img" src="{peerImage}" alt=""></div>
                                            <div class="media-body">
                                                <div class="col-xs-12">
                                                    <div class="d-inline-block">
                                                        <b>{peer}</b></div>
                                                    <label class="float-right label label-warning">Agent</label>
                                                    <div class="f-13 text-muted smaller-text">
                                                        {date}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-block mt-3 small-text col-12 chat-message-preview">{message}
                                            </div>

                                            <hr>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                        <div class="tab-pane fade" id="msgWidgetCustomers">
                            POS
                        </div>
                        <div class="tab-pane fade" id="msgWidgetNotifications">

                        </div>
                    </div>
                </div>
            </div>

        </div>


    <script>
        var isMessageWidget = true;
        $(function () {
            var box = 'inbox';
            var msg = -1;
            <?php
            if (isset($_GET['box'])) {
                echo "box = '{$_GET['box']}';";
                if (isset($_GET['msg'])) {
                    echo "msg = '{$_GET['msg']}';";
                }
            }
            ?>
            var funcStr = window.location.hash.replace(/#/g, '') || box;
            getMessages(funcStr, onGetMessagesWidget);
        });


        function onGetMessagesWidget(response) {

            try {
                var m = JSON.parse(response);
                msgs = m["messages"];
                var messagesList = document.getElementById("messagesList");
                messagesList.innerHTML = "";

                if (msgs.length == 0) {
                    messagesList.innerHTML = "<tr colspan='6' class='mailbox-nothing'><td><h2><a>Nothing to show</a></h2></td></tr>";
                }

                var subjectEl = document.createElement("div");
                var msgEl = document.createElement("div");

                for (var c = 0; c < msgs.length; c++) {
                    var msg = msgs[c];
                    var peer = msg.peer;

                    subjectEl.innerHTML = msg.subject;
                    msgEl.innerHTML = msg.message.substring(0, 30);

                    var subject = subjectEl.innerText;
                    var message = msgEl.innerText;

                    subject = subject.trim() == "" ? "(no subject)" : subject;

                    var idx = (msg.id * 82) + 72;

                    messagesList.innerHTML += "<tr mail-data='" + idx + "' " + ((msg.flags & 1) > 0 ? "class='unread-message'" : "'read-message'") + " onclick='openMessage(this)'>" +
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
                        "<td class='mailbox-date'>" + msg.date + "</td>" +
                        "</tr>";
                }
                currentBox = qBox;
            } catch (e) {
                alert(e + " :: " + response);
            }
        }


    </script>

<?php


