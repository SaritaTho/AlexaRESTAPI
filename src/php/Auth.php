<?php

include_once __DIR__ . "/Database.php";
include_once __DIR__ . "/Utility.php";
include_once __DIR__ . "/User.php";
include_once __DIR__ . "/thirdparty/BrowserDetection.php";

class Auth {

    /**
     * 
     * @var Database The database connection to use
     */
    private $database;

    public function __construct($dbh) {
	if (!isset($dbh)) {
	    throw new Exception("No database provided");
	}

	$this->database = $dbh;
    }

    /**
     * Creates a user token
     * @param int $userid
     * @param type $lifespan
     * @return type
     * @throws Exception
     */
    public function createUserToken($userid, $lifespan) {
	if (is_null($userid)) {
	    throw new Exception("Userid cannot be null");
	}
	if (is_null($lifespan)) {
	    throw new Exception("Lifespan cannot be null");
	}

	// generate 60 character long string for the token
	$newtoken = bin2hex(openssl_random_pseudo_bytes(30));
	$timegenerated = time();
	$expiry = $timegenerated + $lifespan;
	$tokenhash = substr(hash("sha256", $newtoken . $userid), 0, 10); // truncate to 10 characters
	$ip = null; // ip may be null in some cases
	if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
	    $ip = $_SERVER["REMOTE_ADDR"];
	}
	$geolocation = null;
	if (isset($ip)) {
	    try {
		$geolocation = Utility::getGeolocation($ip);
	    } catch (Exception $e) {
		
	    } // geolocation isn't super important
	}
	$browser_raw = new BrowserDetection();
	// ex. "Firefox on Windows (Desktop)" or "Safari on iPhone (Mobile)"
	$browser = $browser_raw->getName() . " on " . $browser_raw->getPlatform() . " (" . ($browser_raw->isMobile() ? "Mobile" : "Desktop") . ")";

	// send the query to the database
	$this->database->pquery("INSERT INTO `logintokens` (`userid`, `token`, `expiry`, `hash`, `timegenerated`, `ip`, `location`, `browser`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [$userid, $newtoken, $expiry, $tokenhash, $timegenerated, $ip, $geolocation, $browser]);

	return $newtoken;
    }

    /*
      Creates a user account
     */

    public function createUserLogin($email, $password) {
	if (is_null($email))
	    throw new Exception("Email cannot be null");
	if (is_null($password))
	    throw new Exception("Password cannot be null");

	// generate password hash
	$password_hash = self::generatePasswordHash($password);

	// create user account
	$this->database->pquery("INSERT INTO `users` (`email`, `password`, `registered`) VALUES (?, ?, ?);", [$email, $password_hash, time()]);

	// add item to account history for account create
	try {
	    // get user id back
	    $results = $this->database->pquery("SELECT `userid` FROM `users` WHERE `email` = ?;", [$email])->fetchAll();
	    $userid = $results[0]["userid"];

	    $this->addAccountHistoryItem($userid, 0);
	} catch (\Exception $e) { // this isn't crucial so we can handle the exception without notifying the user
	    error_log("Error creating account history item for user $userid: $e");
	}
    }

    /*
      Generate a hash from a password string

      Returns: Password hash string
     */

