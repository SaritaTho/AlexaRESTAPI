<?php
	ob_start();
	
	// not using POST - this can happen when using silent operation
	if ($_SERVER['REQUEST_METHOD'] != 'POST') {
		header('Location: /auth/');
		exit();
	}
	
	if (session_status() == PHP_SESSION_NONE)
		session_start();
	
	include_once "../config.php";
	include_once "../php/Database.php";
	include_once "../php/Auth.php";
	
	$database = new Database($webconfig["database"]);
	$auth = new Auth($database);
	
	// user already logged in
	// don't do anything - handle it silently and allow for account change
	/*if ($auth->checkUserIsLoggedIn()) {
		complete(true, "Already logged in");
	}*/
	
	// no credentials provided
	if (!array_key_exists("email", $_POST) || !array_key_exists("password", $_POST)) {
		complete(false, "Please provide an email and password");
	}
	
	$password = $_POST['password'];
	$email = $_POST['email'];
	
	try {
		if ($auth->checkUserCredentials($email, $password)) {	// login success
			$user = $auth->getUserFromEmail($email);
			$auth->login($user->getUserId());
			
			complete(true);
		} else {
			complete(false, "Invalid username or password");
		}
		
	} catch (\Exception $e) {
		error_log($e);
		if ($webconfig["development"]) {
			complete(false, "Internal error: " . $e);
		} else {
			complete(false, "Uh-oh! Something went wrong. Try again?");
		}
	}
	
	// send a response and finish up
	function complete($success, $message = null) {
		// on silent operation, redirect instantly
		if (array_key_exists("silent", $_POST) && $_POST["silent"] == true) {
			if ($success == true) {
				header('Location: /');
			} else {
				header('Location: /auth/');
			}
			exit();
		} else {	// normal operation
			header('Content-Type: application/json');
			echo json_encode(["success" => $success, "message" => $message]);
		}
		
		exit();
	}
?>
