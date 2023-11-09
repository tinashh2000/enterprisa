<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/fos/Bootstrap.php");
	require_once (ROOTDIR . "/fos/login/admin_auth.php");
?>

<form id="frm" action="show_db.php" method="post">
<table cellpadding="0" cellspacing="2">
<tr><td align="center" valign="middle">Database Filter</td><td><input class="sinp" type="text" name="dbfilter" /></td></tr>
<tr><td align="center" valign="middle">Table Filter</td><td><input class="sinp" type="text" name="tblfilter" /></td></tr>
<tr><td align="center" valign="middle">Username</td><td><input class="sinp" type="text" name="admin_un" /></td></tr>
<tr><td align="center" valign="middle">Password</td><td><input class="sinp" type="password" name="admin_pw" /></td></tr>
<tr><td></td><td><input class="sinp" type="submit" /></td></tr>
</table>
</form>

<?php

	$fdb = isset($_POST['dbfilter'])?$_POST['dbfilter']:fosDB;
	$ftbl= isset($_POST['tblfilter'])?$_POST['tblfilter']:"";
	
//	$fosDB->searchTables(true,false,"",$fx);
	if ($fdb != "" || $ftbl != "") {
		echo "(Filters: $fdb, $ftbl)";
		$fosSrv->searchDBs($fdb,$ftbl);	
	}
	else
	{
		echo "No filters";
		$fosSrv->searchDBs("");
		$fosSrv->searchDBs("mt%");
		$fosSrv->searchDBs("ndeip%");
	}
?>