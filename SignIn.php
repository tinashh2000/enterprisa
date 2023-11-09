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
    ["title" => "Sign In", "description" => "Sign In", "path" =>
        ["SignIn" => "Sign In"]], null, 0);
?>
    <div class="main-container shop-bg">
        <div class="container">
            <div class="row d-flex vh-100 align-items-center justify-content-center mb-5">
                <div class="col-lg-6">
                    <div class="account-form form-style p-20 mb-30 bg-fff box-shadow">
                        <div class="account-heading mb-25">
                            <h2>Login</h2>
                        </div>
                        <form method="post" id="loginForm">
                            <p class="mt-3">Don't have an account? <a href="SignUp<?= isset($_GET['sIRA']) ? "?sIRA={$_GET['sIRA']}":""?>">Create an Account here</a></p>

                            <input name="r" value="signIn" type="hidden"/>

                            <div class="mt-3">
                            <b>Username or email address <span>*</span></b>
                            <div class="form-group input-group">
                                <input class="form-control col-12" name="username" type="text"/>
                            </div>
                            <b>Password <span>*</span></b>
                            <div class="form-group input-group">
                                <input class="form-control col-12" name="password" type="password"/>
                            </div>
                            <div class="col-12 p-0 m-0 mt-3">
                                <input name="remember" type="checkbox"/> <b> Remember me </b>
                                <span class="pull-right"><a href="Forgot">Lost your password?</a></span>
                            </div>
                            <div class="login-button mt-3">
                                <div class="row">
                                    <div class="col-12">
                                        <a href="Home"><span class="pull-right"><div class="btn btn-warning">Cancel</div></span></a>
                                        <a href="#"><span class="pull-left"><div class="btn btn-primary" id="loginBtn"
                                                                                    class="d-flex">Login</div></span></a>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
HtmlHelper::PageFooter(["Assets/js/SignUp.js"]);
echo "<script>var retAddr = '" . Mt::getGetVarZ("sIRA", "Home") . "';</script>";
HtmlHelper::PageEndX();