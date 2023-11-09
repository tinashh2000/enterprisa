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
    ["title"=>"Bad Request", "description" => "Bad Request", "path" =>
        ["Login" => "Bad Request"]], null, HtmlHelper::FLAG_NOMENU );
?>


<section class="content">
    <div class="container-fluid v-align">
        <div class="d-flex align-items-center justify-content-center">
            <!-- jquery validation -->
            <div class="col-md-6 p-0">
          <h3><i class="fa fa-exclamation-triangle text-warning"></i> Oops! Bad Request.</h3>

          <p>
            We could not find the page you were looking for.
            Meanwhile, you may <a href="Home">return to the home page</a> or try using the search form.
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
    </div>
      <!-- /.error-page -->
    </section>
    <!-- /.content -->


        <?php

        HtmlHelper::PageFooter(array("Assets/js/SignUp.js")); ?>
        <?php HtmlHelper::PageEndX(); ?>
