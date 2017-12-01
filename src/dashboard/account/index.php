<?php
	include "../commoncode.php";
	include_once "../../config.php";
	include_once "../../php/Database.php";
	include_once "../../php/Utility.php";
	include_once "activity/activity.php";
	include_once "account.php";
	
	$database = new Database($webconfig["database"]);
	$account = new Account($database);
	$activity = new Activity($database);
	
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Alexa REST API Dashboard</title>
	
	<?php include '../../php/templates/basichead.php';
	include '../commonhead.php'; ?>
	
	<link href="/assets/css/dashboard.css" rel="stylesheet">
	<link href="account.css" rel="stylesheet">
</head>
<body>
	<?php include "../commonnav.php"; ?>
	<div class="container-fluid">
		<div class="row">
			<nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar">
				<ul class="nav nav-pills flex-column">
					<li class="nav-item">
						<a class="nav-link" href="../overview">Overview</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="../devices">Devices</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="../logs">Logs</a>
					</li>
				</ul>
				<ul class="nav nav-pills flex-column">
					<li class="nav-item">
						<a class="nav-link active" href="../account">Account <span class="sr-only">(current)</span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="/contact">Support</a>
					</li>
				</ul>
			</nav>
			<main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
				<h1>Account</h1>
				<section class="row accountdetails">
					<div class="container-fluid push-left" id="editinfo">
						<form id="account-form" method="post" action="accountupdateprocessor.php">
							<div class="input-group">
								<span class="input-group-addon"id="email-addon"><i class="fa fa-fw fa-envelope"></i></span>
								<input name="email" class="form-control" aria-label="Email" aria-describedby="email-addon" id="change-email" type="email" placeholder="Email" value="<?php echo $_SESSION["email"]; ?>">
							</div>
							
							<br>
							
							<div class="input-group top">
								<span class="input-group-addon top" id="password-addon"><i class="fa fa-fw fa-key"></i></span>
								<input name="password" class="form-control top" autocomplete="off" aria-label="Password" aria-describedby="password-addon" id="change-password" placeholder="Password" type="password">
							</div>
							<div class="input-group bottom">
								<span class="input-group-addon bottom" id="confirm-password-addon"><i class="fa fa-fw fa-key"></i></span>
								<input name="password" class="form-control bottom" autocomplete="off" aria-label="Password" aria-describedby="confirm-password-addon" id="confirm-change-password" placeholder="Confirm password" type="password">
							</div>
							
							<br>
							
							<button class="btn btn-md btn-primary float-left" type="submit">Save changes</button>
						</form>
						<form id="account-delete-form" method="get" action="/auth/delete">
							<button class="btn btn-danger float-right" type="submit">Delete Account</button>
						</form>
					</div>
				</section>
				<h2>Current logins</h2>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Browser Information</th>
								<!--<th>ID</th>-->
								<th>Time Generated</th>
								<th>Expires</th>
								<th>IP</th>
								<th>Location</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php	
								$account->printAccountLogins();
							?>
						</tbody>
					</table>
					
					<h6><a href="javascript:logoutAll();">Logout all</a></h6>
				</div>
				<br>
				<h2>Latest account activity</h2>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Action</th>
								<th>Time</th>
								<th>IP</th>
								<th>Location</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$activity->printAccountActivity(10);
							?>
						</tbody>
					</table>
					
					<h6><a href="javascript:alert('soon');">See all</a></h6>
					<br />
				</div>
			</main>
		</div>
	</div>
	
	<?php include '../../php/templates/basicscripts.php'; ?>
	<script src="../../assets/js/jquery-ui.min.js"></script>
	<script src="account.js"></script>
</body>
</html>
