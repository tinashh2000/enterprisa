<?php
use Helpers\HtmlHelper;
HtmlHelper::addJsFile('Admin/Js/DataFields.js');
?>

    <?php
    $GLOBALS['dataFieldsConfig'] = ["name" => "Package", "fields" => []];
    require_once (__DIR__ . "/../../Assistant/Contents/DataFieldsDesigner.php");?>

<?php
?>
