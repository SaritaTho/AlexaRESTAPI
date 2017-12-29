<?php
	try {
		include_once "../config.php";
		include_once "../php/Database.php";
		include_once "../php/Auth.php";
		include_once "../php/OAuth.php";
		
		$database = new Database($webconfig["database"]);
		$auth = new Auth($database);
		$oauth = new OAuth($database);
	}
	catch (\Exception $e)
	{
		error_log($e);
		http_response_code(500);
		exit();
	}

