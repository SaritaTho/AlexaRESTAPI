<?php
	include_once "../../php/Utility.php";
	include_once "../commoncode.php";
	
	class Account {
		private $database;
		
		public function __construct($dbh) {
			if (!isset($dbh))
				throw new Exception("No database provided");
			
			$this->database = $dbh;
		}
		
		// retreive login sessions for an account
		public function getAccountLogins($userid) {
			$stmt = $this->database->pquery("SELECT `browser`, `hash`, `timegenerated`, `expiry`, `ip`, `location`, `token` FROM `logintokens` WHERE `userid` = ? ORDER BY `timegenerated` DESC", [$userid]);			
			return $stmt->fetchAll();
		}
		
		/**
		 * Prints account logins as a table
		 */
		public function printAccountLogins() {
			try {
				$logins = $this->getAccountLogins($_SESSION["userid"]);
				
				foreach($logins as $login_item) {
					self::createItemRow($login_item);
				}
				
				unset($login_item);
				
			} catch (Exception $e) {
				echo "<tr><td>Database error</td></tr>" . $e->getMessage();
			}
		}
		
		public static function createItemRow($login_item) {
			global $webconfig;
			
			$row = "<tr id=\"" . $login_item["hash"] . "\">";
			
			$you = $login_item["token"] == $_SESSION["token"];	// whether this is the current session, aka "you"
			
			$row .= // browser
					createItemCell($login_item["browser"] ?? "(Unknown)", null, ($you ? " <b>(you)</b>" : null)) .
					
					// token hash
					//createItemCell($login_item["hash"], "<code>", "</code>") .
					
					// time generated
					createItemCell(Utility::getPrettyTime($login_item["timegenerated"])) .
					
					// expiry time
					createItemCell(Utility::getPrettyTime($login_item["expiry"])) .
					
					// ip address
					createIpCell($login_item["ip"]) .
					
					// location
					createItemCell($login_item["location"] ?? "(Unknown)") .
					
					// actions
					createItemCell("<a href=\"javascript:logoutToken('" . $login_item["hash"] . "');\">Logout</a>");
					
			$row .= "</tr>";
			
			echo $row;
			
			if ($you) {
				// add in handler for refreshing the page if the current session gets deleted
				echo "<script src=\"rowlistener.php?hash=" . $login_item["hash"] . "\" defer></script>";
			}
		}
	}

?>
