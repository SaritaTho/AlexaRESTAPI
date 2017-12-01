<?php

include_once __DIR__ . "/Database.php";
include_once __DIR__ . "/Utility.php";
include_once __DIR__ . "/User.php";
include_once __DIR__ . "/thirdparty/BrowserDetection.php";

/**
 * Handles all authentication to the website
 */
class Auth {

    /**
     * 
     * @var \Database The database connection to use
     */
    private $database;

    /**
     * 
     * @param \Database $dbh Database connection
     * @throws Exception
     */
    public function __construct($dbh) {
		if (!isset($dbh)) {
			throw new Exception("No database provided");
		}

		$this->database = $dbh;
    }

    /**
     * Creates a login token for a user account
     * 
     * @param int $userid User to create token for
     * @param int $lifespan Life of token, in seconds
     * @return string Generated login token
     * @throws Exception
     */
    public function createUserToken($userid, $lifespan) {
		// check parameters
		if (is_null($userid)) {
			throw new Exception("Userid cannot be null");
		}
		if (is_null($lifespan)) {
			throw new Exception("Lifespan cannot be null");
		}

		// generate 60 character long string for the token
		$token = bin2hex(openssl_random_pseudo_bytes(30));

		// Calculate token timestamps
		$timegenerated = time();
		$expiry = $timegenerated + $lifespan;

		// Calculate hash of token for user
		$tokenhash = substr(hash("sha256", $token . $userid), 0, 10); // truncate to 10 characters
		// Get IP
		$ip = null; // ip may be null in some cases
		if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
			$ip = filter_var($_SERVER["REMOTE_ADDR"], FILTER_SANITIZE_STRING);
		}

		// Get IP Geolocation
		$geolocation = null;
		if (!empty($ip)) {
			try {
			$geolocation = Utility::getGeolocation($ip);
			} catch (\Exception $e) {
			
			} // geolocation isn't super important
		}

		// Get browser info
		$browser_raw = new BrowserDetection();
		// ex. "Firefox on Windows (Desktop)" or "Safari on iPhone (Mobile)"
		$browser = $browser_raw->getName() . " on " . $browser_raw->getPlatform() . " (" . ($browser_raw->isMobile() ? "Mobile" : "Desktop") . ")";

		// send the query to the database
		$this->database->pquery("INSERT INTO `logintokens` (`userid`, `token`, `expiry`, `hash`, `timegenerated`, `ip`, `location`, `browser`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [$userid, $token, $expiry, $tokenhash, $timegenerated, $ip, $geolocation, $browser]);

		return $token;
    }

    /**
     * Creates a user account
     *
     * @param string $email Email address for the account
     * @param string $password Password for the account
     * @return void
     */
    public function createUserLogin($email, $password) {
		if (is_null($email)) {
			throw new Exception("Email cannot be null");
		}
		if (is_null($password)) {
			throw new Exception("Password cannot be null");
		}

		// generate password hash
		$password_hash = self::generatePasswordHash($password);

		// create user account
		$this->database->pquery("INSERT INTO `users` (`email`, `password`, `registered`) VALUES (?, ?, ?);", [$email, $password_hash, time()]);

		// add item to account history for account create
		try {
			// get user id back
			$results = $this->database->pquery("SELECT `userid` FROM `users` WHERE `email` = ?;", [$email])->fetchAll();
			$userid = $results[0]["userid"];

			$this->addAccountHistoryItem($userid, HistoryItems::ACCOUNT_CREATE);
		} catch (\Exception $e) { // this isn't crucial so we can handle the exception without notifying the user
			error_log("Error creating account history item for user $email: $e");
		}
    }

    /**
     * Hashes a password
     * 
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function generatePasswordHash($password) {
		return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Checks whether the current environment is logged in
     * 
     * @param boolean $use_cookies Whether to check cookies for login
     * @return boolean Whether the user is logged in
     */
    public function checkUserIsLoggedIn() {
		return ($this->checkUserToken($this->getEnvironmentToken()));
    }

