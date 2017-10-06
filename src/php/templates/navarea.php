<?php
	$loggedIn = false;
	try {
		if (isset($auth))
			$loggedIn = $auth->checkUserIsLoggedIn();
	} catch (\Exception $e) {
		error_log($e);
	}
	
	if ($loggedIn) {
		include "navuser.php";
	} else {
		include "navgeneric.php";
	}
?>
