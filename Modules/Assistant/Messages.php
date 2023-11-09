<?php

namespace Enterprisa;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Helpers\HtmlHelper;
use Api\Mt;

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_MAGICDIV_]);
HtmlHelper::PageStartX(
    ["title" => "Messages", "description" => "Messages", "path" =>
        ["Messages" => "Messages"]], array("Assets/plugins/summernote/summernote-bs4.css", 'Assistant/Css/Messages.css'));

?>
<!--section start-->
<section class="contact-page section-big-py-space b-g-light">
    <div class="custom-container">
            <div class="row">
                <div class="col-12 col-md-3 col-lg-2">
                    <ul class="page-list nav nav-tabs flex-column pl-2">
                        <li class="btn btn-primary text-left">
                                    <span class="d-block" id="composeButton">
                                        <i class="icofont icofont-inbox"></i> Compose
                                    </span>
                        </li>
                        <li class="btn bg-none text-left">
                                    <span class="d-inline-block w-100" onclick="getMessages('compact')">
                                        <i class="icofont icofont-inbox"></i> Messages
                                        <span class="small badge badge-info right float-right"></span>
                                    </span>
                        </li>
                        <li class="btn bg-none text-left">
                                    <span class="d-inline-block w-100" onclick="getMessages('inbox')">
                                        <i class="icofont icofont-inbox"></i> Inbox
                                        <span class="small badge badge-info right float-right"></span>
                                    </span>
                        </li>
                        <li class="btn bg-none text-left">
                                    <span class="d-inline-block w-100" onclick="getMessages('sent')">
                                        <i class="icofont icofont-paper-plane"></i> Sent Mail
                                    </span>
                        </li>
                    </ul>
                </div>
            <?php
            require_once(__DIR__ . "/Contents/MessagesBox.php");
            require_once(__DIR__ . "/Contents/ReadMessage.php");
            ?>
            </div>
    </div></section>
    <script>
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

        $(function () {
            let funcStr = window.location.hash.replace(/#/g, '') || box;
            (msg == -1 && getMessages(funcStr)) || openMessage(msg);
            $('#compose-textarea').summernote({height: 140, width: '100%'})
        });
    </script>
    <?php
    HtmlHelper::PageFooter(array("Assets/plugins/summernote/summernote-bs4.min.js", 'Assistant/Js/Messages.js'));
    HtmlHelper::newModalX("Message", __DIR__ . "/Contents", "modal-xl", ["buttons" => []]);
    HtmlHelper::PageEndX(); ?>


