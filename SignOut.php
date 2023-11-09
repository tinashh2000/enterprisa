<?php 
require_once("Api/Bootstrap.php");

use Api\Authentication\CAuth;
CAuth::logOut();
header("Location:Home");
?>
