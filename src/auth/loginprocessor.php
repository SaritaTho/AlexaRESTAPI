<?php
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
	
	// no credentials provided
	if (!array_key_exists("email", $_POST) || !array_key_exists("password", $_POST)) {
		complete(false, "Please provide an email and password");
	}
	
	$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
	$email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
	
	try {
		// check user login
		if ($auth->checkUserCredentials($email, $password)) {
			$user = $auth->getUserFromEmail($email);
			$auth->login($user->id);
			
			// send success
			complete(true);
			exit;
		} else {
			complete(false, "Incorrect username or password");
			exit;
		}
		
	} catch (\Exception $e) {
		error_log($e);
		
		if ($webconfig["development"]) {
			complete(false, "Internal error: " . $e);
			exit;
		} else {
			complete(false, "An error occured. Try again?");
			exit;
		}
	}
	
	// send a response and finish up
	function complete($success, $message = null) {
		// on silent operation, redirect instantly
		if (array_key_exists("silent", $_POST) && $_POST["silent"]) {
			if ($success) {
				header('Location: /');
			} else {
				header('Location: /auth/?err=' . urlencode($message));
			}
			
			exit;
		}
		
		// output json response
		header('Content-Type: application/json');
		echo json_encode(["success" => $success, "message" => $message]);
		exit;
	}
?>
