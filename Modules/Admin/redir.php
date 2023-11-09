<?php

use Api\Users\CUser;
use Api\Mt;
use Companies\CCompany;

$contentTypes = array("image/jpeg", "image/png", "image/svg+xml", "image/tiff", "image/gif", "text/javascript", "text/css");
$aLog = "";

function defaultCompanyLogo() {
    header("Content-type: image/svg+xml", true);
    echo file_get_contents(Mt::$appDir . "/Assets/img/hlogo");
    die();
}

if (isset($_GET['redirPageId'])) {
    $path = explode("/", $_GET['redirPageId']);
    $c = count($path);

    if ($c == 3 && $path[2] == "pic") {
        $path[2] = "pics";
        $path[3] = "profile";
    }
    switch ($path[0]) {
        case "companies":
            if ($c == 2) {
                $_GET = array("c" => $path[1]);
                require_once(__DIR__ . "/ViewCompany.php");
                die();
            }  else if ($path[2] == "pics") {
                $id = $path[1];
                if ($company = CCompany::getPics($id)) {
                    if ($company['pics'] == "") {
                        defaultCompanyLogo();
                    } else if (isset($company['pics'])) {
                        $p = json_decode($company['pics'], true);
                        if (isset($p[$path[3]])) {
                            if (file_exists(CCompany::$defaultPath . "/{$id}/{$p[$path[3]]}"))
                                reDirShowPic(CCompany::$defaultPath . "/{$id}/{$p[$path[3]]}");
                            else {
                                defaultCompanyLogo();
                            }
                            die();
                        } else {
                            defaultCompanyLogo();
                        }
                    }
                } else {
                    pError();
                }
                break;
            }
            break;
        default:
            break;
    }
    unset($path[0]);
    $_GET['redirPageId'] = implode("/",$path);
    $file = __DIR__ ."/". implode("/",$path);
    $pi = pathinfo($file);
    $ext = isset($pi['extension']) ? strtolower($pi['extension']) : "";

    extRedir($file, $ext, __DIR__);
    die();

} else {
    header("Location: 404.php");
}

pError();
