<?php

namespace Api;

use Currencies\CCurrency;
use Api\CPrivilege;
use Entities\CEntities;

require_once("D.php");
require_once("Classes/CEntities.php");

CPrivilege::addPrivilegesList(array( "Entities" => array(
    "Entities" => array(
            "All" => CEntities::ENTITY_ALL,
            "Create" => CEntities::ENTITY_CREATE,
            "Delete" => CEntities::ENTITY_DELETE,
            "View" => CEntities::ENTITY_READ,
            "Alter" => CEntities::ENTITY_WRITE,
        ),
)));