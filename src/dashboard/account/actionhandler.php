<?php
	if (session_status() == PHP_SESSION_NONE)
		session_start();
	
	include_once "../../config.php";
	include_once "../../php/Database.php";
	include_once "../../php/Auth.php";
	
	$database = new Database($webconfig["database"]);
	$auth = new Auth($database);
	
	if (!$auth->checkUserIsLoggedIn()) {
		complete(false, "User not logged in.");
	}
	
	// check if action exists
	if (!array_key_exists("action", $_POST)) {
		complete(false, "No action specified.");
	}
	
	$action = $_POST["action"];
	
	try {
		switch ($action) {
			// change account email
			case "changeemail":
				if (!array_key_exists("newemail", $_POST)) {
					complete(false, "No email given.");
				}
				
				$newemail = $_POST["newemail"];
				if (!filter_var($newemail, FILTER_VALIDATE_EMAIL)) {
					complete(false, "Invalid email.");
					break;
				}
								
				$auth->changeAccountEmail($_SESSION["userid"], $newemail);
				complete(true);
				break;
			// log out a single token - using the "hash" POST parameter
			case "logouttoken"
				if (!array_key_exists("hash", $_POST)) {
					complete(false, "No hash provided.");
					break;
				}
				
				// get token from hash
				$stmt = $database->dbh->prepare("SELECT `token` FROM `logintokens` WHERE `hash` = ? LIMIT 1");
				$result = $stmt->execute([$_POST["hash"]]);
				
				if ($result == null) {
					complete(false, "Database error");
					break;
				}
				
				$results = $stmt->fetchAll();
				
				if (count($results) == 0) {
					complete(false, "Token not found");
					break;
				}
				
				$auth->logout($results[0]["token"]);
				complete(true);
				break;
			// invalid action
			default:
				complete(false, "Invalid action.");
				break;
		}
	} catch (Exception $e) {
		complete(false, "Internal error: " . $e->getMessage() . " " . $e->getFile() . ":" . $e->getLine());
	}
	
	function complete($success, $message = null) {
		header('Content-Type: application/json');
		echo json_encode(["success" => $success, "message" => $message]);
		exit();
	}
?>
