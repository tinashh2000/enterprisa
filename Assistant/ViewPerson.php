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
use Api\CPerson;


$p = $_GET['p'];
 if (basename($_SERVER['PHP_SELF']) !="redir.php"){
     die("W");
 }

if (!$person = CPerson::get($p)){
    die("<script>window.location.href='" . HtmlHelper::link("404.php") . "'</script>");
}
//$profile = json_decode($user['profile'], true);
//if (!isset($profile['notes'])) $profile['notes']="";
//if (!isset($profile['education'])) $profile['education']="";
//if (!isset($profile['skills'])) $profile['skills']="";

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Assistant");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_]);
HtmlHelper::PageStartX(
    ["title" => "Profile", "description" => "User Profile", "path" =>
        ["Assistant/Contacts" => "People", "people/{$person['id']}"=>$person['name']]], null);

$isAdmin = CPrivilege::isAdmin();
?>

<script src="<?php echo HtmlHelper::link("Assets/js/countries-select2.js") ?>"></script>

<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
                <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Profile</a></li>
                  <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                  <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a></li>
<!--                    <li>-->
<!--                        <ul class="nav nav-pills d-inline">-->
<!--                            <li>Edit</li>-->
<!--                        </ul>-->
<!--                    </li>-->
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="profile">
                      <?php require("Contents/PersonTemplate.php") ?>
                  </div>
                </div>
                <div class="tab-content">
                  <div class="tab-pane" id="activity">

                  </div>
                </div>
                <div class="tab-content">
                  <div class="tab-pane" id="timeline">
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
HtmlHelper::PageEndX();
