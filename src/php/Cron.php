<?php
	// runs routinely to clean up website and perform maintenance
	
	include_once "../config.php";
	include_once "Database.php";
	include_once "Auth.php";
	
	$database = new Database($webconfig["database"]);
	$auth = new Auth($database);
	
	// clear expired tokens
	$stmt = $database->dbh->prepare("DELETE FROM `tokens` WHERE `expiry` < ?");
	$stmt->execute([time()]);
	
	slog("Cleared old tokens");
	
	exit();
	
	// i added a letter in front of "log" because "log" is reserved.. idk why i chose 's'
	function slog($message) {
		file_put_contents("../cron_log", date("Y-M-d.H:m") . " >> " . $message . "\n", FILE_APPEND);
	}
?>
