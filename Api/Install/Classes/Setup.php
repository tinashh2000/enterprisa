<?php

namespace Api\Install;

use Ffw\Status\CStatus;
use Api\AppConfig;
use Api\Mt;
use Api\Users\CUser;
use Api\Forensic\CForensic;
use Api\AppDB;
use Api\Roles\CRole;
use Api\CEnterprisa;

set_time_limit(0); //Unlimited max execution time
require_once(__DIR__ . "/../../Bootstrap.php");
require_once(__DIR__ . "/../../Classes/CRole.php");
class Setup
{
	static function initDB($reset = false)
	{
        CEnterprisa::init($reset);
		return true;
	}
}

