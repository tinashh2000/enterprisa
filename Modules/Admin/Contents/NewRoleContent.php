<?php
use Helpers\HtmlHelper;
HtmlHelper::addJsFile("Admin/Js/Roles.js");

?>
<form id="newRoleForm" onsubmit="return false" method="post">
    <input name="r" type="hidden" value="create" />
    <input name="lastModifiedDate" type="hidden" value="" />
    <input name="id" type="hidden" value="0" />
    <input name="auth" type="hidden" value="" />
    <input name="privilegesList" type="hidden" value="" />
    <input name="flags" type="hidden" value="0" />

    <div class="row">
        <div class="col-md-12 col-sm-12">
            <label>Name</label>
            <div class="form-group input-group">
                <input type="text" name='name' class="form-control" placeholder="Name">
            </div>
        </div>-
    </div>

    <div class="row">
        <div class="form-group form-float col-md-12 col-sm-12 ">
            <div class="form-line">
                <label class="form-label">Privileges</label>
                <select id="sprivilegesList" multiple class="select2 col-12" data-placeholder="Privileges">
                    <?php
                    HtmlHelper::getPrivilegesList();
                    ?>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group form-float col-md-12 col-sm-12 ">
            <div class="form-line">
                <label class="form-label">Privileges</label>
                <select id="sprivileges" name="privileges" class="select2 col-12" data-placeholder="Privileges">
                    <?php if (\Api\CPrivilege::isAdmin()) { ?><option value="1">System Administrator</option> <?php } ?>
                    <option value="2" selected>Standard User</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group form-float col-md-12 col-sm-12 ">
            <div class="form-line">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
        </div>
    </div>
</form>

<?php
?>
<script>
    // $(()=> {
    //     $("#profilePicFile").change(function () { this.pId = "profilePicImg"; readPictureURL(this); });
    // });
</script>
