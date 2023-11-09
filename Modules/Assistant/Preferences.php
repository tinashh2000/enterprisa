<?php

namespace Accounta;

use Api\CPrivilege;
use Modules\CModule;
use Helpers\HtmlHelper;
use Accounta\Accounts\CAccount;
use Api\Mt;
use Api\Authentication\CAuth;

require_once("Scripts/CheckLogin.php");

CAuth::proceedIfLoggedIn();

require_once("Scripts/HtmlHelper.php");

HtmlHelper::customMenu(CModule::getModuleDir("Accounta") . "/Ui/NavMenu.php");
HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Accounta");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_SWITCHERY_]);

HtmlHelper::usesA([[
    "Js" => ['Accounta/Js/ChartOfAccounts.js', 'Accounta/Js/NewAccount.js'],
    "Css" => ['Accounta/Css/Accounts.css']]]);

HtmlHelper::PageStartX(
    ["title" => "Preferences", "description" => "User's personal preferences", "path" =>
        ["Preferences" => "Preferences"]], null);

?>

    <div class="mt-main-body">
                    <div class="row">
                        <div class="col-12">

                            <div class="row d-flex align-items-center justify-content-center ">
                                <div class="card col-md-7 col-12 p-0">
                                    <div class="mDark-bg mModalTitle d-flex align-items-center justify-content-between pl-2 pr-2 p-1">
                                        <h4 class="modal-title">Preferences</h4>
                                    </div>
                                    <div class="row p-3">
                                        <div class="col-md-6 col-12 mt-2">
                                            <div class="row col-12">
                                                <h6><b>Theme</b></h6>
                                                <form id="themeSettings">
                                                    <div class="form-group pl-3 col-12">
                                                        <div class="row">
                                                            <div class="col-8"></div>
                                                            <div class="col-2"><small>Light</small></div>
                                                            <div class="col-2"><small>Dark</small></div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-8">Header</div>
                                                            <div class="col-4 d-flex justify-content-center"><input class="js-switchery" name="header"
                                                                                      type="checkbox"/>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-8">Navbar</div>
                                                            <div class="col-4 d-flex justify-content-center"><input class="js-switchery" name="navbar"
                                                                                      type="checkbox"/>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-8">Subitem</div>
                                                            <div class="col-4 d-flex justify-content-center"><input class="js-switchery"
                                                                                      name="subItem"
                                                                                      type="checkbox"/>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-8">Logo</div>
                                                            <div class="col-4 d-flex justify-content-center"><input class="js-switchery" name="logo"
                                                                                      type="checkbox"/>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-8">Layout Type</div>
                                                            <div class="col-4 d-flex justify-content-center"><input class="js-switchery"
                                                                                      name="layoutType"
                                                                                      type="checkbox"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12 mt-2">
                                            <b>Links</b><br>
                                            <div><a href="PostingAccounts" class="text-success"><i
                                                            class="fas fa-arrow-circle-right"></i> &nbsp; Posting
                                                    Accounts</a></div>
                                            <!--        <div><a href="PostingAccounts"><i class="fas fa-arrow-circle-right"></i> &nbsp; Posting Accounts</a></div>-->
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="btn btn-primary mr-3"><i class="fas fa-save"></i> &nbsp;Save
                                                Settings
                                            </div>
                                            <div class="btn btn-danger"><i class="fas fa-trash"></i> &nbsp;Discard
                                                Settings
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    </div>

    <script>
        $(function () {
            let themeSettings = document.getElementById("themeSettings");

            $("#themeSettings input[name='header'], #themeSettings input[name='navbar'], #themeSettings input[name='subItem'], #themeSettings input[name='logo'], #themeSettings input[name='layoutType']").on("change", function(e) {
                setCurrentThemeItem(e.currentTarget.name, e.currentTarget.checked ? "dark" : "light");
            });

            let t = getCurrentTheme();
            themeSettings.header.checked = t.header == "dark";
            themeSettings.navbar.checked = t.navbar == "dark";
            themeSettings.subItem.checked = t.subItem == "dark";
            themeSettings.logo.checked = t.logo == "dark";
            themeSettings.layoutType.checked = t.layoutType == "dark";

            if ($(".js-switchery")) {
                Array.prototype.slice.call(document.querySelectorAll(".js-switchery")).forEach(function (e) {
                    new Switchery(e, {color: "#269AB9", jackColor: '#fff', size: 'small'})
                });
            }
        });


    </script>
    <?php
HtmlHelper::PageFooter();
HtmlHelper::PageEndX();
