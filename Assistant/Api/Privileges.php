<?php

namespace Api;

use Assistant\CAssistant;
use Api\CPrivilege;

require_once("D.php");
require_once("Classes/CAssistant.php");

if (true) return;
CPrivilege::addPrivilegesList(array( "General" => array(
    "General" => array(
        "All" => CAssistant::ASSISTANT_PERMISSIONSBASE + CPrivilege::ALL,
        "Create" => CAssistant::ASSISTANT_PERMISSIONSBASE + CPrivilege::CREATE,
        "Delete" => CAssistant::ASSISTANT_PERMISSIONSBASE + CPrivilege::DELETE,
        "View" => CAssistant::ASSISTANT_PERMISSIONSBASE + CPrivilege::READ,
        "Alter" => CAssistant::ASSISTANT_PERMISSIONSBASE + CPrivilege::MODIFY,
    ),
)));