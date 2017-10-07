<?php
	ob_start();
	
	session_start();
	
	include_once __DIR__."/../config.php";
	include_once __DIR__."/../php/Database.php";
	include_once __DIR__."/../php/Auth.php";
	
	$database = new Database($webconfig["database"]);
	$auth = new Auth($database);
	
	// if user is not logged in
	if (!$auth->checkUserIsLoggedIn()) {
		header('Location: /auth?redirect=%2Fdashboard%2F');
		exit();
	}
	
	function createItemCell($content, $before = null, $after = null) {
		return sprintf("<td>$before%s$after</td>", $content);
	}
?>
