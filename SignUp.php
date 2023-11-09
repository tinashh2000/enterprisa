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

HtmlHelper::uses([HtmlHelper::_VALIDATE_, HtmlHelper::_SELECT2_, HtmlHelper::_DATETIMEPICKER_]);
HtmlHelper::PageStartX(
    ["title" => "Sign Up", "description" => "Sign Up", "path" =>
        ["SignUp" => "Sign Up"]], null, HtmlHelper::FLAG_NOMENU);
?>
    <div class="main-container shop-bg">
        <div class="container">
            <div class="row d-flex vh-100 align-items-center justify-content-center mb-5">

                <div class="col-lg-6">
                    <div class="account-form form-style p-20 mb-30 bg-fff box-shadow">
                        <div class="account-heading mb-25">
                            <h2>Register</h2>
                        </div>
                        <form method="post" onsubmit="return false" id="signUpForm">
                            <p>Already have an account? <a href="SignIn<?= isset($_GET['sIRA']) ? "?sIRA={$_GET['sIRA']}":""?>">Login here</a></p>
                            <div class="row">
                                <div class="col-12">

                                    <label>Full Name</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='fullname' class="form-control"
                                               placeholder="Full Name">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">

                                    <label>Username</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='username' class="form-control"
                                               placeholder="Username" value="Administrator">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">


                                    <label>Email</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='email' class="form-control"
                                               placeholder="Email Address">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">


                                    <label>Phone</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="text" name='phone' class="form-control"
                                               placeholder="Phone number">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">

                                    <label>Address</label>
                                    <div class="form-group input-group mb-3">
                                        <textarea name='address' class="form-control"
                                                  placeholder="Address"></textarea>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-12">
                                    <label>Password</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="password" name='password' class="form-control"
                                               placeholder="Password">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label>Confirm Password</label>
                                    <div class="form-group input-group mb-3">
                                        <input type="password" name='cpassword' class="form-control"
                                               placeholder="Confirm Password">
                                    </div>
                                </div>

                            </div>

                            <div class="login-button mt-3">
                                <div class="row">
                                    <div class="col-12">
                                        <a href="Home"><span class="pull-right"><button>Cancel</button></span></a>
                                        <a href="#"><span class="pull-left"><button type="submit" id="signUpBtn"
                                                                                    class="d-flex">Register</button></span></a>
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
HtmlHelper::PageFooter(array("Assets/js/SignUp.js"));
HtmlHelper::PageEndX();

