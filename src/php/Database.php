<?php
	// wrapper around the PDO database
	class Database {
		public $dbh;
		
		public function __construct($dbconfig) {
			$this->dbh = new PDO(
			"mysql:host=" . $dbconfig['host'] . ";port=" . $dbconfig['port'] . ";dbname=" . $dbconfig['dbname'],
			$dbconfig['username'],
			$dbconfig['password']);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		
		// pquery stands for Prepared Query, which prepares and executes a query
		// returns: statement object
		public function pquery($query, $values = null) {
			$stmt = $this->dbh->prepare($query);
			$response = $stmt->execute($values);
			
			if ($response == null) {
				throw new Exception("Failed to execute query: response null");
			}
			
			return $stmt;
		}
	}	