    /**
     * Checks a user login token
     * 
     * @param string $token User login token
     * @return boolean Whether token is valid or not
     */
    private function checkUserToken($token) {
		if (!isset($token)) {
			return false;
		}

		$results = $this->database->pquery("SELECT `token` from `logintokens` WHERE `token` = ? AND `expiry` > ? LIMIT 1", [$token, time()])->fetchAll();

		// any tokens active?
		if (count($results) == 0) {
			return false;
		} else {
			return true;
		}
    }

    /**
     * Checks whether a pair of credentials are valid
     * 
     * @param string $email Email address to check
     * @param string $password Password to check
     * @return boolean Whether the credentials are valid
     * @throws Exception
     */
    public function checkUserCredentials($email, $password) {
		if (is_null($email)) {
			throw new Exception("Email cannot be null");
		}
		if (is_null($password)) {
			throw new Exception("Password cannot be null");
		}

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

    /**
     * Logs a user into the current environment
     * 
     * @global array $webconfig
     * @param int $userid
     * @throws Exception
     */
    public function login($userid) {
		if (is_null($userid)) {
			throw new Exception("User ID cannot be null");
		}

		global $webconfig;

		// if user is already logged in, logout before continuing
		if ($this->checkUserIsLoggedIn()) {
			$this->logout($this->getEnvironmentToken());
			$this->destroySession();
		}

		// generate new token using ID
		$newtoken = $this->createUserToken($userid, $webconfig["authentication"]["login-lifetime"]);
		$user = $this->getUserFromUserId($userid);
		$user->setToken($newtoken);

		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}

		$this->setupUserEnvironment($user);
		
		// verify that session is valid
		if (!$this->checkUserIsLoggedIn()) {
			throw new Exception("Failed to verify login");
		}
    }

   /**
    * Checks whether the user's environment is valid
    * 
    * @return boolean
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

    /**
     * Sets up the user's environment
     * 
     * @global array $webconfig
     * @param \User $user
     * @throws Exception
     */
    public function setupUserEnvironment($user) {
		global $webconfig;
		
		if (is_null($user)) {
			throw new Exception("User cannot be null");
		}

		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}

		$_SESSION["token"] = $user->getToken();
		$_SESSION["email"] = $user->getEmail();
		$_SESSION["userid"] = $user->getUserId();

