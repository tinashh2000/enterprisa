<?php
namespace Helpers;
class ErrorPage
{
    static function show($title = "", $message="") {

?>



        <div class="container-fluid v-align">
            <div class="d-flex align-items-center justify-content-center">
                <!-- jquery validation -->
                <div class="col-md-6 p-0">
                    <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! <?php echo ($title == "") ? "Error while loading page." : $title ?></h3>
                    <p>
                        <?php echo $message; ?><br>
                        You may <a href="Home">return to dashboard</a> or try using the search form.
                    </p>

                    <form class="search-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search">

                            <div class="input-group-append">
                                <button type="submit" name="submit" class="btn btn-warning"><i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.input-group -->
                    </form>
                </div>
                <!-- /.error-content -->
            </div>
            <!-- /.error-page -->




<?php
    }
}