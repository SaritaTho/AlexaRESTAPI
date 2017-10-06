<?php
	$webconfig = [
		"host" => [
			"domain" => "alexa.yourdomain.com"
		],
		"database" => [
			"host" => "localhost",
			"port" => "443",
			"username" => "root",
			"password" => "",
			"dbname" => "alexa"
		],
		"recaptcha" => [
			"site" => "google-recaptcha-sitekey",
			"secret" => "google-recaptcha-secret"
		],
		"geoip" => [
			"api-uri" => "http://freegeoip.net/json/%s",
			"browser-uri" => "https://geoiptool.com/en/?ip=%s"
		]
	];
?>
