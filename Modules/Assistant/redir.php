<?php
namespace Assistant;
use Api\Users\CUser;
use Api\Mt;
use Api\CPerson;
use Ffw\Status\CStatus;
use Api\Users\CurrentUser;
$contentTypes = array("image/jpeg", "image/png", "image/svg+xml", "image/tiff", "image/gif", "text/javascript", "text/css");
$aLog = "";
if (isset($_GET['redirPageId'])) {
    $path = explode("/", $_GET['redirPageId']);
    $c = count($path);

    if ($c == 3 && $path[2] == "pic") {
        $path[2] = "pics";
        $path[3] = "profile";
        $c = 3;
    }

    switch ($path[0]) {
        case "users":
            if ($c == 2) {
                $_GET = array("u" => $path[1]);
                require_once(__DIR__ . "/Profile.php");
                die();
            } else if ($path[2] == "pics") {

                if ($user = CUser::get($path[1])) {
                    reDirShowPic(CPerson::$defaultPath . "/pics/{$user['uid']}_pp.jpg", Mt::$appDir . "/Assets/img/unknownuser.jpg");
                    die();
                } else {
                    die();
                }
                break;
            }
            break;

        case "people":
            if ($c == 2) {
                $_GET = array("p" => $path[1]);
                require_once(__DIR__ . "/ViewPerson.php");
                die();
            } else if ($path[2] == "pics") {
                if ($path[1] == "me") {
                    $path[1] = CurrentUser::getUid();
                    $person = ["name"=>CurrentUser::getFullname()];
                }
                else if ($path[1] == "System") {
                    reDirShowPic(Mt::$appDir . "/Assets/img/tv.svg", Mt::$appDir . "/Assets/img/unknownuser.jpg");
                } else if ($person = CPerson::get($path[1])) {
                    $path[1] = $person['uid'];
                } else if ($person = CUser::get($path[1])) {
                    $path[1] = $person['uid'];
                }

                if (!is_file(CPerson::$defaultPath . "/pics/{$path[1]}_pp.jpg")) {
                    if (!isset($person) || !$person) {
                        reDirShowPic(Mt::$appDir . "/Assets/img/question-circle.svg", Mt::$appDir . "/Assets/img/unknownuser.jpg");
                    } else {
                        $names = explode(" ",$person['name']);
                        $cn = count($names);
                         if ($cn < 2) {
                             $initials = substr(trim($names[0]), 0, 2);
                         } else
                             $initials = substr(trim($names[0]), 0, 1) . substr(trim($names[$cn-1]), 0, 1);
                        header("Content-type: image/svg+xml", true);
                        echo '<?xml version="1.0" encoding="utf-8"?'.'>
<!-- Generator: Adobe Illustrator 23.0.5, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 140 140" style="enable-background:new 0 0 140 140;" xml:space="preserve">
<style type="text/css">
	.st0{fill:#000;}
	.st1{font-family:"Open Sans", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";}
	.st2{font-size:64px;}
</style>
<rect height="140" width="140" x="0" y="0" fill="#eee" />
<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="st0 st1 st2">'.strtoupper($initials).'</text>
</svg>

';
                        die();
                    }
                } else {
                    reDirShowPic(CPerson::$defaultPath . "/pics/{$path[1]}_pp.jpg", Mt::$appDir . "/Assets/img/unknownuser.jpg");
                }
                die();
            } else {
                pError();
            }

        case "messages":
            if ($c == 3) {
                $_GET = array("box" => $path[1], "msg" => $path[2]);
                require_once(__DIR__ . "/Messages.php");
                die();
            }
        default:
            break;
    }

} else {
    header("Location: 404.php");
}

pError();
