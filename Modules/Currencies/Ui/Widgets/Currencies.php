<?php

namespace Currencies;
use Api\AppDB;
use Currencies\CCurrency;
use Helpers\HtmlHelper;
use Api\Mt;

HtmlHelper::CssInclude(HtmlHelper::_MAGICDIV_["Css"]);
HtmlHelper::CssInclude(["Assets/bundle/css/themify-icons.css", "Assets/bundle/css/icofont.css", "Assistant/Css/Task.css"]);

require_once(__DIR__ . "/../../Api/Bootstrap.php");
?>

<?php
$currencies = CCurrency::fetch();
?>
    <div class="card prod-p-card card-red">
        <div class="card-body">
            <div class="row align-items-center m-b-15">
                <div class="col">
                    <h4 class="m-b-0 f-w-700 text-white"><?= AppDB::numRows($currencies) ?> Currencies</h4>
                </div>
                <div class="col-auto">
                    <i class="fas fa-money-bill-alt text-c-red f-18"></i>
                </div>
            </div>
            <p class="m-b-0 text-white">
                <?php
                while ($item = AppDB::fetchAssoc($currencies)) {
                    echo "<b>{$item['name']}</b> : <a>{$item['ratio']}</a><br>";
                }
                ?>
            </p>
        </div>


    </div>
<?php
HtmlHelper::uses([["Js" => ["Assets/plugins/jquery-longpress/jquery.longpress.js", "Assets/bundle/js/modernizr.js", "Assets/bundle/js/todo.js", "Assistant/Js/Tasks.js"]]]);
HtmlHelper::uses([["Js" => HtmlHelper::_MAGICDIV_["Js"]]]);
