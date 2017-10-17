<?php
	ob_start();
	
	session_start();
	
	try
	{
		include_once "config.php";
		include_once "php/Database.php";
		include_once "php/Auth.php";
		
		$database = new Database($webconfig["database"]);
		$auth = new Auth($database);
	}
	catch(\Exception $e)
	{
		// don't fail on these because it's our homepage
		error_log($e);
	}
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Alexa REST API</title>
	
	<?php include 'php/templates/basichead.php'; ?>
	
	<link href="/assets/css/jumbotron.css" rel="stylesheet">
</head>
<body>
	<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
		<a class="navbar-brand" href="/">Alexa REST API</a>
		<button aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation" class="navbar-toggler" data-target="#navbarsExampleDefault" data-toggle="collapse" type="button">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarsExampleDefault">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
					<a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/contact">Contact</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/dashboard">Dashboard</a>
				</li>
			</ul>
			<?php
				try
				{
					include "php/templates/navarea.php";
				}
				catch (Exception $e)
				{
					error_log($e);
				}
			?></div>
	</nav><!-- Main jumbotron for a primary marketing message or call to action -->
	<div class="jumbotron">
		<div class="container">
			<h1 class="display-3">Alexa REST API</h1>
			<p>Alexa REST API is a service that allows you to connect your Amazon Alexa&reg; device to anything else you can think of.
			It integrates as a Smart Home Device that you control, giving you the ability to define your own actions, and forwards all requests you make to any RESTful API.</p>
			<p><a class="btn btn-primary btn-lg" href="/dashboard" role="button">Get started &raquo;</a></p>
		</div>
	</div>
	<div class="container">
		<!-- Example row of columns -->
		<div class="row">
			<div class="col-md-4">
				<h2>Heading</h2>
				<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui.</p>
				<p><a class="btn btn-secondary" href="#" role="button">View details &raquo;</a></p>
			</div>
			<div class="col-md-4">
				<h2>Heading</h2>
				<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui.</p>
				<p><a class="btn btn-secondary" href="#" role="button">View details &raquo;</a></p>
			</div>
			<div class="col-md-4">
				<h2>Heading</h2>
				<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
				<p><a class="btn btn-secondary" href="#" role="button">View details &raquo;</a></p>
			</div>
		</div>
		<hr>
		<footer>
			<p>&copy; Hayden Andreyka 2017&nbsp;<span class="text-muted">|</span>&nbsp;<a href="/privacypolicy" target="_blank">Privacy Policy</a></p>
		</footer>
	</div>
	
	<?php include 'php/templates/basicscripts.php'; ?>
</body>
</html>