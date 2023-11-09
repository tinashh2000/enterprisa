<?php
use Api\Mt;
use Api\Session\CSession;
use Ffw\Status\CStatus;
?>
<!-- Main content -->
<section class="content">
    <div class="container-fluid v-align">
        <div class="row  d-flex align-self-center justify-content-center">

            <div class='card col-md-6 col-sm-10 p-0 m-0'>
                <h3 class="col-12 bg-primary pl-3 p-2">Installation Step <?php echo $setupPhase;?></h3>
                <div class="p-3">

                    <p>

                    </p>
                    <div class="row col-12 m-0 p-0">
                        <div class="col-6 d-flex justify-content-start m-0 p-0"><a href="Install" id="install-proceed" class="btn btn-primary pl-2 pr-2">Next &nbsp;<i class=" fas fa-arrow-circle-right"></i></a></div>
                        <div class="col-6 d-flex justify-content-end m-0 p-0"><a href="/<?php echo Mt::$appRel ?>" class="btn btn-warning" id="install-cancel">Exit &nbsp;<i class=" fas fa-times-circle"></i></a></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
