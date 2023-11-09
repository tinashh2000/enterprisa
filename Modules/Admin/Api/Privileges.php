<?php

namespace Api;

use Api\Users\CUser;
use Admin\CAdmin;
use Api\CPrivilege;

require_once("D.php");
require_once("Classes/CAdmin.php");

CPrivilege::addPrivilegesList(array( "Admin" => array(
    "Users" => array(
            "All" => CPrivilege::L_ALL_USER,
            "Create" => CPrivilege::L_CREATE_USER,
            "Delete" => CPrivilege::L_DELETE_USER,
            "View" => CPrivilege::L_VIEW_USER,
            "Alter" => CPrivilege::L_ALTER_USER
        ),
)));
