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
		 * Creates an access token for a user
		 * 
		 * @param $client int The client ID to create the token for
		 * @param $userid int The user ID to create the token for
		 * @param $scope string The scope(s) of the access token. Should conform to RFC6749 Section 3.3
		 * @param $expiry int How long the token should last, in seconds.  Null never expires
		 * @param $authcode int The ID of the authorization code that generated the token, if there is one
		 */
		public function createAccessToken($client, $userid, $scope, $expiry = null, $authcode = null) {
			$token = generateTokenString();
			$database->pquery("INSERT INTO `oauth_accesstokens` (`client`, `expiry`, `scope`, `token`, `userid`, `authcode`) VALUES (?, ?, ?, ?, ?, ?)",
				[$client, $expiry, $scope, $token, $userid, $authcode]);
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
			
			// get redirect uris
			$redirect_uris_results = $this->database->pquery("SELECT `uri` FROM `oauth_redirecturis` WHERE `client` = ?", [$client->id])->fetchAll(PDO::FETCH_COLUMN);
			$client->redirect_uris = array_values($redirect_uris_results);
			
			return $client;
		}
		
		/**
		 * Creates a new token string
		 *
		 * @param $length int The length of the token
		 * @returns string The generated token string
		 */
		private function generateTokenString($length = 256) {
			return bin2hex(random_bytes($length));
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
		
		/**
		 * An array containing valid redirect uris
		 */
		public $redirect_uris;
	}
	
?>