<?php
	$webconfig = [
		"development" => false,
		"host" => [
			"domain" => "alexa.yourdomain.com",
			"ssl-only" => true,
		],
		"authentication" => [
			"login-lifetime" => 2.6E6	// approx one month
		],
		"database" => [
			"host" => "localhost",
			"port" => "3306",
			"username" => "root",
			"password" => "",
			"dbname" => "alexa"
		],
		"recaptcha" => [
			"enabled" => true,
			"site" => "google-recaptcha-sitekey",
			"secret" => "google-recaptcha-secret"
		],
		"geoip" => [
			"api-uri" => "http://freegeoip.net/json/%s",
			"browser-uri" => "https://geoiptool.com/en/?ip=%s"
		]
	];
?>
