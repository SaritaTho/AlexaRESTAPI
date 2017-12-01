<?php
	include_once "../../php/Auth.php";
	include_once "../commoncode.php";
	
	class Activity {
		private $database;
		
		public function __construct($dbh) {
			if (!isset($dbh)) {
				throw new Exception("Dbh cannot be null");
			}
			
			$this->database = $dbh;
		}
		
		public function getAccountActivity($userid, $amount) {
			$stmt = $this->database->pquery("SELECT `action`, `time`, `ip`, `location` FROM `accounthistory` WHERE `userid` = ? ORDER BY `time` DESC", [$userid]);
			
			return $stmt->fetchAll();
		}
		
		public function printAccountActivity($amount = 1000) {
			try {
				$activity = $this->getAccountActivity($_SESSION["userid"], $amount);
				
				for ($i = 0; $i < $amount; $i++) {
					self::createItemRow($activity[$i]);
					
					if ($i >= (sizeof($activity) - 1)) {
						break;
					}
				}
			} catch (\Exception $e) {
				echo "<tr><td>Database error</td></tr>" . $e->getMessage();
			}
			
		}
		
		public static function createItemRow($activity_item) {
			global $webconfig;
			
			$row = "<tr>";
			
			// action name
			$row .=
			createItemCell(HistoryItems::getFriendlyName($activity_item["action"])) .
			
			// time
			createItemCell(Utility::getPrettyTime($activity_item["time"])) .
			
			// ip
			createIpCell($activity_item["ip"]) .
			
			// geolocation
			createItemCell($activity_item["location"]);
			
			$row .= "</tr>";
			
			echo $row;
		}
	}

?>
