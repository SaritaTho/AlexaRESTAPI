<?php
	if ($auth->checkUserIsLoggedIn()) {
		include "navuser.php";
	} else {
		include "navgeneric.php";
	}
?>
