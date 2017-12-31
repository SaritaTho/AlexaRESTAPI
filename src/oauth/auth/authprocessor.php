<?php
	session_start();
	
	try {
		include_once "../../config.php";
		include_once "../../php/Database.php";
		include_once "../../php/Auth.php";
		include_once "../../php/OAuth.php";
		
		$database = new Database($webconfig["database"]);
		$auth = new Auth($database);
		$oauth = new OAuth($database);
	}
	catch (\Exception $e)
	{
		error_log($e);
		fail("Internal init error");
		exit();
	}
	
	// get all request data
	if (!in_array($_SERVER["REQUEST_METHOD"], ["GET", "POST"])) {
		fail("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
		exit();
	}
	$request = filter_var_array(array_merge($_GET, $_POST), FILTER_SANITIZE_STRING);
	// add state in if it doesn't exist
	if (!array_key_exists("state", $request)) {
		$request["state"] = null;
	}
	
	// fucntion for getting the original request string
	function getRequestString() {
		global $request;
		return urldecode(http_build_query($request));
	}
	
	// check that user is logged in
	if (!$auth->checkUserIsLoggedIn()) {
		// send them back to the auth page and let it handle it
		header(sprintf("Location: /oauth/authf(/?%s", getRequestString()));
		exit();
	}
	
	// check auth key
	if (!array_key_exists("authkey", $request) || !array_key_exists("oauth_auth_key", $_SESSION) || $request["authkey"] != $_SESSION["oauth_auth_key"]) {
		// unset authkey because it was invalid
		unset($_SESSION["oauth_auth_key"]);
		unset($request["authkey"]);
		
		header(sprintf("Location: /oauth/auth/?%s", getRequestString()));
		exit();
	}
	
	// get client
	$client = $oauth->getClientFromId($request["client_id"]);
	if (is_null($client)) {
		fail("Invalid client_id");
		exit();
	}
	
	// check redirect uri
	if (!in_array($request["redirect_uri"], $client->redirect_uris)) {
		fail("Invalid redirect_uri");
		exit();
	}
	
	// displays an error message
	function fail($message = "Error during authentication") {
		global $request;
		echo sprintf("%s<br><a href=\"/oauth/auth?%s\">Retry?</a>", $message, getRequestString());
		exit();
	}
	
	// used to send success callback
	function callback_success() {
		// TODO: this
	}
	
	// used to send the user back to the webpage with an error description
	function callback_error($error = "server_error", $description = null) {
		$cb_params = [
			"error" => $error,
			"state" => $request["state"],
			"error_description" => $description ];
			
		header("Location: " . $request["redirect_uri"] . "?" . http_build_query($cb_params));
		exit();
	}
