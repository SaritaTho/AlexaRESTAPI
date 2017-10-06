<?php
	// generates some javascript
	
	header("Content-type: application/javascript");
	
	if (array_key_exists("hash", $_GET)) {
		echo "$(\"#" . $_GET["hash"] . "\").on(\"remove\", () => { location.reload(); });";
	}
?>
