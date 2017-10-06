<?php
	include "../commoncode.php";
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Alexa REST API Dashboard</title>
	
	<?php include '../../php/templates/basichead.php';
	include '../commonhead.php'; ?>
	
	<link href="/assets/css/dashboard.css" rel="stylesheet">
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
						<a class="nav-link active" href="../devices">Devices <span class="sr-only">(current)</span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="../logs">Logs</a>
					</li>
				</ul>
				<ul class="nav nav-pills flex-column">
					<li class="nav-item">
						<a class="nav-link" href="../account">Account</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="/contact">Support</a>
					</li>
				</ul>
			</nav>
			<main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
				<h1>Devices</h1>
				this isn't done ok
				<h2>Device list or something</h2>
				<div class="table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Device ID</th>
								<th>Friendly Name</th>
								<th>Control Event</th>
								<th>Time</th>
								<th>Log Event</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>2</td>
								<td>Laptop</td>
								<td>turnOn</td>
								<td>8:26 PM 5/9/17</td>
								<td><a href="#">Log</a></td>
							</tr>
						</tbody>
					</table>
				</div>
			</main>
		</div>
	</div>
	
	<?php include '../../php/templates/basicscripts.php'; ?>
</body>
</html>
