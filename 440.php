<?php
use Api\AppDB;
use Api\Session\CSession;
use Ffw\Crypt\CCrypt8;
use Ffw\Status\CStatus;
use Helpers\HtmlHelper;
use Api\Mt;

require_once("Api/Bootstrap.php");
require_once(Mt::$appDir  . "/Scripts/HtmlHelper.php");
HtmlHelper::uses([HtmlHelper::_VALIDATE_]);
HtmlHelper::PageStartX(
["title"=>"Page Not Found", "description" => "Page Not Found", "path" =>
["404" => "Page Not Found"]], null, HtmlHelper::FLAG_NOMENU );
?>

<section class="content">
    <div class="container-fluid v-align">
        <div class="d-flex align-items-center justify-content-center">
            <!-- jquery validation -->
            <div class="col-md-6 p-0">
          <h3><i class="fa fa-exclamation-triangle text-warning"></i>440. Session expired.</h3>
          <p>
            Sorry, your session has expired. Kindly login or refresh your source page.
            Meanwhile, you may <a href="Home" class="btn-link">return to the home page</a> or try using the search form.
          </p>

          <form class="search-form">
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Search">

              <div class="input-group-append">
                <button type="submit" name="submit" class="btn btn-warning"><i class="fa fa-search"></i>
                </button>
              </div>
            </div>
            <!-- /.input-group -->
          </form>
        </div>
        <!-- /.error-content -->
      </div>
      <!-- /.error-page -->
    </section>
    <!-- /.content -->

      </div>

<?php
HtmlHelper::PageFooter(array("Assets/js/SignUp.js")); ?>
<?php HtmlHelper::PageEndX(); ?>

