<?php

namespace Api;

use Currencies\CCurrency;
use Api\CPrivilege;
require_once("D.php");
require_once("Classes/CCurrency.php");

CPrivilege::addPrivilegesList(array( "Currencies" => array(
    "Currencies" => array(
            "All" => CCurrency::CURRENCIES_ALL,
            "Create" => CCurrency::CURRENCIES_CREATE,
            "Delete" => CCurrency::CURRENCIES_DELETE,
            "View" => CCurrency::CURRENCIES_READ,
            "Alter" => CCurrency::CURRENCIES_WRITE,
        ),
)));