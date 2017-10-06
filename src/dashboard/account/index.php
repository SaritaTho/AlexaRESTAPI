<?php
	include "../commoncode.php";
	include_once "../../config.php";
	include_once "../../php/Database.php";
	include_once "../../php/Utility.php";
	include_once "account.php";
	
	$database = new Database($webconfig["database"]);
	$account = new Account($database);
	
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
							
								try {
									$logins = $account->getAccountLogins($_SESSION["userid"]);
									
									foreach($logins as $login_item) {
										createItemRow($login_item);
									}
									
									unset($login_item);
									
								} catch (Exception $e) {
									echo "<tr><td>Database error</td></tr>" . $e->getMessage();
								}
								
								function createItemRow($login_item) {
									global $webconfig;
									
									$row = "<tr id=\"" . $login_item["hash"] . "\">";
									
									$you = $login_item["token"] == $_SESSION["token"];	// whether this is the current session, aka "you"
									
									$row .= // browser
											createItemCell($login_item["browser"] ?? "(Unknown)", null, ($you ? " <b>(you)</b>" : null)) .
											
											// token hash
											/*createItemCell($login_item["hash"], "<code>", "</code>") .*/
											
											// time generated
											createItemCell(Utility::getPrettyTime($login_item["timegenerated"])) .
											
											// expiry time
											createItemCell(Utility::getPrettyTime($login_item["expiry"]));
											
											// ip address
											if (isset($login_item["ip"])) {	// check if ip address exists
												$row .= createItemCell(
													$login_item["ip"], "<a href=\"" . sprintf($webconfig["geoip"]["browser-uri"],	// text
													$login_item["ip"]) . "\" target=\"_blank\">", "</a>");							// link
											} else {	// ip doesn't exist
												$row .= createItemCell("(Unknown)");
											}
											
											// location
									$row .=	createItemCell($login_item["location"] ?? "(Unknown)") .
											
											// actions
											createItemCell("<a href=\"javascript:logoutToken('" . $login_item["hash"] . "');\">Logout</a>");
											
									$row .= "</tr>";
									
									echo $row;
									
									if ($you) {
										// add in handler for refreshing the page if the current session gets deleted
										echo "<script src=\"rowlistener.php?hash=" . $login_item["hash"] . "\" defer></script>";
									}
								}
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
							<tr>
								<td>Email change</td>
								<td>8:26 PM 5/9/2017</td>
								<td><a href="#">56.104.98.32</a></td>
								<td>Mesa, Arizona, United States</td>
							</tr>
							<tr>
								<td>Password change</td>
								<td>3:02 AM 5/6/2017</td>
								<td><a href="#">2600:8800:2c05:ea00:290e:cf57:e2dd:71e6</a></td>
								<td>California, United States</td>
							</tr>
						</tbody>
					</table>
					
					<h6><a href="javascript:alert('soon');">See all</a></h6>
				</div>
			</main>
		</div>
	</div>
	
	<?php include '../../php/templates/basicscripts.php'; ?>
	<script src="../../assets/js/jquery-ui.min.js"></script>
	<script src="account.js"></script>
</body>
</html>
