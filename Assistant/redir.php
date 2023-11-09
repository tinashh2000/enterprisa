<?php
namespace Assistant;
use Api\Users\CUser;
use Api\Mt;
use Api\CPerson;
use Api\CSlide;
use Ffw\Status\CStatus;
use TariAds\CShop;
use TariAds\CListing;
use TariAds\CBaseProduct;

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
                    if ($user['pics'] == "") {
                        header("Content-type: image/jpeg", true);
                        echo file_get_contents(Mt::$appDir . "/Assets/img/unknownuser.jpg");
                        die();
                    } else if (isset($user['pics'])) {
                        $p = json_decode($user['pics'], true);
                        if (isset($p[$path[3]])) {
                            header("Content-type: image/jpeg", true);
                            if (file_exists(CUser::$defaultPath . "/{$user['username']}/{$p[$path[3]]}"))
                                echo file_get_contents(CUser::$defaultPath . "/{$user['username']}/{$p[$path[3]]}");
                            else {
                                echo file_get_contents(Mt::$appDir . "/Assets/img/unknownuser.jpg");
                            }
                            die();
                        }
                    }
                    print_r($path);
//                    switch ($path[3]) {
//                        case "profile":
//
//                    }
                } else {
                    print_r(CStatus::getErrors());
                    pError();
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
//                echo CPerson::$defaultPath . "/pics/{$path[1]}_pp.jpg";
                reDirShowPic(CPerson::$defaultPath . "/pics/{$path[1]}_pp.jpg", "");
                die();
            } else {
                pError();
            }
        case "shops":
            if ($c == 2) {
                $_GET = array("sid" => $path[1]);
                require_once(__DIR__ . "/../ViewShop.php");
                die();
            } else if ($path[2] == "pics") {
                reDirShowPic(CShop::$defaultPath . "/pics/{$path[1]}_pp.jpg", Mt::$appDir . "/Assets/img/nologo.jpg");
                die();
            } else {
                pError();
            }
            break;
        case "baseProducts":
        case "products":
            if ($c == 2) {
                $_GET = array("pid" => $path[1]);
                require_once(__DIR__ . "/../ViewBaseProduct.php");
                die();
            } else if ($path[2] == "pics") {
                if ($c == 3) {
                    reDirShowPic(CBaseProduct::$defaultPath . "/pics/{$path[1]}_1.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                } else if ($c > 3 && is_numeric($path[3])) {
                    reDirShowPic(CBaseProduct::$defaultPath . "/pics/{$path[1]}_{$path[3]}.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                    die();
                }
            } else {
                pError();
            }
            break;
        case "listings":
            if ($c == 2 || ($c==3 && $path[2]=="")) {
                $_GET = array("pid" => $path[1]);
                require_once(__DIR__ . "/../ListingPage.php");
                die();
            } else if ($path[2] == "pics") {
                if ($c == 3) {
                    if (file_exists(CListing::$defaultPath . "/pics/{$path[1]}_1.jpg"))
                        reDirShowPic(CListing::$defaultPath . "/pics/{$path[1]}_1.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                    else {
                        $l = CListing::get($path[1]);
                        if ($l && file_exists(CBaseProduct::$defaultPath . "/pics/{$l['productId']}_1.jpg"))
                            reDirShowPic(CBaseProduct::$defaultPath . "/pics/{$l['productId']}_1.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                        else
                            reDirShowPic(CBaseProduct::$defaultPath . "/pics/{$path[1]}_1.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                    }

                } else if ($c > 3 && is_numeric($path[3])) {

                    if (file_exists(CListing::$defaultPath . "/pics/{$path[1]}_{$path[3]}.jpg"))
                        reDirShowPic(CListing::$defaultPath . "/pics/{$path[1]}_{$path[3]}.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                    else {
                        $l = CListing::get($path[1]);
                        if ($l && file_exists(CBaseProduct::$defaultPath . "/pics/{$l['productId']}_{$path[3]}.jpg"))
                            reDirShowPic(CBaseProduct::$defaultPath . "/pics/{$l['productId']}_{$path[3]}.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                        else
                            reDirShowPic(CBaseProduct::$defaultPath . "/pics/{$path[1]}_{$path[3]}.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");
                    }
                }
                die();
            } else {
                pError();
            }

        case "slides":
            if ($path[2] == "pics") {

                if (file_exists(CSlide::$defaultPath . "/slides/{$path[1]}.jpg"))
                    reDirShowPic(CSlide::$defaultPath . "/slides/{$path[1]}.jpg", Mt::$appDir . "/Assets/img/placeholder.svg");

                die("Error");
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
