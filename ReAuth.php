<?php

use Ffw\Crypt\CCrypt9;
use Api\Authentication\CAuth;
use Api\Mt;
require_once("Api/Bootstrap.php");

if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
    if (CAuth::login(CCrypt9::unScrambleText($_COOKIE['username']), CCrypt9::unScrambleText($_COOKIE['password']), true)) {
        if (CAuth::isLoggedIn()) {
            header("Location:" . Mt::$appRelDir . $_GET['sIRA']);
            return true;
        }
    }
    CAuth::logOut();
}

require_once("SignIn.php");
