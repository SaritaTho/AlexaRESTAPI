<?php
	/**
	 * An OAuth server implementation
	 */
	class OAuth {
		// database connection to use
		private $database;
		
		/**
		 * The list of valid scopes for authentication
		 */
		public static $scopes = [
			"managedevices" => "Manage your devices",
			"controldevices" => "Control your devices",
			"account" => "See your account info"
		];
		
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
		
		/**
		 * Gets a client from their client ID
		 * 
		 * @param $client_id string ID of the client
		 * @returns OAuthClient The client, or null if the client does not exist
		 */
		public function getClientFromId($client_id) {
			$results = $this->database->pquery("SELECT * FROM `oauth_clients` WHERE `client_id` = ?", [$client_id])->fetchAll();
			
			// return null if no client is returned
			if (count($results) < 1) {
				return null;
			}
			
			$client = new OAuthClient();
			$client->friendly_name = $results[0]["name"];
			$client->publisher_name = $results[0]["publisher"];
			$client->icon = $results[0]["icon"];
			$client->client_id = $client_id;
			$client->id = $results[0]["id"];
			$client->secret = $results[0]["client_secret"];
			$client->url = $results[0]["url"];
			
			return $client;
		}
	}
	
	/**
	 * Represents an OAuth client
	 */
	class OAuthClient {
		function __construct() {
			
		}
		
		/**
		 * The friendly name to be shown to the user-agent
		 */
		public $friendly_name;
		
		/**
		 * The client publisher
		 */
		public $publisher_name;
		
		/**
		 * The client icon, in base64
		 */
		public $icon;
		
		/**
		 * The client secret string
		 */
		public $secret;
		
		/**
		 * The OAuth 2 client identifier string
		 */
		public $client_id;
		
		/**
		 * The internal ID of the client
		 */
		public $id;
		
		/**
		 * The webpage for the client
		 */
		public $url;
	}
	
?>