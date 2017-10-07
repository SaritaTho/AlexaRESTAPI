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
	}	
