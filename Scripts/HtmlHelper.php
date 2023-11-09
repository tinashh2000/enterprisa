<?php

namespace Helpers;

use Api\Authentication\CAuth;
use Api\CPerson;
use Api\CPrivilege;
use Api\Mt;
use Api\Session\CSession;
use Modules\CModule;
use Helpers\CMenu;

require_once("CMenu.php");

class HtmlHelper
{
const FLAG_NOMENU = 1;
const FLAG_NOBUNDLE = 32;
const FLAG_NOCOMMON = 64;
const FLAG_CUSTOMMENU = 128;
const FLAG_NOEXTRA = self::FLAG_NOBUNDLE | self::FLAG_NOCOMMON;

const _BS_TABLE_ = [
    "Js" => ["Assets/plugins/jquery.resizableColumns/jquery.resizableColumns.min.js",
        "Assets/plugins/bootstrap-table/bootstrap-table.min.js",
        "Assets/plugins/bootstrap-table/extensions/resizable/bootstrap-table-resizable.min.js",
        "Assets/plugins/bootstrap-table/extensions/editable/bootstrap-table-editable.js"],
    "Css" => [
        "Assets/plugins/jquery.resizableColumns/jquery.resizableColumns.css",
        "Assets/plugins/bootstrap-table/bootstrap-table.min.css",
        "Assets/plugins/bootstrap-table/extensions/editable/bootstrap-editable.css",
    ]];

const _SELECT2_ = [
    "Js" => "Assets/plugins/select2/js/select2.full.js",
    "Css" => ["Assets/plugins/select2/css/select2.min.css"]];

const _SWITCHERY_ = [
    "Js" => "Assets/bundle/js/switchery.min.js",
    "Css" => ["Assets/bundle/css/switchery.min.css"]];

const _VALIDATE_ = ["Js" => ["Assets/plugins/jquery-validation/jquery.validate.js", "Assets/plugins/jquery-validation/additional-methods.min.js", "Assets/js/Validate.js"]];

const _MAGICDIV_ = [
    "Js" => "Assets/js/magicDiv.js",
    "Css" => "Assets/css/magicDiv.css",
];

const _TIMELINE_ = [
    "Js" => "Assets/js/timeline.js",
    "Css" => "Assets/css/timeline.css",
];

const _JQUERY_STEPS_ = [
    "Js" => ["Assets/plugins/jquery-steps/jquery.steps.min.js",
        "Assets/plugins/jquery-steps/form-wizard.js"],
    "Css" => "Assets/plugins/jquery-steps/jquery.steps.css",
];

const _DATETIMEPICKER_ = [
    "Js" => "Assets/plugins/daterangepicker/daterangepicker.js",
    "Css" => "Assets/plugins/daterangepicker/daterangepicker.css",
];

const _SORTABLE_ = [
    "Js" => "Assets/bundle/js/sortable.js",
];

const _SUMMERNOTE_ = [
    "Js" => "Assets/plugins/summernote/summernote-bs4.js",
    "Css" => "Assets/plugins/summernote/summernote-bs4.css",
];

const _TIMETABLE_ = [
    "Js" => "Assets/plugins/timetable.js/js/timetable.js",
    "Css" => ["Assets/plugins/timetable.js/css/timetablejs.css", "Assets/plugins/timetable.js/css/demo.css"],
];

static $customMenu = null;

static protected $menu;
static protected $settingsMenu;
static protected $appsMenu;
static protected $currentMenu;
static protected $profileMenu;
static protected $moduleBaseDir = null;

static protected $jsFiles = array();
static protected $cssFiles = array();
static protected $usedJsFiles = array();
static protected $usedCssFiles = array();

static protected $iflags = 0;

static protected $commonCss = array(
    "Assets/css/Theme-light.css",
    "Assets/css/Enterprisa.css",
    "Assets/css/Branding.css",
    "Assets/css/Messages.css"
);

static protected $bundleCss = array(
    "Assets/fonts/quicksand/quicksand.css",
    "Assets/fonts/sourcesanspro/sourcesanspro.css",
    "Assets/fonts/Lato/Lato.css",
    "Assets/fonts/fontawesome/css/all.min.css",
    "Assets/plugins/toastr/toastr.min.css",
    "Assets/css/sidebars.css",
    "Assets/css/Theme.css",
    "Assets/plugins/bootstrap/css/bootstrap.min.css",
    "Assets/plugins/waves/waves.min.css",
    "Assets/plugins/toastr/toastr.min.css",
    "Assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css",
    "Assets/fonts/feather/feather.css",
    "Assets/fonts/bootstrap-icons/bootstrap-icons.css",
);

static protected $commonJs = array(
    "Assets/js/App.js",
    "Assets/js/Auth.js",
    "Assets/js/Enterprisa.js");

static protected $commonJsLogged = array(
    "Assets/js/Messages.js",
    "Assets/js/Async.js"
);

static protected $earlyJs = array(
    "Assets/plugins/jquery/jquery.min.js",
);

static protected $bundleJs = array(
    "Assets/plugins/jquery-ui/jquery-ui.min.js",
//        "Assets/plugins/popper/popper.min.js",
    "Assets/plugins/bootstrap/js/bootstrap.bundle.min.js",
    "Assets/plugins/slimscroll/jquery.slimscroll.min.js",
    "Assets/plugins/waves/waves.min.js",
    "Assets/plugins/toastr/toastr.min.js",
    "Assets/plugins/moment/moment.min.js",
    "Assets/plugins/overlayScrollbars/js/OverlayScrollbars.min.js",

);

const __BUNDLE__ = "ENTERPRISA_BUNDLE";
const __COMMON__ = "ENTERPRISA_COMMON";

static function createProfileMenu($title, $link = "", $icon = "")
{
    if (self::$profileMenu === null) {
        self::$profileMenu = new CMenu($title, $link, $icon);
    }
    return self::$profileMenu;
//        return self::$profileMenu->addMenu($title);
}

static function getProfileMenu()
{
    return self::$profileMenu;
}

static function createMenu($title, $position = null, $link = null, $icon = "feather icon-home")
{
    if (self::$currentMenu == self::$menu) {
        return self::$currentMenu->addMenu($title, $position, $link, $icon);
    } else
        return self::$currentMenu->addMenu($title, $position, $link, $icon);
}

static function getSettingsMenu()
{
    if (self::$settingsMenu === null && CPrivilege::isAdmin()) {
        self::$settingsMenu = new CMenu("Admin", "", "fas fa-cogs");
    }
    return self::$settingsMenu;
}

static function customMenu($mnu)
{
    if (file_exists($mnu)) {
        self::$customMenu = $mnu;
    } else {
        die("Not found $mnu");
    }
}

static function moduleBaseDir($d)
{
    self::$moduleBaseDir = $d;
}

static function getProfilePic($u)
{
    return self::link("users/$u/pics/profile");
}

static function getCustomMenu()
{
    return self::$customMenu;
}

static function getLeftMenu()
{
    if (self::$menu == null)
        self::initializeMenu();
    return self::$menu;
}

static function getAppsMenu()
{
    return self::$appsMenu;
}

static function getPrivilegesList()
{
    $pl = CPrivilege::getPrivilegesList();
    foreach ($pl as $module) {
        foreach ($module as $k => $i) {
            echo "<optgroup label='$k'>\n";
            foreach ($i as $kk => $ii) {
                foreach ($ii as $kkk => $iii) {
                    echo "<option value='$iii'>$kk.$kkk</option>\n";
                }
            }
            echo "</optgroup>\n";
        }
    }
}

static function initializeMenu()
{
    global $redirCurrentModule;

    self::$appsMenu = new CMenu("Apps");
    $mnux = self::$appsMenu->addMenu("Apps", null, "", 'fas fa-mobile');

    self::$menu = new CMenu();
    if (class_exists("Api\Install\CInstall", false))
        return;

    $defaultModule = CSession::getValue("settings", "DefaultModule");

    try {
        if (!$hf = fopen(__DIR__ . "/../Modules/enterprisa", "r")) throw new \Exception("File reading error");

        if ($defaultModule != "") {
            $n = basename(Mt::removePrefix(CModule::getModuleDir($defaultModule), Mt::$appDir));
        } else if (isset($redirCurrentModule)) {
            $n = basename($redirCurrentModule);
        } else {
            $n = "";
        }

        while (!feof($hf)) {
            $m = trim(fgets($hf));
            $mDir = __DIR__ . "/../Modules/$m";
            $mnuFile = $mDir . "/Ui/LeftMenu.php";

            if (is_file($mnuFile)) {
//                    if ($m == $n)
//                        self::$currentMenu = self::$menu;
//                    else
                self::$currentMenu = $mnux;

                require_once($mnuFile);
            }
        }
        $mnux->addMenuX(self::$profileMenu, 0);
        $mnux->addMenuX(self::getSettingsMenu());


        fclose($hf);
    } catch (\Exception $e) {

    }
}

static function __setup()
{
}

static function addJsFile($file)
{
    if (is_array($file)) {
        foreach ($file as $jsFile)
            self::addJsFile($jsFile);
    } else if (is_string($file) && !in_array($file, self::$jsFiles))
        array_push(self::$jsFiles, $file);
}

static function addCssFile($file)
{
    if (is_array($file)) {
        foreach ($file as $cssFile)
            self::addCssFile($cssFile);
    } else if (is_string($file) && !in_array($file, self::$cssFiles))
        array_push(self::$cssFiles, $file);
}


static function includeCSS($path)
{
    self::CssInclude($path);
}

static function includeJS($path)
{
    self::JsInclude($path);
}

static function JsInclude($libs)
{

    if ($libs == null) return;
    if (is_string($libs)) {
        if (in_array($libs, self::$usedJsFiles)) return;
        array_push(self::$usedJsFiles, $libs);
        $abs = substr($libs, 0, 8) == "https://" || substr($libs, 0, 7) == "http://";
        echo "<script src='" . ($abs ? "" : Mt::$appRelDir . "/") . "{$libs}'></script>\n";
        return;
    }

    foreach ($libs as $lib) {
        if (is_array($lib)) {
            self::JsInclude($lib);
            continue;
        } else {
            if (in_array($lib, self::$usedJsFiles)) continue;
            array_push(self::$usedJsFiles, $lib);
            $abs = substr($lib, 0, 8) == "https://" || substr($lib, 0, 7) == "http://";
            echo "<script src='" . ($abs ? "" : Mt::$appRelDir . "/") . "{$lib}'></script>\n";
        }
    }
}

static function CssInclude($libs)
{
    if ($libs == null) return;
    if (is_string($libs)) {
        if (in_array($libs, self::$usedCssFiles)) return;
        array_push(self::$usedCssFiles, $libs);
        $abs = substr($libs, 0, 8) == "https://" || substr($libs, 0, 7) == "http://";
        echo "<link rel='stylesheet' href='" . ($abs ? "" : Mt::$appRelDir . "/") . "{$libs}'>\n";
        return;
    }
    foreach ($libs as $lib) {
        if (is_array($lib)) {
            self::CssInclude($lib);
            continue;
        } else {
            if (in_array($lib, self::$usedCssFiles)) continue;
            array_push(self::$usedCssFiles, $lib);
            $abs = substr($lib, 0, 8) == "https://" || substr($lib, 0, 7) == "http://";
            echo "<link rel='stylesheet' href='" . ($abs ? "" : Mt::$appRelDir . "/") . "{$lib}'>\n";
        }
    }
}

static function uses($items)
{
    foreach ($items as $item) {
        if (is_array($item) && isset($item['Css'])) self::addCssFile($item['Css']); //array_push(self::$cssFiles, $item["Css"]);
        if (is_array($item) && isset($item['Js'])) self::addJsFile($item['Js']); //array_push(self::$jsFiles, $item["Js"]);
    }
}

static function usesA($items)
{
    foreach ($items as $item) {
        if (is_array($item) && isset($item['Css'])) self::addCssFile($item['Css']); //array_push(self::$cssFiles, $item["Css"]);
        if (is_array($item) && isset($item['Js'])) self::addJsFile($item['Js']); //array_push(self::$jsFiles, $item["Js"]);
    }
}

static function PageStartX($info, $cssFiles = array(), $flags = 0)
{
$path = isset($info['path']) ? $info['path'] : null;
self::$iflags = $flags;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <title>Harare International University - <?php echo $info['title'] ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="icon" type="image/x-icon" href="Assets/favicon.ico">
    <link rel="manifest" href="<?php echo Mt::$appRelDir ?>/manifest.json"><?php
    if (($flags & self::FLAG_NOBUNDLE) == 0) {
        self::CssInclude(self::$bundleCss);
        self::JsInclude(self::$earlyJs);
    }
    self::CssInclude(self::$cssFiles);
    self::CssInclude($cssFiles);
    self::$cssFiles = array();
    if (($flags & self::FLAG_NOCOMMON) == 0) self::CssInclude(self::$commonCss);
    echo "<script>
                var homeLink = '" . Mt::$appRelDir . "';
                var bDir = '" . ((self::$moduleBaseDir !== null) ? self::$moduleBaseDir . "/'" : "'") . "
            </script>";
    if (CAuth::isLoggedIn()) //Lets see if it works && (self::$iflags & self::FLAG_NOMENU) == 0)
        self::JsInclude("Assets/js/Stats.js");
    ?>
</head>
<body class="loading <?= (($flags & self::FLAG_NOMENU) == 0) ? "" : "nomenu" ?>">
<div class="loader-bg">
    <div class="v-align-bottom">

        <div class="d-flex vh-100 align-items-center justify-content-center mb-5">
            <!--                    <img src="--><?php //echo Mt::$appRelDir
            ?><!--/Assets/img/logo-small.png" />-->
            <!--                    <div class="loader-bar"></div>-->
            <div class="loading-screen_logo mb-5">
                <img src='<?php echo Mt::$appRelDir ?>/Assets/img/hlogo' height="60rem"/>
                <div class="loading-screen_progress-wrapper">
                    <div class="loading-screen_progress-status" data-reactid=".0.1.1.0"></div>
                    <div class="loading-screen_progress-bg">
                        <div class="loading-screen_progress loading-screen_progress-linear" style="width:100%"
                             data-reactid=".0.1.1.1.0"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-container">
    <?php
    if (($flags & self::FLAG_NOMENU) == 0) {
        require(Mt::$appDir . "/Widgets/RightSidebar.php");
    }
    ?>
    <div class="mt-main-container">

        <?php
        if (($flags & self::FLAG_NOMENU) == 0) {
            require(Mt::$appDir . "/Widgets/VLeftSidebar.php");
        }
        ?>
        <div class="mt-sidebar-backdrop"></div>
        <div class="mt-main-contents">
            <?php
            if (($flags & self::FLAG_NOMENU) == 0) {
                require(Mt::$appDir . "/Widgets/Navbar.php");
            }
            $bc_count = $path == null ? 0 : count($path);
            if (($flags & self::FLAG_NOMENU) == 0 && is_array($path) && $bc_count > 0) { ?>
                <div class="mt-page-header row px-2 py-3 mx-0 mb-2 card rounded">
                    <div class="row">
                        <div class="col-12 col-sm-6 d-flex align-items-center justify-content-start">
                            <div class="row">
                                <div class="d-flex">
                                    <div class="mr-3 rounded border-1 togoo" style="height:30px; max-width:30px"><i
                                                class="fa-2x fas fa-home text-primary"></i></div>
                                    <div class="col lh-1">
                                        <h1 class="h6 mb-0 lh-1"><?= $info['title'] ?></h1>
                                        <small><?= $info['description'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 d-flex align-items-center d-none d-sm-flex justify-content-sm-end">
                            <div class="page-header-breadcrumb">
                                <ul class=" breadcrumb breadcrumb-title m-0 small">
                                    <li class="breadcrumb-item">
                                        <a href="<?= HtmlHelper::mlink("Home") ?>"><i class="fas fa-home"></i></a>
                                    </li>
                                    <?php
                                    $lastKey = "";
                                    $lastValue = "";
                                    foreach ($path as $key => $value) {
                                        if ($lastKey != "")
                                            echo "<li class='breadcrumb-item'><a href='$lastKey'>$lastValue</a></li>";

                                        $lastKey = $key != "" ? Mt::$appRelDir . "/$key" : "";
                                        $lastValue = $value;
                                    }

                                    if ($lastKey != "")
                                        echo "<li class='breadcrumb-item active'><a href=''>$lastValue</a></li>";
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } else {

            }
            }

            static function PageFooter($jsFiles = array(), $flags = 0)
            {
            ?>
        </div>
    </div>
</div>
<?php
if ((intval($flags) & self::FLAG_NOBUNDLE) == 0)
    self::$jsFiles = array_merge(self::$bundleJs, self::$jsFiles);    //self::JsInclude(self::$bundleJs);

self::addJsFile($jsFiles);
//    self::JsInclude(self::$jsFiles);

if ((intval($flags) & self::FLAG_NOCOMMON) == 0) {
    self::addJsFile(self::$commonJs);

    if (CAuth::isLoggedIn() && (self::$iflags & self::FLAG_NOMENU) == 0)
        self::addJsFile(self::$commonJsLogged);
}

if ((self::$iflags & self::FLAG_NOMENU) == 0)
    require_once(__DIR__ . "/../Widgets/Footer.php");
}

static function PageEndX()
{

self::JsInclude(self::$jsFiles);
self::$jsFiles = array();
?>
</body>
</html>
<?php
}

static function link($url)
{
    return Mt::$appRelDir . "/$url";
}

static function mlink($url)
{
    return (self::$moduleBaseDir ?? Mt::$appRelDir) . "/$url";
}

static function newModal($name, $path, $size = 'modal-lg')
{
    $fn = "$path/New{$name}Content.php";
    $pref = "New";
    if (!file_exists($fn)) {
        if (substr($name, 0, 4) == "Show") {
            $pref = "Show";
            $name = substr($name, 4);
        } else if (substr($name, 0, 4) == "Open") {
            $pref = "Open";
            $name = substr($name, 4);
        } else {
            die("Unknown link : $name, $path");
        }

        $fn = "$path/$pref{$name}Content.php";
    }

    ?>
    <div class="modal fade MSpecial-Modal p-0" id="<?php echo strtolower($pref) . $name ?>Modal">
        <div class="modal-dialog <?php echo $size; ?> modal-dialog-centered">
            <div class="modal-content p-0 m-0">
                <?php
                require_once($fn);
                ?>
            </div>
        </div>
    </div>

    <?php
}

static function showModalX($name, $path, $size = 'modal-lg', $options = null)
{
    return self::newModalX($name, $path, $size, $options);
}

static function newViewX($name, $path, $size = 'modal-lg', $options = null)
{
    if ($options == null) $options = [];
    $options['noModal'] = true;
    if (!isset($options['title'])) $options['title'] = null;

    return self::newModalX($name, $path, $size, $options);
}

static function newModalX($name, $path, $size = 'modal-lg', $options = null)
{
    $fn = "$path/New{$name}Content.php";
    $pref = "New";
    if (!file_exists($fn)) {
        if (substr($name, 0, 4) == "Show") {
            $pref = "Show";
            $name = substr($name, 4);
        } else if (substr($name, 0, 4) == "Open") {
            $pref = "Open";
            $name = substr($name, 4);
        } else {
            $pref = "";
            if (!file_exists("$path/$pref{$name}Content.php"))
                die("Unknown link : $name, $path");
        }

        $fn = "$path/$pref{$name}Content.php";

    }
    $options['prefix'] = $pref;

    if (isset($options["noModal"])) {
        $modalStr = "";
        $modalDlgStr = "";

    } else {
        $modalStr = "modal fade MSpecial-Modal cls";
        $modalDlgStr = "modal-dialog modal-dialog-centered";

    }
    ?>
    <div class="<?= $modalStr ?> p-0" id="<?php echo strtolower($pref) . $name ?>Modal">
        <div class="<?= $modalDlgStr ?>  <?php echo $size; ?>">
            <div class="modal-content p-0 m-0" id="<?php echo strtolower($pref) . $name ?>Content">
                <div class='modal-busy-content col-12 p-0 m-0'>
                    <?php

                    if (!array_key_exists('title', $options) || !is_null($options['title'])) {
                        self::modalTitle("Please Wait", ["title" => "Please Wait", "prefix" => "", "buttons" => []]);
                    } ?>
                    <div class="modal-loader col-12 pt-5 pb-5">
                        <div class="col-12 d-flex justify-content-center align-self-center p-5">
                            <div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>
                        </div>
                    </div>
                </div>

                <div class='modal-container col-12 p-0 m-0' style="">

                    <?php

                    if (!array_key_exists('title', $options) || !is_null($options['title'])) {
                        self::modalTitle($name, $options);
                    }
                    ?>

                    <div class='m-content overlayScrollContainer p-3'>
                        <?php
                        require_once($fn);
                        ?>
                    </div>
                    <?php

                    self::modalButtons($name, $options);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
}

static function modalTitle($name, $options)
{
    $buttons = isset($options['buttons']) ? $options['buttons'] : null;

    if (!isset($options['prefix'])) \get_call_stack();

    ?>
    <div class="mModalTitle d-flex align-items-center justify-content-between pl-2 pr-2 p-1">

        <span id="<?php echo strtolower($options['prefix']) . $name ?>Title"><?php echo isset($options['title']) ? $options['title'] : "New " . $name ?></span>
        <?php
        if ($buttons == null || in_array("top", $buttons) === TRUE) {
            ?>

            <span>
                <a href="#">
                <button type="button"
                        class="link-btn d-none text-white mr-3 <?php echo strtolower($options['prefix']) . $name ?>DeleteBtn deleteBtn"
                        onclick="doDelete<?php echo $name ?>()"
                ><small><i class='fas fa-trash text-warning'></i></small></button>
                </a>

                <a href="#">
                    <button type="submit"
                            onclick="submitForm('#<?php echo strtolower($options['prefix']) . $name ?>Form')"
                            class="link-btn text-white mr-3 createBtn new<?php echo $name ?>CreateBtn">
                        <small><i class='fas fa-check text-primary'></i></small></button>
                </a>
                <a href="#">
                    <button type="button"
                            class="link-btn d-none text-white p-0 mr-1 cancelBtn <?php echo strtolower($options['prefix']) . $name ?>CancelBtn cancelBtn"
                            data-bs-dismiss="modal">
                        <small><i class='fas fa-ban text-danger'></i></small>
                    </button>
                </a>
            </span>
            <?php
        }
        ?>
    </div>

    <div class="row col-12 mModalLogo">
        <div class="col-auto modal-logo mt-2 mb-2 p-0 pr-2 pl-1"><a href="home"><img
                        src='<?php
                        echo HtmlHelper::link("Assets/img/hlogo"); ?>'
                        height='20em'/></a></div>
    </div>
    <?php
}

static function modalButtons($name, $options = null)
{
    $buttons = isset($options['buttons']) ? $options['buttons'] : null;

    if ($buttons == null || array_search("bottom", $buttons) === TRUE) {
        ?>

        <div class="row col-12 m-0">
            <div class="col-4">
                <a href="#">
                    <button type="button"
                            class="link-btn d-none text-warning mb-2 mr-3 new<?php echo $name ?>DeleteBtn deleteBtn"
                            id="new<?php echo $name ?>DeleteBtn"
                            onclick="doDelete<?php echo $name ?>()"><i
                                class='fas fa-trash'></i>&nbsp;&nbsp;<span>Delete</span>
                    </button>
                </a>
            </div>
            <div class="col-8 d-flex justify-content-end">
                <a href="#">
                    <button type="submit"
                            class="link-btn text-success mb-2 mr-3 createBtn new<?php echo $name ?>CreateBtn"
                            id="new<?php echo $name ?>CreateBtn"
                            onclick="submitForm('#new<?php echo $name ?>Form')">
                        <i
                                class='fas fa-check'></i>&nbsp;&nbsp;<span>Create</span>
                    </button>
                </a>
                <a href="#">
                    <button type="button"
                            class="link-btn d-none text-danger mb-2 mr-1 cancelBtn new<?php echo $name ?>CancelBtn"
                            id="new<?php echo $name ?>CancelBtn"
                            data-bs-dismiss="modal"><i
                                class='fas fa-ban'></i>&nbsp;&nbsp;<span>Cancel</span>
                    </button>
                </a>
            </div>
        </div>
        <?php
    }
}
}
HtmlHelper::__setup();
