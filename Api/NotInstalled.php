<?php
use Api\AppDB;
use Api\Session\CSession;
use Ffw\Crypt\CCrypt8;
use Ffw\Status\CStatus;
use Helpers\HtmlHelper;
use Api\Mt;

require_once(__DIR__ . "/Bootstrap.php");
require_once(__DIR__ . "/../Scripts/HtmlHelper.php");
HtmlHelper::uses([HtmlHelper::_VALIDATE_]);
HtmlHelper::PageStartX(
    ["title"=>"Page Not Found", "description" => "Page Not Found", "path" =>
        ["404" => "Page Not Found"]], null, HtmlHelper::FLAG_NOMENU );
?>

<section class="content">
    <div class="container-fluid v-align">
        <div class="d-flex align-items-center justify-content-center">
            <!-- jquery validation -->

            <div class="col-xl-8 col-md-12 mt-5">
                <div class="row mt-5">

                    <div class="col-md-12 p-0">
                        <h3><i class="fas fa-exclamation-triangle text-warning"></i> System not found.</h3>
                        <p>
                            Essential files required to run this system could not be found. Please contact us for reinstallation and possibly data recovery. In the meantime please shutdown this system to avoid data loss.

                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-8 col-lg-8 col-md-12 col-12">
                        <div class="contuct mb-50 bg-fff box-shadow p-20">
                            <div class="contuct-title">
                                <h2>Contact Us</h2>
                            </div>
                            <div class="contuct-form form-style">
                                <form id="contactForm" onsubmit="return submitContact()" action="SendMessage.php">
                                    <input type='hidden' name="r" value="" />
                                    <input type="hidden" name="details" value="" />
                                    <input type="hidden" name="key" value="" />
                                    <span>Your Name (required)</span>
                                    <input type="text" class="form-control" name="name" required />
                                    <span>Your Email (required)</span>
                                    <input type="email" class="form-control" name="email" />
                                    <span>Subject</span>
                                    <input type="text" class="form-control" name="subject" />
                                    <span>Your Message</span>
                                    <textarea cols="30" class="form-control" rows="10" name="message"></textarea>
                                    <button class="btn btn-primary mt-1">Send</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-12 col-12">
                        <div class="contuct-detail mb-50 p-20 bg-fff box-shadow">
                            <div class="contuct-title">
                                <h2>Contact detail</h2>
                            </div>
                            <p>Feel free to contact us about anything. We will be pleased to hear from you</p>
                            <div class="same">
                                <h5>OFFICE ADDRESS</h5>
                                <p></p>
                            </div>
                            <div class="same">
                                <h5>EMAIL</h5>
                                <p><a href="mailto:tinashh2000@gmail.com">tinashh2000@gmail.com</a></p>
                            </div>
                            <div class="same">
                                <h5>PHONE NUMBER</h5>
                                <p><a href="tel:">+263 736 931 931</a></p>
                            </div>
                            <div class="same">
                                <h5>TIME HOURS</h5>
                                <p>Monday to Friday: 10am to 7pm</p>
                                <p>Saturday: 10am to 4pm</p>
                                <p>Sunday: 12am to 4pm</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- /.error-page -->
</section>
<!-- /.content -->

</div>

<?php
HtmlHelper::PageFooter(array("Assets/js/SignUp.js")); ?>
<?php HtmlHelper::PageEndX(); ?>

