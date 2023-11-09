<?php
namespace Enterprisa;

require_once("Api/Bootstrap.php");
require_once("scripts/CheckLogin.php");
require_once("Scripts/HtmlHelper.php");

use Api\CPrivilege;
use Api\Users\CurrentUser;
use Api\Users\CUser;
use Helpers\HtmlHelper;
use Api\Mt;


$u = $_GET['u'];
 if (basename($_SERVER['PHP_SELF']) !="redir.php"){
     die("W");
 }

if (!$user = CUser::get($u)){
    die("<script>window.location.href='" . HtmlHelper::link("404.php") . "'</script>");
}
$profile = json_decode($user['profile'], true);
if (!isset($profile['notes'])) $profile['notes']="";
if (!isset($profile['education'])) $profile['education']="";
if (!isset($profile['skills'])) $profile['skills']="";

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_]);
HtmlHelper::PageStartX(
    ["title" => "Profile", "description" => "User Profile", "path" =>
        ["Home" => "User Profile"]], null);

$isAdmin = CPrivilege::isAdmin();
?>

<script src="<?php echo HtmlHelper::link("Assets/js/countries-select2.js") ?>"></script>

<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
                <div class="row">
          <div class="col-2 col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" src="<?php echo HtmlHelper::getProfilePic($_GET['u']); ?>" alt="User profile picture">
                </div>

                <h3 class="profile-username text-center"><?php echo $user["name"]; ?></h3>

                <p class="text-muted text-center"><?php echo $user["name"]; ?></p>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <!-- About Me Box -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">About <?php echo $user["name"]; ?></h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <strong><i class="fas fa-book mr-1"></i> Education</strong>

                <p class="text-muted">
                    <?php echo $profile["education"]; ?>
                </p>

                <hr>

                <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>

                <p class="text-muted"><?php echo $user["city"] . ", " . $user["country"]; ; ?></p>

                <hr>

                <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>

                <p class="text-muted">
                    <?php echo $profile["skills"]; ?>
                </p>

                <hr>

                <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>

                <p class="text-muted"><?php echo $profile["notes"]; ?></p>
              </div>
            </div>
          </div>
          <div class="col-10 col-sm-10 col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Profile</a></li>
                  <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                  <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a></li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="profile">
                      <?php require("Contents/UserProfileContent.php") ?>
                  </div>
                    <div class="active tab-pane" id="activity">
                        <?php require("Contents/ActivityContent.php") ?>
                  </div>
                  <div class="tab-pane" id="timeline">
                      <?php require("Contents/TimelineContent.php") ?>
                  </div>
                </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.nav-tabs-custom -->
          </div>
          <!-- /.col -->
                </div></div></div></div></div>
<?php 
HtmlHelper::PageFooter();
?>

</body>
</html>
