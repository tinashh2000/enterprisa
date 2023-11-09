<?php

require_once(Mt::$appDir . "/Api/CPrivilege.php");
function checkPrivileges($RequiredPrivilege)  {
	if (!CPrivilege::check($RequiredPrivilege)) {
		if (($RequiredPrivilege & Privileges::IS_ADMIN) > 0 && !isUserAdmin()) {
			require_once(Mt::$appDir . "/404.php");
			die();
		}
		$ErrorMessage = "Current user does not have the required privileges to access this page";
		require_once(Mt::$appDir . "/PageError.php");
		die();
	}
}
?>
