<?php
	session_start();
	
	include_once "../config.php";
	
	if (array_key_exists("action", $_GET)) {
		$action = $_GET["action"];
		
		include_once "../php/Database.php";
		include_once "../php/Auth.php";
		
		$database = new Database($webconfig["database"]);
		$auth = new Auth($database);
		
		// user logging out
		if ($action == "logout") {
			if (array_key_exists("token", $_SESSION)) {
				$auth->logout($_SESSION["token"]);
				$auth->destroySession();
			}
			
			if (array_key_exists("redirect", $_GET)) {
				header('Location: ' . $_GET["redirect"]);
			} else {
				header('Location: /');
			}
			
			exit();
		} else if ($action == "create") {
			header('Location: /auth/create/');
			exit();
		}
	}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Sign in to Alexa API</title>
	<meta content="" name="description">
	<meta content="" name="author">
	
	<?php include '../php/templates/basichead.php'; ?>
	
	<link href="/assets/css/sticky-footer.css" rel="stylesheet">
	<link href="/assets/css/auth.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<form id="loginform" class="form-auth" method="post" action="loginprocessor.php">
			<h2 class="form-auth-heading">Sign in to Alexa REST API</h2><br />
			
			<label class="sr-only" for="inputEmail">Email address</label>
			<input name="email" autofocus="true" class="form-control top" id="inputEmail" placeholder="Email address" required="true" type="email">
			
			<label class="sr-only" for="inputPassword">Password</label>
			<input name="password" class="form-control bottom" id="inputPassword" placeholder="Password" required="true" type="password">
			
			<!--<div class="checkbox">
				<label><input type="checkbox" value="remember-me"> Remember me</label>
			</div>-->
			<p class="error"><strong><span class="hidden" id="errortext">Error logging in</span></strong></p>
			<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button><br />
			<p class="text-center">Or, <a href="create/">create an account.</a></p>
		</form>
	</div>
	
	<?php include "footer.php"; ?>
	
	<?php include '../php/templates/basicscripts.php'; ?>
	<script>redirectUri = <?php if (array_key_exists("redirect", $_GET)) {
		echo "\"" . $_GET["redirect"] . "\"";
	} else {
		echo "null";
	} ?>;
	</script>
	<script src="/assets/js/ie10-viewport-bug-workaround.js"></script>
	<script src="/assets/js/jquery-form.min.js"></script>
	<script src="login.js"></script>
</body>
</html>