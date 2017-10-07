<?php
	ob_start();
	
	// system for creating user logins
	
	include_once "../../config.php";
	
	// make sure user inputted all info
	if (!array_key_exists("email", $_POST)) {
		complete(false, "Please enter an email.");
	} else if (!array_key_exists("password", $_POST)) {
		complete(false, "Please enter a password.");
	} else if (!array_key_exists("confirmemail", $_POST)) {
		complete(false, "Please confirm your email.");
	} else if (!array_key_exists("confirmpassword", $_POST)) {
		complete(false, "Please confirm your password.");
	}
	
	// move stuff
	$email = $_POST["email"];
	$confirmemail = $_POST["confirmemail"];
	
	$password = $_POST["password"];
	$confirmpassword = $_POST["confirmpassword"];
	
	// check emails &  passwords all match
	if ($email !== $confirmemail) {
		complete(false, "The emails don't match.");
	} else if ($password !== $confirmpassword) {
		complete(false, "Passwords don't match.");
	}
	
	// check valid email
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		complete(false, "Please enter a valid email address.");
	}
	
	// check recaptcha
	
	// create payload
	$recaptcha_payload = [
		"secret" => $webconfig["recaptcha"]["secret"],
		"response" => $_POST["g-recaptcha-response"],
		"remoteip" => $_SERVER["REMOTE_ADDR"]
	];
	
	// create headers
	$recaptcha_headers = [
		"http" => [
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($recaptcha_payload)
		]
	];
	
	// send POST
	$recaptcha_result_raw = file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create($recaptcha_headers));
	
	if ($recaptcha_result_raw == false) {
		complete(false, "An internal error occured.");
	}
	
	$recaptcha_result = json_decode($recaptcha_result_raw);
	
	if ($recaptcha_result->success != true) {
		complete(false, "Please complete the reCAPTCHA");
	}
	
	try {
		// do the stuff
		include_once "../../php/Database.php";
		include_once "../../php/Auth.php";
		
		$database = new Database($webconfig["database"]);
		$auth = new Auth($database);
		
		// check if any other users already registered with that email
		$stmt = $database->dbh->prepare("SELECT NULL FROM `users` WHERE `email` = ?");
		$result = $stmt->execute([$email]);
		
		if ($result == null) {
			throw new Exception("Existing user query failed");
		}
		
		// check if email exists in database
		if (count($stmt->fetchAll()) > 0) {
			complete(false, "That email is already registered.");
		}
		
		$auth->generateUserLogin($email, $password);
		
		complete(true);
	} catch (Exception $e) {
		// replace this with 500
		//complete(false, $e->getMessage());
		header('HTTP 1.1 500 Internal Server Error');
	}
	
	// complete the request and terminate the script
	function complete($success, $message = null) {
		header('Content-type: application/json');
		echo json_encode (["success" => $success, "message" => $message]);
		exit();
	}
?>