    public static function generatePasswordHash($password) {
	return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 
     * @param type $checkcookies Whether to check cookies for login
     * @param type $token
     * @return boolean Whether the user is logged in
     */
    public function checkUserIsLoggedIn($checkcookies = true, $token = null) {
	if ($session == null) { // default session
	    $session = $_SESSION;
	}

	if ($cookies == null) { // default cookies
	    $cookies = filter_input_array($_COOKIE);
	}

	// check manually entered token
	if (isset($token)) {
	    $result = $this->checkUserToken($token);
	    if ($result)
		return $result;
	}

	// check session token
	$result = $this->checkTokenRaw($session);
	if ($result)
	    return $result;

	// check cookies
	if ($checkcookies) {
	    $result = $this->checkTokenRaw($cookies);
	    if ($result)
		return $result;
	}

	// no dice :(
	return false;
    }

    /*
      Checks a token as a child of an object

      Returns: Boolean
     */

    private function checkTokenRaw($tokenobject, $key = "token") {
	if (is_null($tokenobject)) // token object not set
	    return false;

	if (!array_key_exists($key, $tokenobject)) // token doesn't exist
	    return false;

	$token = $tokenobject[$key];

	if (is_null($token))
	    return false;

	if ($this->checkUserToken($token)) {  // is the token valid?
	    if (!$this->checkUserEnvironment())  // set up the user environment so session vars survive
		$this->setupUserEnvironment($this->getUserFromToken($token));

	    return $token;
	} else
	    return false;
    }

    /*
      Checks whether a user login token is valid

      Returns: Boolean
     */

    public function checkUserToken($token) {
	if (is_null($token))
	    throw new Exception("Token cannot be null");

	$results = $this->database->pquery("SELECT `token` from `logintokens` WHERE `token` = ? AND `expiry` > ? LIMIT 1", [$token, time()])->fetchAll();

	// any tokens active?
	if (count($results) == 0) {
	    return false;
	} else {
	    return true;
	}
    }

    /*
      Checks whether a pair of credentials are valid

      Returns: Boolean
     */

    public function checkUserCredentials($email, $password) {
	if (is_null($email))
	    throw new Exception("Email cannot be null");
	if (is_null($password))
	    throw new Exception("Password cannot be null");

	// query gather password hashes from matching users
	$results = $this->database->pquery("SELECT `password` FROM `users` WHERE `email` = ? LIMIT 1", [$email])->fetchAll();

	// no matching accounts on record
	if (count($results) == 0) {
	    return false;
	}

	// verify hashed password for user
	if (password_verify($password, $results[0]["password"])) {
	    return true; // password verified, user is good
	} else {
	    return false; // password incorrect
	}
    }

    /*
      Logs a user in to the current session

      Returns: null
     */

    public function login($userid) {
	if (is_null($userid))
	    throw new Exception("User ID cannot be null");

	global $webconfig;

	// if user is already logged in, logout before continuing
	$loggedin = $this->checkUserIsLoggedIn(); // returns token on valid login
	if ($loggedin) {
	    $this->logout($loggedin);
	    $this->destroySession();
	}

	// generate new token using ID
	$newtoken = $this->createUserToken($userid, $webconfig["authentication"]["login-lifetime"]);
	$email = $this->getUserEmail($userid);

	$user = new User($userid, $email, $newtoken);

	if (session_status() == PHP_SESSION_NONE)
	    session_start();

	$this->setupUserEnvironment($user);

	// verify that session is valid
	if (!$this->checkUserIsLoggedIn()) {
	    throw new Exception("Failed to verify login");
	}
    }

    /*
      Checks that the user's "environment" (cookies, php session) is valid

      Returns: Boolean
     */

    public function checkUserEnvironment() {
	if (!isset($_SESSION)) {
	    return false;
	}

	$sess = $_SESSION;

	if (!array_key_exists("token", $sess) || !array_key_exists("email", $sess) || !array_key_exists("userid", $sess)) {
	    return false;
	}
    }

    /*
      Sets up the user's "environment" (cookies, php session)

      Returns: null
     */

    public function setupUserEnvironment($user) {
	global $webconfig;

	if (is_null($user))
	    throw new Exception("User cannot be null");

	if (session_status() == PHP_SESSION_NONE)
	    session_start();

	$_SESSION["token"] = $user->getToken();
	$_SESSION["email"] = $user->getEmail();
	$_SESSION["userid"] = $user->getUserId();

	// assign token to a cookie
	setcookie("token", $user->getToken(), time() + $webconfig["authentication"]["login-lifetime"], "/", $webconfig["host"]["domain"], $webconfig["host"]["ssl-only"], false);
    }

    /*
      Voids a user's login token

      Returns: null
     */

    public function logout($token) {
	if (is_null($token))
	    throw new Exception("Token cannot be null");

	// void token
	$this->database->pquery("DELETE FROM `logintokens` WHERE `token` = ?", [$token]);
    }

    /*
      Reset's a user's session completely.
      This does not void the user's token

      Returns: null
     */

    public function destroySession() {
	unset($_SESSION["token"]);
	session_unset();
	session_destroy();

	if (session_status() == PHP_SESSION_NONE)
	    session_start();
    }

    // logs out all sessions, second param decides whether or not to logout current session
    public function logoutAll($userid) {
	// delete all of a user's tokens
	$this->database->pquery("DELETE FROM `logintokens` WHERE `userid` = ?", [$userid]);
    }

    // gets an account id based on an email
    public function getUserId($email) {
	if (is_null($email))
	    throw new Exception("Email cannot be null");

	// grab user id from database based on email
	$results = $this->database->pquery("SELECT `userid` FROM `users` WHERE `email` = ? LIMIT 1", [$email])->fetchAll();

	// no accounts returned, likely doesn't exist
	if (count($results) == 0) {
	    throw new Exception("Account not found");
	}

	return $results[0]["userid"];
    }

    // get email from token
    public function getUserEmail($userid) {
	if (is_null($userid))
	    throw new Exception("Userid cannot be null");

	// grab email from database
	$results = $this->database->pquery("SELECT `email` FROM `users` WHERE `userid` = ? LIMIT 1", [$userid])->fetchAll();

	if (count($results) == 0) {
	    throw new Exception("Account not found");
	}

	return $results[0]["email"];
    }

    // gets user info from a token
    public function getUserFromToken($token) {
	// get userid
	$results = $this->database->pquery("SELECT `userid` FROM `logintokens` WHERE `token` = ? LIMIT 1", [$token])->fetchAll();

	// no users returned
	if (count($results) == 0) {
	    throw new Exception("User not found");
	}

	$userid = $results[0]["userid"];
	$email = $this->getUserEmail($userid);

	return new User($userid, $email, $token);
    }

    // gets a user object based on userid - token will not be populated
    public function getUserFromUserId($userid) {
	// query database for user email
	$results = $this->database->pquery("SELECT `email` FROM `users` WHERE `userid` = ? LIMIT 1", [$userid])->fetchAll();

	// no users returned
	if (count($results) == 0) {
	    throw new Exception("User not found");
	}

	$email = $results[0]["email"];

	return new User($userid, $email, null); // token is null
    }

    // change an account password
    public function changeAccountPassword($userid, $newpassword) {
	if (is_null($userid))
	    throw new Exception("Userid cannot be null");
	if (is_null($newpassword))
	    throw new Exception("New password cannot be null");

	$password_hash = generatePasswordHash($newpassword);

	// update password in database
	$this->database->pquery("UPDATE `users` SET `password` = ? WHERE `userid` = ? LIMIT 1", [$password_hash, $userid]);

	// account history for password change
	$this->addAccountHistoryItem($userid, 2);
    }

    // change a user's email
    public function changeAccountEmail($userid, $newemail) {
	if (is_null($userid))
	    throw new Exception("Userid cannot be null");
	if (is_null($newemail))
	    throw new Exception("New email cannot be null");

	// update email in db
	$this->database->pquery("UPDATE `users` SET `email` = ? WHERE `userid` = ? LIMIT 1", [$newemail, $userid]);

	// add account history item for email
	$this->addAccountHistoryItem($userid, 1);
    }

    // add an item to the account security history
    /*
      action mappings:

      0: account creation
      1: email change
      2: password change

     */
    public function addAccountHistoryItem($userid, $action, $time = null, $ip = null) {
	if (is_null($userid))
	    throw new Exception("Userid cannot be null");
	if (is_null($action))
	    throw new Exception("Action cannot be null");

	// default for ip
	if ($ip == null) {
	    $ip = $_SERVER["REMOTE_ADDR"];
	}

	// default for time
	if ($time == null) {
	    $time = time();
	}

	// lookup geolocation from ip
	$geolocation = null;
	if (isset($ip)) {
	    try {
		$geolocation = Utility::getGeolocation($ip);
	    } catch (Exception $e) {
		
	    } // geolocation isn't super important kaaaay
	}

	// add item to database
	$this->database->pquery("INSERT INTO `accounthistory` (`userid`, `action`, `time`, `ip`, `location`) VALUES (?, ?, ?, ?, ?)", [$userid, $action, $time, $ip, $geolocation]);
    }

}
