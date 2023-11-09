<?php
use Helpers\HtmlHelper;
?>
<form id="newCustomFormForm" onsubmit="return false" method="post">
    <input name="r" type="hidden" value="create" />
    <input name="lastModifiedDate" type="hidden" value="" />
    <input name="id" type="hidden" value="0" />
    <input name="auth" type="hidden" value="" />
    <input name="flags" type="hidden" value="0" />

    <?php require_once (__DIR__ . "/../Assistant/Contents/DataFieldsDesigner.php");?>

</form>

<?php
?>
<script>
    // $(()=> {
    //     $("#profilePicFile").change(function () { this.pId = "profilePicImg"; readPictureURL(this); });
    // });
</script>
