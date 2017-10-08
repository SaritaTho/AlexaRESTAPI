<?php
	ob_start();
	
	include_once "../../config.php";
	include_once "../../php/Database.php";
	include_once "../../php/Auth.php";
	
	// load classes
	try {
		$database = new Database($webconfig["database"]);
		$auth = new Auth($database);
	} catch (\Exception $e) {
		error_log("Error initialzing: $e");
		complete(false, "An internal error occured, please refresh and try again.");
		exit();
	}
	
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
	
	// move stuff into local variables
	$email = $_POST["email"];
	$confirmemail = $_POST["confirmemail"];
	$password = $_POST["password"];
	$confirmpassword = $_POST["confirmpassword"];
	
	// check that confirmed emails & passwords all match
	if ($email !== $confirmemail) {
		complete(false, "The emails you entered do not match.");
		exit();
	} else if ($password !== $confirmpassword) {
		complete(false, "The passwords you entered do not match.");
		exit();
	}
	
	// validate email
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		complete(false, "Please enter a valid email address.");
		exit();
	}
	
	// check if any other users are already registered with that email
	try {
		$results = $database->pquery("SELECT NULL FROM `users` WHERE `email` = ?;", [$email])->fetchAll();
		
		// check if email exists in database
		if (count($results) > 0) {
			complete(false, "That email is already registered.");
		}
	} catch (\Exception $e) {
		error_log("Error checking for existing user ($email): $e");
		complete(false, "An internal error occured. Please refresh and try again.");
		exit();
	}
	
	// check reCAPTCHA
	if (!$webconfig["development"] && $webconfig["recaptcha"]["enabled"]) {
		try {
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
				throw new Exception("Google reCAPTCHA verification request failed");
			}
			
			// decode json response
			try {
				$recaptcha_result = json_decode($recaptcha_result_raw);
			} catch (\Exception $e) {
				error_log("Failed to parse reCAPTCHA response: $e\n\nResponse content:\n$recaptcha_result_raw");
				complete(false, "An error occured. Please refresh and try again");
				exit();
			}
			
			// recaptcha failed
			if ($recaptcha_result->success != true) {
				complete(false, "Please complete the reCAPTCHA");
				exit();
			}
		} catch (\Exception $e) {
			error_log("Error occured verifying reCAPTCHA: $e");
			complete(false, "An internal error occured. Please refresh and try again.");
			exit();
		}
	}
	
	// create the account
	try {
		$auth->generateUserLogin($email, $password);
	} catch (\Exception $e) {
		error_log("Error creating user login: $e");
		complete("An internal error occured. Please refresh and try again.");
		exit();
	} finally {
		complete(true);
		exit();
	}
	
	// complete the request and terminate the script
	function complete($success, $message = null) {
		header('Content-type: application/json');
		echo json_encode (["success" => $success, "message" => $message]);
		exit();
	}
?>
