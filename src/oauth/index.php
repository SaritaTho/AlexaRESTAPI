<?php	
	// load stuff
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
	
	// user needs to be authenticated to do anything on this page
	if (!$auth->checkUserIsLoggedIn())
	{
		// have user sign in and then bring them back to this page
		header("Location: ../auth/?redirect=" . urlencode("/oauth/?" . $_SERVER["QUERY_STRING"]));
		exit();
	}
	
	
?>
