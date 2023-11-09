<?php
namespace Users;

use Api\CPrivilege;
use Helpers\HtmlHelper;
use Api\Mt;
use Api\Users\CurrentUser;

if (!isset($isAdmin)) $isAdmin = false;

?>
<div class="row">
    <div class="col-md-12">
        <div>
            <form id="newUserForm" class="wizard-form jqStepsForm" onsubmit="return false" action="#">
                <input name="r" type="hidden" value="create"/>
                <input name="lastModifiedDate" type="hidden" value=""/>
                <input name="auth" type="hidden" value=""/>
                <input name="flags" type="hidden" value="0"/>
                <h1> General information </h1>

                <div class='p-0 pr-3 mb-5'>
                    <?php
                    $personRequirements = array("name", "email", "phone", "isNotAPerson");
                    require(Mt::$appDir . "/Modules/Assistant/Contents/PersonTemplate.php"); ?>
                </div>

                <h3> User Profile</h3>

                <div class=' p-0 pr-3 mb-5'>
                    <?php require("UserTemplate.php"); ?>
                </div>


                <div class="mt-3">
                    <div class="row">
                        <div class="col-12">
                            <span>
                                <button type="submit" id="updateProfileBtn"
                                class="btn btn-primary mr-2 pl-5 pr-5">Save Profile</button></span>

                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>