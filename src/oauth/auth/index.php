<?php	
	// load stuff
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
		exit();
	}
	
	function fail($reason, $code = 400) {
		http_response_code($code);
		echo $reason;
	}
	
	// make a place for us to put request data
	$request;
	if (!in_array($_SERVER["REQUEST_METHOD"], ["GET", "POST"])) {
		fail("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
		exit();
	}
	$request = filter_var_array(array_merge($_POST, $_GET), FILTER_SANITIZE_STRING);
	
	// user needs to be authenticated to do anything on this page
	if (!$auth->checkUserIsLoggedIn())
	{
		// have user sign in and then bring them back to this page
		header("Location: /auth/?redirect=" . urlencode("/oauth/auth/?" . urldecode(http_build_query($request))));
		exit();
	}
	
	// check response type
	if (!array_key_exists("response_type", $request)) {
		fail("No response_type specified");
		exit();
	}
	if (!in_array($request["response_type"], ["token", "code"])) {	// only allow token and code grants
		fail("Invalid or unknown response_type: " . $request["response_type"]);
		exit();
	}
	
	// check scopes
	if (!array_key_exists("scope", $request)) {
		fail("No scope was specified, but is required to be defined.");
		exit();
	}
	$scopes = explode(',', $request["scope"]);
	foreach($scopes as $scope) {
		if (!in_array($scope, \OAuth::$scopes)) {
			fail("Scope " . $scope . " is invalid.");
			exit();
		}
	}
	
	// check client_id
	if (!array_key_exists("client_id", $request)) {
		fail("No client_id specified.");
		exit();
	}
	$client = $oauth->getClientFromId($request["client_id"]);
	if (is_null($client)) {
		fail("Invalid client_id");
		exit();
	}
	
	// get state
	$state = null;
	if (array_key_exists("state", $request)) {
		$state = $request["state"];
	}
	
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Authorize access to your account</title>
	<meta content="" name="description">
	<meta content="" name="author">
	
	<?php include '../../php/templates/basichead.php'; ?>
	
	<link href="/assets/css/auth.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<form id="loginform" class="form-auth" method="post">
			<h2 class="">Alexa REST API</h2><br />
			
			<div class="text-center">
				<img class="rounded-circle text-center" style="max-width: 100px;" src="/assets/images/unknown-client.png">
			</div>
			<br />
			
			<h4 class="auth-description">Give <a href="#"><?php echo $client->friendly_name; ?></a> access to your account?</h4>
			
			<span class="auth-bigger"><p style="margin-bottom: 5px;">This will allow them to do the following things:</p>
			<ul>
				<li>Control Your Devices</li>
				<li>Manage your devices</li>
				<li>View your profile info</li>
			</ul></span>
			
			<br/>
			
			<div style="border-bottom: 20px;">
				<button class="btn btn-lg btn-info float-left" style="width: 45%" type="button">Allow</button>
				<button class="btn btn-lg  btn-danger float-right" style="width: 45%" type="button">Deny</button>
			</div>
			<br class="high" />
			<div class="auth-email"><?php echo $_SESSION["email"]; ?>&nbsp;<a href="<?php echo "/auth/?action=logout&redirect=" . urlencode("/oauth/auth/?" . $_SERVER["QUERY_STRING"]);?>">(Not you?)</a></div>
		</form>
	</div>
	<script src="/assets/js/ie10-viewport-bug-workaround.js"></script>
	<script src="/assets/js/jquery-form.min.js"></script>
</body>
</html>
