<?php	
	// load stuff
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
		http_response_code(500);
		exit;
	}
	
	// make a place for us to put request data
	if (!in_array($_SERVER["REQUEST_METHOD"], ["GET", "POST"])) {
		error("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
		exit;
	}
	$request = filter_var_array(array_merge($_POST, $_GET), FILTER_SANITIZE_STRING);
	
	// user needs to be authenticated to do anything on this page
	if (!$auth->checkUserIsLoggedIn())
	{
		// have user sign in and then bring them back to this page
		header(sprintf("Location: /auth/?redirect=%s", urlencode("/oauth/auth/?" . getRequestParams())));
		exit;
	}
	$user = $auth->getCurrentUser();
	
	// check client_id
	if (!array_key_exists("client_id", $request)) {
		error("No client_id specified");
		exit;
	}
	$client = $oauth->getClientFromId($request["client_id"]);
	if (is_null($client)) {
		error("Invalid client_id specified. ");
		exit;
	}
	
	// get redirect uri
	if (array_key_exists("redirect_uri", $request) && !empty($request["redirect_uri"])) {
		if (!in_array($request["redirect_uri"], $client->redirect_uris)) {
			error("Invalid redirect_uri");
			exit;
		}
	} else {
		// default to first defined redirect uri if it is not set
		$request["redirect_uri"] = $client->redirect_uris[0];
	}
	
	// check scopes
	if (!array_key_exists("scope", $request)) {
		callback_error("invalid_scope");
		exit;
	}
	$scopes = explode(',', $request["scope"]);
	foreach($scopes as $scope) {
		if (!in_array($scope, array_keys(\OAuth::$scopes))) {
			callback_error("invalid_scope");
			exit;
		}
	}
	
	// get state
	if (!array_key_exists("state", $request)) {
		$request["state"] = null;
	}
	
	// check to see if it's been confirmed yet
	if (array_key_exists("accepted", $request)) {
		// check auth key
		if (!array_key_exists("authkey", $request) || $request["authkey"] !== $_SESSION["oauth_auth_key"]) {
			// auth key fail
			unset($request["authkey"]);
			unset($request["accepted"]);
			
			header(sprintf("Location: .?%s", getRequestParams()));
			exit;
		}
		
		if ($request["accepted"] === "true") {
			// generate token based on token type
			switch ($request["response_type"]) {
				case "code": {
					
					break;
				}
				case "token": {
					try {
						// generate token
						$token = $oauth->createAccessToken($client->id, $user->id, $request["scope"]);
					} catch (\Exception $ex) {
						error_log($ex);
						callback_error("server_error");
						exit;
					}
					
					callback_token_response($token);
					break;
				}
				default: {
					callback_error("unsupported_response_type");
					break;
				}
			}
			exit;
		} else if ($request["accepted"] === "false") {
			callback_error("access_denied");
			exit;
		} else {
			error("Invalid value for accepted");
			exit;
		}
	}
	
	// generate a random key that will be checked by the oauth handler
	// this ensures authorizations can only come from this page
	$_SESSION["oauth_auth_key"] = bin2hex(random_bytes(4));
	
	/**
	 * Gets the contents of $request, expressed as url parameters
	 */
	function getRequestParams() {
		global $request;
		return http_build_query($request);
	}
	
	/**
	 * Used for error callback
	 * 
	 * @param $error string An error code as specified by RFC6749 Section 4.1.2.1
	 */
	function callback_error($error, $description = null) {
		global $request;
		
		$cb_params = [
			"error" => $error,
			"state" => $request["state"],
			"error_description" => $description ];
			
		header("Location: " . $request["redirect_uri"] . "?" . http_build_query($cb_params));
		exit;
	}
	
	/**
	 * Sends an access token to the client 
	 * 
	 * @param $token string The access token string
	 * @param $expiry int The lifetime of the token, in seconds from the current time
	 * @param $type string The type of the token. Must be valid according to RFC6749
	 */
	function callback_token_response($token, $expiry = 0, $type = "bearer") {
		global $request;
		
		$cb_params = [
			"access_token" => $token,
			"token_type" => $type,
			"expires_in" => $expiry,
			"scope" => $request["scope"],
			"state" => $request["state"]
		];
		
		var_dump($cb_params);
		
		header(sprintf("Location: %s?&%s", $request["redirect_uri"], http_build_query($cb_params)));
		exit;
	}
	
	/**
	 * Informs the user of an error, and gives them an option to return to the previous page
	 * 
	 * @param $message string The message to display
	 */
	function error($message) {
		printf("%s<br><a href=\"javascript:history.back();\">Go back?</a>", $message);
	}
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Authorize access to your account</title>
	<meta content="" name="description">
	<meta content="" name="author">
	
	<?php include '../../php/templates/basichead.php'; ?>
	<script>
		var authRequest = <?php echo json_encode($request); ?>;
		var authKey = "<?php echo $_SESSION["oauth_auth_key"]; ?>";
	</script>
	<link href="/assets/css/auth.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<form id="loginform" class="form-auth" method="post">
			<div class="text-center">
				<img class="rounded-circle text-center" style="max-width: 100px;" src="<?php echo $client->icon ?? "/assets/images/unknown-client.png"; ?>">
			</div>
			<p class="text-center auth-publisher"><?php echo $client->publisher_name; ?></p>
			<h4 class="auth-description text-center">Allow <a href="<?php echo $client->url; ?>" target="_blank"><?php echo $client->friendly_name; ?></a> to access your account?</h4>
			<span class="auth-bigger"><p style="margin-bottom: 5px;">This will allow them to do the following:</p>
			<ul class="scope-list">
				<?php
					// list scopes as list items
					foreach ($scopes as $scope) {
						echo "<li>" . \OAuth::$scopes[$scope] . "</li>";
					}
				?>
			</ul></span>
			<br/>
			<div style="border-bottom: 20px;">
				<button class="btn btn-lg btn-info float-left" style="width: 45%" type="button" onclick="Submit(true);">Allow</button>
				<button class="btn btn-lg  btn-danger float-right" style="width: 45%" type="button" onclick="Submit(false);">Deny</button>
			</div>
			<br class="high" />
			<div class="auth-email"><?php echo $user->email ?>&nbsp;<a href="<?php printf("/auth/?action=logout&redirect=%s", urlencode("/oauth/auth/?" . getRequestParams())); ?>">(Not you?)</a></div>
		</form>
	</div>
	<script src="/assets/js/ie10-viewport-bug-workaround.js"></script>
	<script src="/assets/js/jquery.min.js"></script>
	<script src="oauth-auth.js"></script>
</body>
</html>
