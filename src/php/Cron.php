<?php
	// runs routinely to clean up website and perform maintenance
	
	include_once __DIR__ . "/../config.php";
	include_once __DIR__ . "/Database.php";
	include_once __DIR__ . "/Auth.php";
	include_once __DIR__ . "/Utility.php";
	
	try {
		$database = new Database($webconfig["database"]);
		$auth = new Auth($database);
	} catch (\Exception $e) {
		slog("Error starting: " . $e);
		exit();
	}
	
	// clear expired tokens
	$stmt = $database->dbh->prepare("DELETE FROM `logintokens` WHERE `expiry` < ?");
	$stmt->execute([time()]);
	
	slog("Cleared old tokens");
	
	exit();
	
	// i added a letter in front of "log" because "log" is reserved.. idk why i chose 's'
	function slog($message) {
		$text = sprintf("[%s] %s", Utility::getLogTime(), $message);
		file_put_contents("../cron_log", $text, FILE_APPEND);
		echo $text;
	}
?>