		// assign token to a cookie
		setcookie("token", $user->getToken(), time() + $webconfig["authentication"]["login-lifetime"], "/", $webconfig["host"]["domain"], $webconfig["host"]["ssl-only"], false);
    }

    /**
     * Voids a user login token
     * 
     * @param string $token
     * @throws Exception
     */
    public function logout($token) {
		if (is_null($token)) {
			throw new Exception("Token cannot be null");
		}

		// void token
		$this->database->pquery("DELETE FROM `logintokens` WHERE `token` = ?", [$token]);
		}

		/**
		 * Destroys a user's session. This does not void their login token.
		 */
		public function destroySession() {
		unset($_SESSION["token"]);
		session_unset();
		session_destroy();

		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
    }

    /**
     * Logs out all session for a user
     * 
     * @param type $userid User ID to clear sessions for
     */
    public function logoutAll($userid) {
		// delete all of a user's tokens
		$this->database->pquery("DELETE FROM `logintokens` WHERE `userid` = ?", [$userid]);
    }

    /**
     * Gets the User for an email address
     * 
     * @param string $email Email address
     * @return \User User associated with email
     * @throws Exception
     */
    public function getUserFromEmail($email) {
		if (is_null($email)) {
			throw new Exception("Email cannot be null");
		}

		// grab user id from database based on email
		$results = $this->database->pquery("SELECT `userid` FROM `users` WHERE `email` = ? LIMIT 1", [$email])->fetchAll();

		// no accounts returned, likely doesn't exist
		if (count($results) == 0) {
			throw new Exception("Account not found");
		}

		$userid = $results[0]["userid"];
		
		$user = new User();
		$user->setUserId($userid)->setEmail($email);
		
		return $user;
    }

    /**
     * Gets a User from a login token, throws exception if user does not exist
     * 
     * @param string $token Login token
     * @return \User User associated with the token
     * @throws Exception
     */
    public function getUserFromToken($token) {
		// get userid
		$results = $this->database->pquery("SELECT `userid` FROM `logintokens` WHERE `token` = ? LIMIT 1", [$token])->fetchAll();

		// no users returned
		if (count($results) == 0) {
			throw new Exception("User not found");
		}

		$userid = $results[0]["userid"];
		$email = $this->getUserEmail($userid);

		$user  = new User();
		$user->setUserId($userid)->setEmail($email)->setToken($token);
		
		return $user;
    }

    /**
     * Gets a User from a User ID, throws exception if User does not exist.
     * 
     * @param int $userid User ID
     * @return \User User of ID, token property will be left null
     * @throws Exception
     */
    public function getUserFromUserId($userid) {
		// query database for user email
		$results = $this->database->pquery("SELECT `email` FROM `users` WHERE `userid` = ? LIMIT 1", [$userid])->fetchAll();

		// no users returned
		if (count($results) == 0) {
			throw new Exception("User not found");
		}

		$email = $results[0]["email"];

		$user = new User();
		$user->setEmail($email)->setUserId($userid); // token is null

		return $user;
    }

    /**
     * Gets the login token for the current user
     * 
     * @return string User login token
     * @return null For when no token was found
     */
    public function getEnvironmentToken() {
		if (array_key_exists("token", $_SESSION)) {
			return $_SESSION["token"];
		}

		if (array_key_exists("token", $_COOKIE)) {
			return filter_var($_COOKIE["token"], FILTER_SANITIZE_STRING);
		}

		return null;
    }

    /**
     * Changes the password for a user
     * 
     * @param int $userid User to modify
     * @param string $newpassword The new password
     * @throws Exception
     */
    public function changeAccountPassword($userid, $newpassword) {
		if (is_null($userid)) {
			throw new Exception("Userid cannot be null");
		}
		if (is_null($newpassword)) {
			throw new Exception("New password cannot be null");
		}

		$password_hash = generatePasswordHash($newpassword);

		// update password in database
		$this->database->pquery("UPDATE `users` SET `password` = ? WHERE `userid` = ? LIMIT 1", [$password_hash, $userid]);

		// account history for password change
		$this->addAccountHistoryItem($userid, HistoryItems::PASSWORD_CHANGE);
    }

    /**
     * Changes a user's email address
     * 
     * @param int $userid User ID to modify
     * @param string $newemail New email address
     * @throws Exception
     */
    public function changeAccountEmail($userid, $newemail) {
		if (is_null($userid)) {
			throw new Exception("Userid cannot be null");
		}
		if (is_null($newemail)) {
			throw new Exception("New email cannot be null");
		}

		if (!filter_var($newemail, FILTER_VALIDATE_EMAIL)) {
			throw new Exception("Invalid email");
		}

		// update email in db
		$this->database->pquery("UPDATE `users` SET `email` = ? WHERE `userid` = ? LIMIT 1", [$newemail, $userid]);

		// add account history item for email
		$this->addAccountHistoryItem($userid, HistoryItems::EMAIL_CHANGE);
		}

		/**
		 * Adds an account history item for a user
		 * 
		 * @param int $userid User ID
		 * @param int $action Refer to HistoryItems
		 * @param int $time Epoch timestamp
		 * @param string $ip IP Address of user who caused it
		 * @throws Exception
		 */
		public function addAccountHistoryItem($userid, $action, $time = null, $ip = null) {
		if (is_null($userid)) {
			throw new Exception("Userid cannot be null");
		}
		if (is_null($action)) {
			throw new Exception("Action cannot be null");
		}

		// default for ip
		if ($ip == null) {
			$ip = filter_var($_SERVER["REMOTE_ADDR"], FILTER_SANITIZE_STRING);
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

class HistoryItems {

    /**
     * Account creation
     */
    const ACCOUNT_CREATE = 0;

    /**
     * Email change
     */
    const EMAIL_CHANGE = 1;

    /**
     * Password change
     */
    const PASSWORD_CHANGE = 2;
	
	public static function getFriendlyName($item) {
		switch ($item) {
			case self::ACCOUNT_CREATE:
				return "Account Creation";
			case self::EMAIL_CHANGE:
				return "Email Change";
			case self::PASSWORD_CHANGE:
				return "Password Change";
			default:
				return "Unknown";
		}
	}

}
