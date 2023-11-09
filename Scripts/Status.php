<?php

function printStatus() {
	global $errormsg, $statusmsg;

	if ($errormsg != "") {
		echo '<div class="alert alert-danger alert-dismissable" role="alert"><button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>'.$errormsg.'</div>';
	}
	else if ($statusmsg != "") {
		echo '<div class="alert bg-blue alert-dismissable" role="alert"><button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>'.$statusmsg.'</div>';
	}
}


function jsGetStatus() {
	global $errormsg, $statusmsg;
	if ($errormsg != "") {
		echo '<script>swal("Error", "'.html_entity_decode($errormsg).'", "error");</script>';
	}
	else if ($statusmsg != "") {
		echo '<script>swal("Error", "'.html_entity_decode($statusmsg).'", "success");</script>';
	}
}

?>
