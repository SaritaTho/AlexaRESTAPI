<?php
	include_once __DIR__ . "/Database.php";
	include_once __DIR__ . "/Utility.php";
	include_once __DIR__ . "/User.php";
	include_once __DIR__ . "/thirdparty/BrowserDetection.php";
	
	class Auth {
		private $database;
		
		public function __construct($dbh) {
			if (!isset($dbh))
				throw new Exception("No database provided");
			
			$this->database = $dbh;
		}
		
		// generate a user login token
		public function generateUserToken($userid, $lifespan) {
			if (is_null($userid)) throw new Exception("Userid cannot be null");
			if (is_null($lifespan)) throw new Exception("Lifespan cannot be null");
			
			$stmt = $this->database->dbh->prepare("INSERT INTO `logintokens` (`userid`, `token`, `expiry`, `hash`, `timegenerated`, `ip`, `location`, `browser`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			
			// generate 60 character long string for the token
			$newtoken = bin2hex(openssl_random_pseudo_bytes(30));
			$timegenerated = time();
			$expiry = $timegenerated + $lifespan;
			$tokenhash = substr(hash("sha256", $newtoken . $userid), 0, 10);	// truncate to 10 characters
			$ip = null;	// ip may be null in some cases
			if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
				$ip = $_SERVER["REMOTE_ADDR"];
			}
			$geolocation = null;
			if (isset($ip)) {
				try {
					$geolocation = Utility::getGeolocation($ip);
				} catch (Exception $e) {}	// geolocation isn't super important
			}
			$browser_raw = new BrowserDetection();
			// ex. "Firefox on Windows (Desktop)" or "Safari on iPhone (Mobile)"
			$browser = $browser_raw->getName() . " on " . $browser_raw->getPlatform() . " (" . ($browser_raw->isMobile()?"Mobile":"Desktop") . ")";
						
			$result = $stmt->execute([$userid, $newtoken, $expiry, $tokenhash, $timegenerated, $ip, $geolocation, $browser]);
			
			if ($result == false) {
				throw new Exception("Query failed");
			}
			
			return $newtoken;
		}
		
		// create an email/password in the db
		public function generateUserLogin($email, $password) {
			if (is_null($email)) throw new Exception("Email cannot be null");
			if (is_null($password)) throw new Exception("Password cannot be null");
			
			// create user account
			$stmt = $this->database->dbh->prepare("INSERT INTO `users` (`email`, `password`, `registered`) VALUES (?, ?, ?)");
			$result = $stmt->execute([$email, $this->generatePasswordHash($password), time()]);
			
			if ($result == false) {
				throw new Exception("Failed to run creation query");
			}
			
			// log it
			// get user id back
			$stmt = $this->database->dbh->prepare("SELECT `userid` FROM `users` WHERE `email` = ?");
			$result = $stmt->execute([$email]);
			
			if ($result == false) {
				return;
			}
			
			$userid = $stmt->fetchAll()[0]["userid"];
			
			$this->addAccountHistoryItem($userid, 0);
		}
		
		// generate a hash for a password
		public function generatePasswordHash($password) {
			return password_hash($password, PASSWORD_DEFAULT);
		}
		
		// checks if a user is logged in and makes sure the environment is set if 
		// returns token if a token is found, false if user not logged in
		public function checkUserIsLoggedIn($checkcookies = true, $session = null, $cookies = null, $token = null) {
			if ($session == null)	// default session
				$session = $_SESSION;
			
			if ($cookies == null)	// default cookies
				$cookies = $_COOKIE;
			
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
		
		// checks a token from an object based on a key, then sets up the user environment if it doesn't exist
		private function checkTokenRaw($tokenobject, $key = "token") {
			if (is_null($tokenobject))	// token object not set
				return false;
			
			if (!array_key_exists($key, $tokenobject))	// token doesn't exist
				return false;
			
			$token = $tokenobject[$key];
			
			if (is_null($token))
				return false;
			
			if ($this->checkUserToken($token)) {		// is the token valid?
				if (!$this->checkUserEnvironment())		// set up the user environment so session vars survive
					$this->setupUserEnvironment($this->getUserFromToken($token));
				
				return $token;
			} else return false;
		}
		
		// check to see if a login token is valid ahainst the database
		public function checkUserToken($token) {
			if (is_null($token)) throw new Exception("Token cannot be null");
			
			$stmt = $this->database->dbh->prepare("SELECT `token` from `logintokens` WHERE `token` = ? AND `expiry` > ? LIMIT 1");
			$result = $stmt->execute([$token, time()]);
			
			// empty response - error
			if ($result == false) {
				return false;
			}
			
			// any tokens active?
			if (count($stmt->fetchAll()) == 0) {
				return false;
			} else {
				return true;
			}
		}
		
		// check user credentials against database
		public function checkUserCredentials($email, $password) {
			if (is_null($email)) throw new Exception("Email cannot be null");
			if (is_null($password)) throw new Exception("Password cannot be null");
			
			$stmt = $this->database->dbh->prepare("SELECT `password` FROM `users` WHERE `email` = ? LIMIT 1");
			$result = $stmt->execute([$email]);
			
			// empty response - error
			if ($result == false) {
				throw new Exception("Failed to query database");
			}
			
			$users = $stmt->fetchAll();
			
			// no accounts on record
			if (count($users) == 0) {
				return false;
			}
			
			// verify pass
			if (password_verify($password, $users[0]["password"])) {
				return true;
			} else {
				return false;
			}
		}
		
		// logs in a user onto the current session
		public function login($userid) {
			if (is_null($userid)) throw new Exception("User ID cannot be null");
			
			global $webconfig;
			
			// if user is already logged in, logout before continuing
			$loggedin = $this->checkUserIsLoggedIn();	// returns token on valid login
			if ($loggedin) {
				$this->logout($loggedin);
				$this->destroySession();
			}
			
			// generate new token using ID
			$newtoken = $this->generateUserToken($userid, $webconfig["authentication"]["login-lifetime"]);
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
		
		// checks to see if a user's environment is valid
		public function checkUserEnvironment() {
			if (!isset($_SESSION)) {
				return false;
			}
			
			$sess = $_SESSION;
			
			if (!array_key_exists("token", $sess) || !array_key_exists("email", $sess) || !array_key_exists("userid", $sess)) {
				return false;
			}
		}
		
		// sets up all the user variables needed
		public function setupUserEnvironment($user) {
			global $webconfig;
			
			if (is_null($user)) throw new Exception("User cannot be null");
			
			if (session_status() == PHP_SESSION_NONE)
				session_start();
			
			$_SESSION["token"] = $user->getToken();
			$_SESSION["email"] = $user->getEmail();
			$_SESSION["userid"] = $user->getUserId();
			
			// assign token to a cookie
			setcookie("token", $user->getToken(), time()+$webconfig["authentication"]["login-lifetime"], "/", $webconfig["host"]["domain"], $webconfig["host"]["ssl-only"], false);
		}
		
		// void a login token and destroy session
		public function logout($token) {
			if (is_null($token)) throw new Exception("Token cannot be null");
			
			// void token
			$stmt = $this->database->dbh->prepare("DELETE FROM `logintokens` WHERE `token` = ?");
			$stmt->execute([$token]);
		}
		
		public function destroySession() {
			unset($_SESSION["token"]);
			session_unset();
			session_destroy();
			
			if (session_status() == PHP_SESSION_NONE)
				session_start();
		}
		
		// logs out all sessions, second param decides whether or not to logout current session
		public function logoutAll($userid) {
			$stmt = $this->database->dbh->prepare("DELETE FROM `logintokens` WHERE `userid` = ?");
			$result = $stmt->execute([$userid]);
			
			if ($result == false) {
				throw new Exception("Database query failed");
			}
		}
		
		// gets an account id based on an email
		public function getUserId($email) {
			if (is_null($email)) throw new Exception("Email cannot be null");
			
			$stmt = $this->database->dbh->prepare("SELECT `userid` FROM `users` WHERE `email` = ? LIMIT 1");
			$result = $stmt->execute([$email]);
			
			if ($result == false) {
				throw new Exception("Database error");
			}
			
			$results = $stmt->fetchAll();
			
			// no accounts returned, likely doesn't exist
			if (count($results) == 0) {
				throw new Exception("Account not found");
			}
			
			return $results[0]["userid"];
		}
		
		// get email from token
		public function getUserEmail($userid) {
			if (is_null($userid)) throw new Exception("Userid cannot be null");
			
			$stmt = $this->database->dbh->prepare("SELECT `email` FROM `users` WHERE `userid` = ? LIMIT 1");
			$result = $stmt->execute([$userid]);
			
			if ($result == false) {
				throw new Exception("Database query failed");
			}
			
			$results = $stmt->fetchAll();
			
			if (count($results) == 0) {
				throw new Exception("Account not found");
			}
			
			return $results[0]["email"];
		}
		
		// gets user info from a token
		public function getUserFromToken($token) {
			// get userid
			$stmt = $this->database->dbh->prepare("SELECT `userid` FROM `logintokens` WHERE `token` = ? LIMIT 1");
			$result = $stmt->execute([$token]);
			
			if ($result == false) {
				throw new Exception("Database query failed");
			}
			
			$results = $stmt->fetchAll();
			
			if (count($results) == 0) {
				throw new Exception("User not found");
			}
			
			$userid = $results[0]["userid"];
			
			$email = $this->getUserEmail($userid);
			
			return new User($userid, $email, $token);
		}
		
		// gets a user object based on userid - token will not be populated
		public function getUserFromUserId($userid) {
			$stmt = $this->database->dbh->prepare("SELECT `email` FROM `users` WHERE `userid` = ? LIMIT 1");
			$result = $stmt->execute([$userid]);
			
			if ($result == false) {
				throw new Exception("Failed to query database");
			}
			
			$results = $stmt->fetchAll();
			
			if (count($results) == 0) {
				throw new Exception("User not found");
			}
			
			$email = $results[0]["email"];
			
			return new User($userid, $email, null);	// token is null
		}
		
		// change an account password
		public function changeAccountPassword($userid, $newpassword) {
			if (is_null($userid)) throw new Exception("Userid cannot be null");
			if (is_null($newpassword)) throw new Exception("New password cannot be null");
			
			$stmt = $this->database->dbh->prepare("UPDATE `users` SET `password` = ? WHERE `userid` = ? LIMIT 1");
			$result = $stmt->execute([generatePasswordHash($newpassword), $userid]);
			
			if ($result == false) {
				throw new Exception("Database error");
			}
			
			$this->addAccountHistoryItem($userid, 2);
		}
		
		public function changeAccountEmail($userid, $newemail) {
			if (is_null($userid)) throw new Exception("Userid cannot be null");
			if (is_null($newemail)) throw new Exception("New email cannot be null");
			
			$stmt = $this->database->dbh->prepare("UPDATE `users` SET `email` = ? WHERE `userid` = ? LIMIT 1");
			$result = $stmt->execute([$newemail, $userid]);
			
			if ($result == false) {
				throw new Exception("Database query failed");
			}
		}
		
		// add an item to the account security history
		/*
			action mappings:
			
			0: account creation
			1: email change
			2: password change
			
		*/
		public function addAccountHistoryItem($userid, $action, $time = null, $ip = null) {
			if (is_null($userid)) throw new Exception("Userid cannot be null");
			if (is_null($action)) throw new Exception("Action cannot be null");
			
			if ($ip == null) {
				$ip = $_SERVER["REMOTE_ADDR"];
			}
			
			if ($time == null) {
				$time = time();
			}
			
			$stmt = $this->database->dbh->prepare("INSERT INTO `accounthistory` (`userid`, `action`, `time`, `ip`, `location`) VALUES (?, ?, ?, ?, ?)");
			
			$geolocation = null;
			
			// lookup geolocation from ip
			if (isset($ip)) {
				try {
					$geolocation = Utility::getGeolocation($ip);
				} catch(Exception $e) {}	// geolocation isn't super important kaaaay
			}
			
			$response = $stmt->execute([$userid, $action, $time, $ip, $geolocation]);
			
			if ($response == false) {
				throw new Exception("Failed to insert database entry.");
			}
		}
	}
?>
