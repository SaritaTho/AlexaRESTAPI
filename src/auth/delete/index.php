<?php
	ob_start();
	
	session_start();
	
	include_once "../../config.php";
	include_once "../../php/Database.php";
	include_once "../../php/Auth.php";
	
	$database = new Database($webconfig["database"]);
	$auth = new Auth($database);
	
	if (!$auth->checkUserIsLoggedIn()) {
		header("Location: /auth/?redirect=/auth/delete");
		exit();
	}
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Delete Account - Alexa REST API</title>
	<meta content="" name="description">
	<meta content="" name="author">
	
	<?php include '../../php/templates/basichead.php'; ?>
	
	<link href="/assets/css/sticky-footer.css" rel="stylesheet">
	<link href="/assets/css/auth.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<form id="loginform" class="form-auth" method="post" action="loginprocessor.php">
			<h2 class="form-auth-heading">Delete your account</h2><br />
			
			<p class="notice text-center"><strong><?php echo $_SESSION["email"]; ?></strong></p>
			
			<label class="sr-only" for="inputPassword">Password</label>
			<input name="password" class="form-control" id="inputPassword" placeholder="Confirm password" required="true" type="password">
			
			<!--<div class="checkbox">
				<label><input type="checkbox" value="remember-me"> Remember me</label>
			</div>-->
			<p class="error"><strong><span class="hidden" id="errortext">Error logging in</span></strong></p>
			<button class="btn btn-lg btn-danger btn-block" type="submit">Delete Account</button><br />
			<button class="btn btn-large btn-success btn-block" type="button" onClick="javascript:window.history.back();">Back to safety</a></button>
		</form>
	</div>
	
	<?php include "../footer.php"; ?>
	
	<?php include '../../php/templates/basicscripts.php'; ?>
	<script src="/assets/js/ie10-viewport-bug-workaround.js"></script>
	<script src="/assets/js/jquery-form.min.js"></script>
</body>
</html>
