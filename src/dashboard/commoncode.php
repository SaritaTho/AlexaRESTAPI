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
	
	function createIpCell($ip) {
		global $webconfig;
		
		if (isset($ip)) {	// check if ip address exists
			return createItemCell(
				$ip, "<a href=\"" . sprintf($webconfig["geoip"]["browser-uri"],	// text
				$ip) . "\" target=\"_blank\">", "</a>");							// link
		} else {	// ip doesn't exist
			return createItemCell("(Unknown)");
		}
	}
?>
