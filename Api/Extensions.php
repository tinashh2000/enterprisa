<?php

namespace Api\X;

class Attendance {
	const SCHEDULED	=	1 << 0;
//	const SCHEDULED	=	1 << 0;
}

class DurationMeasure {
	const Seconds = 0;
	const Minutes = 1;
	const Hours = 2;
	const Days = 3;
	const Weeks = 4;
	const Months = 5;
	const Years = 6;
	const Entirety = 200;
}
require_once("Classes/CPrivilege.php");

?>
