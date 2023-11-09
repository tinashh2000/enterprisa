<?php

namespace Api;

use Api\AppDB;
use Api\Mt;
use Api\Session\CSession;
use Ffw\Crypt\CCrypt8;
use Ffw\Status\CStatus;
use Helpers\HtmlHelper;

require_once("Api/Bootstrap.php");
require_once("Scripts/HtmlHelper.php");

HtmlHelper::uses([HtmlHelper::_VALIDATE_, HtmlHelper::_SELECT2_]);
HtmlHelper::PageStartX(
    ["title" => "Forgot Password", "description" => "Forgot Password", "path" =>
        ["Forgot" => "Forgot Password"]], null, HtmlHelper::FLAG_NOMENU);
?>
    <div class="main-container shop-bg">
        <div class="container">
            <div class="row d-flex vh-100 align-items-center justify-content-center mb-5">

                <div class="col-lg-6">
                    <div class="account-form form-style p-20 mb-30 bg-fff box-shadow">
                        <div class="account-heading mb-25">
                            <h2>Register</h2>
                        </div>
                        <form action="#">
                            <b>Email address  <span>*</span></b>
                            <div class="form-group input-group">
                                <input class="form-control col-12" name="username" type="text"/>
                            </div>
                        </form>
                        <div class="login-button mt-3">
                            <div class="row">
                                <div class="col-12">
                                    <a href="Home"><span class="pull-right"><button>Cancel</button></span></a>
                                    <a href="#"><span class="pull-left"><button id="resetPasswordBtn"
                                                                                class="d-flex">Reset password</button></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
HtmlHelper::PageFooter(array("Assets/js/SignUp.js"));
HtmlHelper::PageEndX();