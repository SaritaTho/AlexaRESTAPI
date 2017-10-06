<?php
	class Account {
		private $database;
		
		public function __construct($dbh) {
			if (!isset($dbh))
				throw new Exception("No database provided");
			
			$this->database = $dbh;
		}
		
		// retreive login sessions for an account
		public function getAccountLogins($userid) {
			$stmt = $this->database->dbh->prepare("SELECT `browser`, `hash`, `timegenerated`, `expiry`, `ip`, `location`, `token` FROM `logintokens` WHERE `userid` = ? ORDER BY `timegenerated` DESC");
			$result = $stmt->execute([$userid]);
			
			if ($result == false) {
				throw new Exception("Failed to query database");
			}
			
			return $stmt->fetchAll();
		}
	}

?>
