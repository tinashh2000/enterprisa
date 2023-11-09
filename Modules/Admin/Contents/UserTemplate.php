<?php
namespace Users;

use Api\CPrivilege;
use Helpers\HtmlHelper;
use Api\Mt;
use Api\Users\CurrentUser;

$isAdmin = CPrivilege::isAdmin();

if (!isset($userRequirements))
    $userRequirements = array("username");
?>
<input name="personUid" type="hidden" value="" />
<input name="userId" type="hidden" value="0" />
<input name="roles" type="hidden" value="" />
<input name="privilegesList" type="hidden" value="" />

<div class="row">
    <div class="col-md-12 col-sm-12 ">
        <label>Username</label>
        <div class="form-group input-group">
            <input type="text" name='username' class="form-control" placeholder="Username"<?php echo array_search("username", $userRequirements) !== FALSE ? " required" :"" ?>>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 ">
        <label>Password</label>
        <div class="form-group input-group">
            <input type="password" name='password' class="form-control" placeholder="Password">
        </div>
    </div>
    <div class="col-md-12 col-sm-12 ">
        <label>Confirm Password</label>
        <div class="form-group input-group">
            <input type="password" name='cpassword' class="form-control" placeholder="Confirm Password">
        </div>
    </div>
</div>

<?php if ($isAdmin) { ?>
    <div class="row">
        <div class="form-group form-float col-md-12 col-sm-12 ">
            <label class="form-label">Privileges</label>
            <select id="sprivilegesList" multiple class="select2"
                    data-placeholder="Privileges">
                <?php
                HtmlHelper::getPrivilegesList();
                ?>
            </select>
        </div>

        <div class="form-group form-float col-md-12 col-sm-12 ">
            <div class="form-line">
                <label class="form-label">User Roles</label>
                <select id="rolesList" name="rolesList" multiple class="select2 col-12"
                        data-placeholder="Select Roles">
                </select>
            </div>
        </div>
    </div>
<?php } ?>

<div class="row">
    <div class="col-md-12 col-sm-12">
        <label>Notes</label>
        <div class="form-group input-group">
                            <textarea type="text" name='comments' class="form-control"
                                      placeholder="Notes"></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group mb-0">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="sendActivationEmail" id="sendActivationEmail" class="custom-control-input" checked />
                <label class="custom-control-label" for="sendActivationEmail">Send notification email to user</label>
            </div>
        </div>
    </div>
</div>
