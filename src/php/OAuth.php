<?php
	/**
	 * An OAuth server implementation
	 */
	class OAuth {
		// database connection to use
		private $database;
		
		/**
		 * Create a new OAuth instance
		 * 
		 * @param $dbh \Database The database connection to use
		 */
		function __construct($dbh) {
			$this->database = $dbh;
		}
		
		/**
		 * Creates a new access token for an app
		 * 
		 * @param $appid int App ID of the app the token is for
		 * @param $userid int User ID the token is for
		 * @returns string Token string
		 */
		public function createOAuthToken($appid, $userid) {
			// generate a 256 char random string for the token
			$token = bin2hex(openssl_random_pseudo_bytes(128));
			
			$query = $this->database->pquery("INSERT INTO `oauth_tokens` (`appid`, `token`, `userid`) VALUES (?, ?, ?)", [$appid, $token, $userid]);
		}
	}
	
?>