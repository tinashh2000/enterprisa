<?php

namespace Roles;


use Api\CSettings;
use Modules\CModule;
use Helpers\HtmlHelper;
use Api\Session\CSession;
use Api\CPrivilege;
use Api\CPriv;
use Admin\CAdmin;
use Api\Mt;
use Ffw\Status\CStatus;

require_once("Scripts/CheckLogin.php");

CPrivilege::isAdmin();

require_once("Scripts/HtmlHelper.php");

HtmlHelper::moduleBaseDir(Mt::$appRelDir . "/Admin");
HtmlHelper::uses([HtmlHelper::_BS_TABLE_, HtmlHelper::_SELECT2_, HtmlHelper::_VALIDATE_, HtmlHelper::_JQUERY_STEPS_]);
HtmlHelper::PageStartX(
    ["title" => "Settings", "description" => "Settings", "path" =>
        ["Settings" => "Settings"]], null);

CSettings::loadSession();
$settings = CSession::getArray("settings");
HtmlHelper::includeCSS("Admin/Css/Roles.css");
?>

    <div class="mt-main-body">
        <div class="row">
            <div class="col-12">
                <div class="row" id="settingsForms">
                    <div class="col-12 mb-5">
                        <form data-target="defaultModule">
                            <div class="">
                                <label>Default App<span class="pl-3"></span></label>
                                <div class="form-group input-group">
                                    <select id="defaultModule" name='defaultModule'
                                            class="select2" placeholder="Default Module">
                                        <option value="">None</option>
                                        <?php
                                        $defaultModule = CSession::getValue("settings", "DefaultModule");

                                        $modules = CModule::getModulesInfo();
                                        foreach ($modules as $k => $mdl) {
                                            echo "<option value='$k'" . ($defaultModule == $k ? " selected='select'" : "") . ">{$mdl['Name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <a class="link-btn updateBtn text-primary"><small>Update</small></a>
                        </form>
                    </div>

                    <div class="col-6 mb-5">
                        <form data-target="defaultLogo">
                            <div class="form-group form-float">
                                <label>Default Logo</label>
                                <div class="overflow-hidden">
                                    <img class="img-thumbnail border-0" name="pic"
                                         src='<?php echo Mt::$appRelDir; ?>/Assets/img/logo'
                                         id="defaultLogoImg"/>
                                    <input type="file" class="form-control" name="defaultLogo"
                                           id="defaultLogoFile" required>
                                    <a class="link-btn updateBtn text-primary"><small>Update</small></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-6 mb-5">
                        <form data-target="headerLogo">
                            <div class="form-group form-float">
                                <label>Header Logo</label>
                                <div class="overflow-hidden">
                                    <img class="img-thumbnail border-0" name="pic"
                                         src='<?php echo Mt::$appRelDir; ?>/Assets/img/hlogo'
                                         id="headerLogoImg"/>
                                    <input type="file" class="form-control" name="headerLogo"
                                           id="headerLogoFile" required>
                                    <a class="link-btn updateBtn text-primary"><small>Update</small></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-12 mb-5">
                        <form data-target="organisationInfo">
                            <input type="hidden" name="personId" value="0"/>
                            <input type="hidden" name="uid" value=""/>

                            <div class="">
                                <label>Organisation Name</label>
                                <div class="form-group input-group">
                                    <input type="text" id="organisationName" name='name' class="form-control"
                                           placeholder="Name" readonly>
                                </div>
                            </div>

                            <div class="">
                                <label>Organisation Email <span class="pl-3"></span></label>
                                <div class="form-group input-group">
                                    <input type="email" id="organisationEmail" name='email'
                                           class="form-control" placeholder="Email"
                                           value="<?php echo $settings['Email'] ?>" required>
                                </div>
                            </div>
                            <a class="link-btn updateBtn text-primary"><small>Update</small></a>
                        </form>
                    </div>

                    <div class="col-12 mb-5">
                        <form data-target="footerText">
                            <div class="">
                                <label>Footer Text<span class="pl-3"></span></label>
                                <div class="form-group input-group">
                                    <input type="text" id="footerText" name='footerText'
                                           class="form-control" placeholder="Footer Text"
                                           value="<?php echo $settings['FooterText'] ?>" required>
                                </div>
                            </div>
                            <a class="link-btn updateBtn text-primary"><small>Update</small></a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
HtmlHelper::PageFooter(array("Assets/js/countries-select2.js", "Assets/js/Person.js", "Admin/Js/Settings.js"));
?>
    <script>isAdmin = true;</script>
    <?php HtmlHelper::PageEndX();

