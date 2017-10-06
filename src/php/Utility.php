<?php
	include_once __DIR__ . "/../config.php";
	
	class Utility {
		public static function getGeolocation($ip) {
			global $webconfig;
			$geolocation_raw = file_get_contents(sprintf($webconfig["geoip"]["api-uri"], $ip));
			
			if ($geolocation_raw != false) {
				$geolocation_array = json_decode($geolocation_raw);
				// city, region, country
				// san francisco, california, united states
				return $geolocation_array->city . ", ". $geolocation_array->region_name . ", ". $geolocation_array->country_name;
			} else {
				return null;
			}
		}
		
		public static function getPrettyTime($timestamp = null) {
			if (is_null($timestamp)) {
				$timestamp = time();
			}
			
			return date("g:i A T n/j/Y", $timestamp);	// 8:35 AM UTC 9/16/2017
		}
	}
?>
