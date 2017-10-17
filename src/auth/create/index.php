<?php
	ob_start();
	
	include_once "../../config.php";
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Create Account - Alexa REST API</title>
	<meta content="" name="description">
	<meta content="" name="author">
	
	<?php include '../../php/templates/basichead.php'; ?>
	
	<link href="/assets/css/auth.css" rel="stylesheet">
	<link href="/assets/css/sticky-footer.css" rel="stylesheet">
	<script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
	<div class="container">
		<form id="createform" class="form-auth" method="post" action="createprocessor.php">
			<h2 class="form-auth-heading">Create Account</h2>
			
			<h6 class="notice">All fields required.</h6>
			
			<label class="sr-only" for="inputEmail">Email address</label>
			<input name="email" autofocus="true" class="form-control top" id="inputEmail" placeholder="Email address" required="true" type="email">
			
			<label class="sr-only" for="inputEmail">Confirm email</label>
			<input name="confirmemail" class="form-control bottom" id="confirmEmail" placeholder="Confirm email" required="true" type="email">
			
			<br>
			
			<label class="sr-only" for="inputPassword">Password</label>
			<input name="password" class="form-control top" id="inputPassword" placeholder="Password" required="true" type="password">
			
			<label class="sr-only" for="inputPassword">Confirm password</label>
			<input name="confirmpassword" class="form-control bottom" id="confirmPassword" placeholder="Confirm password" required="true" type="password">
			
			<!--<div class="checkbox">
				<label><input type="checkbox" value="remember-me"> Remember me</label>
			</div>-->
			
			<?php
				// only display recaptcha if it's enabled and development mode is disabled
				if ($webconfig["recaptcha"]["enabled"]) {
					echo "<div class=\"g-recaptcha\" data-sitekey=\"" . $webconfig["recaptcha"]["site"] . "\"></div>";
				}
			?>
			
			<p class="error"><strong><span class="hidden" id="errortext">Error signing up</span></strong></p>
			<button class="btn btn-lg btn-primary btn-block" type="submit">Create account</button>
			
			<br>
			 
			<p class="text-center">By signing up, you agree to our <a href="/terms" target="_blank">terms of service.</a></p>
		</form>
	</div>
	
	<?php 
		include '../footer.php';
		include '../../php/templates/basicscripts.php'; ?>
	</script>
	<script src="/assets/js/ie10-viewport-bug-workaround.js"></script>
	<script src="/assets/js/jquery-form.min.js"></script>
	<script src="create.js"></script>
</body>
</html>
