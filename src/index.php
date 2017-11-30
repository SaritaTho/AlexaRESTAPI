<?php
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
	</nav>
	<div class="jumbotron">
		<div class="container">
			<h1 class="display-3">Alexa REST API</h1>
			<p class="bigger">Alexa REST API is a service that allows you to connect your Amazon Alexa&reg; device to anything else you can think of.<br>
			It integrates as a Smart Home Device that you control, giving you the ability to define your own actions, and forwards all of your requests to any RESTful API you choose.</p>
			<p><a class="btn btn-primary btn-lg" href="/dashboard" role="button">Get started &raquo;</a></p>
		</div>
	</div>
	<div class="container">
		<!-- Example row of columns -->
		<div class="row">
			<div class="col-md-4">
				<h2>You're in control</h2>
				<p>Alexa REST API acts like a bridge between Alexa and your own systems - we provide a simple interface for you to define your actions, and send all your requests wherever you choose. Requests can be formatted as JSON or as a URL encoded form.</p>
			</div>
			<div class="col-md-4">
				<h2>Easy to use</h2>
				<p>This service is designed for programmers, and those who already know how to interface with an HTTP API.<br>
				However, by standardizing a format for requests to be sent from Alexa, it's now easier than ever for third party utilities to connect to Alexa, without having to create their own Skill.</p>
			</div>
			<div class="col-md-4">
				<h2>Open source</h2>
				<p>Want to host Alexa REST API yourself for private use? Go ahead! We just ask that you follow the guidelines listed in the installation instructions.</p>
				<p><a class="btn btn-secondary" href="https://github.com/Technoguyfication/alexa-rest-api" target="_blank" role="button">Alexa REST API on Github &raquo;</a></p>
			</div>
		</div>
		<hr>
		<footer>
			<p>&copy; Hayden Andreyka 2017&nbsp;<span class="text-muted">|</span>&nbsp;<a href="/privacypolicy" target="_blank">Privacy and Cookie Policy</a></p>
		</footer>
	</div>
	
	<?php include 'php/templates/basicscripts.php'; ?>
</body>
</html>