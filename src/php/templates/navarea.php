<?php
	try {
		if (isset($auth)) {	// don't display login or user nav if auth is unavailable
			if ($auth->checkUserIsLoggedIn()) {
				include "navuser.php";
			} else {
				include "navgeneric.php";
			}
		}
	} catch (\Exception $e) {
		error_log($e);
	}
?>
