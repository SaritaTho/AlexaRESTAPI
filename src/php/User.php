<?php
	// simple structure for a user
	class User {
		public $email;
		public $token;
		public $userid;
		
		public function __construct($userid, $email, $token) {
			$this->email = $email;
			$this->token = $token;
			$this->userid = $userid;
		}
		
		public function getUserId() {
			return $this->userid;
		}
		
		public function getEmail() {
			return $this->email;
		}
		
		public function getToken() {
			return $this->token;
		}
		
		// setters can be added but currently too lazy to add them
	}
?>
