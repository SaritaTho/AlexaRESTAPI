<?php
	// commoncode handles login redirect, so include it to prevent the user from being redirected again
	include "commoncode.php";
	
	header('Location: /dashboard/overview/');
	exit();
?>
