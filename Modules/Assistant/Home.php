<?php
namespace Enterprisa;

require_once("Api/Bootstrap.php");
require_once("Scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Helpers\HtmlHelper;
use Api\Users\CurrentUser;
use Api\Users\CUser;
use Api\Mt;

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_JQUERY_STEPS_]);
HtmlHelper::PageStartX(
    ["title" => "Profile", "description" => "User Profile", "path" =>
        ["Home" => "User Profile"]], null);

HtmlHelper::includeJS("Assets/js/countries-select2.js"); ?>

<div class="mt-main-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <img class="profile-user-img img-fluid img-circle" src="people/me/pics/profile"
                                         alt="User profile picture">
                                </div>
                                <h3 class="profile-username text-center" id="userFullname"></h3>
                                <p class="text-muted text-center" id="userProfile"></p>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->

                        <!-- About Me Box -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">About Me</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <strong><i class="fas fa-book mr-1"></i> Education</strong>

                                <p class="text-muted" id="profileEducation">
                                </p>

                                <hr>

                                <strong><i class="fas fa-map-marker-alt mr-1"></i> <span
                                            id="userLocation">Location</span></strong>

                                <p class="text-muted" id="userCity"></p>

                                <hr>

                                <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>

                                <p class="text-muted" id="profileSkills">
                                </p>

                                <hr>

                                <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>
                                <p class="text-muted" id="profileNotes"></p>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->

                    <div class="col-md-9">
                        <?php require_once(__DIR__ . "/../Admin/Contents/NewUserContent.php"); ?>
                    </div>
                </div>
            </div>

<?php
HtmlHelper::PageFooter(array("Assets/js/countries-select2.js", "Assets/js/Person.js", "Admin/Js/Users.js"));
HtmlHelper::PageEndX();?>

<script>

    $(() => {
        editUser2();
    });

    initFW("#newUserForm", function() {
        submitUserForm(userForm);
    });
</script>

