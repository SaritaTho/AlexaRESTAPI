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
	
	// make a place for us to put relevant request data
	$request;
	var_dump($_SERVER["REQUEST_METHOD"]);
	var_dump($_POST);
	
	var_dump($auth->checkUserEnvironment());
	
	// get or post?
	if ($_SERVER["REQUEST_METHOD"] == "GET")
	{
		$request = $_GET;
	} else if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$request = $_POST;
	} else {
		// invalid method
		http_response_code(400);
		echo "Invalid request method: " . $_SERVER["REQUEST_METHOD"];
		exit();
	}
?>
