CREATE TABLE `accounthistory` (
	`userid` INT(10) UNSIGNED NOT NULL COMMENT 'User the action is for',
	`action` INT(10) UNSIGNED NOT NULL COMMENT 'Action identifier',
	`time` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Timestamp',
	`ip` VARCHAR(45) NOT NULL COMMENT 'IP that generated the action',
	`location` TEXT NULL COMMENT 'Geolocation of IP',
	INDEX `id` (`userid`),
	INDEX `time` (`time`),
	CONSTRAINT `FK_ACCOUNTHISTORY_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores account security history for users'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `logintokens` (
	`userid` INT(10) UNSIGNED NOT NULL COMMENT 'Internal user id',
	`token` VARCHAR(60) NOT NULL COMMENT 'Randomly generated token' COLLATE 'utf8_bin',
	`expiry` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Expiration date of token',
	`hash` VARCHAR(10) NOT NULL COMMENT 'A short hash for user reference',
	`timegenerated` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Time created',
	`ip` VARCHAR(45) NULL DEFAULT NULL COMMENT 'IP that generated the token',
	`location` TEXT NULL COMMENT 'Geolocation of IP',
	`browser` TEXT NULL COMMENT 'Browser information string',
	INDEX `expiry` (`expiry`),
	INDEX `id` (`userid`),
	INDEX `hash` (`hash`),
	CONSTRAINT `FK_LOGINTOKENS_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores login tokens to keep user sessions.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `oauth_accesstokens` (
	`id` INT(10) UNSIGNED NOT NULL COMMENT 'ID of the client',
	`expiry` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Expiry of the access token, in seconds since epoch',
	`scope` TEXT NOT NULL COMMENT 'The scope of the token',
	`token` VARCHAR(256) NOT NULL COMMENT 'Access token string',
	`userid` INT(10) UNSIGNED NOT NULL COMMENT 'Userid of the user the token is for',
	`authcode` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'The authorization code that generated the token',
	PRIMARY KEY (`id`),
	INDEX `FK_OAUTH_ACCESSTOKENS_USERID` (`userid`),
	INDEX `FK_OAUTH_ACCESSTOKENS_AUTHCODE` (`authcode`),
	CONSTRAINT `FK_OAUTH_ACCESSTOKENS_AUTHCODE` FOREIGN KEY (`authcode`) REFERENCES `oauth_authcodes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_OAUTH_ACCESSTOKENS_ID` FOREIGN KEY (`id`) REFERENCES `oauth_clients` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_OAUTH_ACCESSTOKENS_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores OAuth access tokens'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `oauth_authcodes` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal ID of the authorization code',
	`client` INT(10) UNSIGNED NOT NULL COMMENT 'Internal ID of the client',
	`userid` INT(10) UNSIGNED NOT NULL COMMENT 'ID of the user',
	`scope` INT(10) UNSIGNED NOT NULL COMMENT 'The scope of the authorization code',
	`expiry` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Expiry of the authorization grant. Should be a small amount of time.',
	`redirect_uri` TEXT NULL COMMENT 'The redirect URI of the original authorization request.',
	`code` TEXT NOT NULL COMMENT 'The authorization code given to the client' COLLATE 'utf8_bin',
	`used` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Whether the authorization code has been used. 1 for true, and 0 for false.',
	PRIMARY KEY (`id`),
	INDEX `FK_OAUTH_AUTHCODES_USERID` (`userid`),
	INDEX `FK_OAUTH_AUTHCODES_CLIENT` (`client`),
	CONSTRAINT `FK_OAUTH_AUTHCODES_CLIENTID` FOREIGN KEY (`client`) REFERENCES `oauth_clients` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_OAUTH_AUTHCODES_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores OAuth authorization codes'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `oauth_clients` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Internal ID of client',
	`client_id` TEXT NOT NULL COMMENT '(OAuth) A random, permanent client ID. Not to be confused with the id column.',
	`client_secret` TEXT NOT NULL COMMENT 'A random, non-permanent client secret' COLLATE 'utf8_bin',
	`redirect_uri` TEXT NOT NULL COMMENT 'Redirect URI of the client',
	`name` VARCHAR(25) NOT NULL COMMENT 'Friendly client name',
	`publisher` VARCHAR(25) NULL DEFAULT NULL COMMENT 'Client publisher',
	`icon` LONGBLOB NULL COMMENT 'Client icon, stored in base64',
	PRIMARY KEY (`id`)
)
COMMENT='Stores information about OAuth clients\r\n'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;

CREATE TABLE `oauth_refreshtokens` (
	`id` INT(10) UNSIGNED NOT NULL COMMENT 'ID of the client',
	`token` VARCHAR(256) NOT NULL COMMENT 'Refresh token string' COLLATE 'utf8_bin',
	`userid` INT(10) UNSIGNED NOT NULL COMMENT 'Userid of the user the token is for',
	`authcode` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Authorization code that created the token',
	`scope` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'The scope of the refresh token. Access tokens of more restrictive scopes may be requested.',
	PRIMARY KEY (`id`),
	INDEX `FK_OAUTH_REFRESHTOKENS_USERID` (`userid`),
	INDEX `FK_OAUTH_REFRESHTOKENS_AUTHCODE` (`authcode`),
	CONSTRAINT `FK_OAUTH_REFRESHTOKENS_AUTHCODE` FOREIGN KEY (`authcode`) REFERENCES `oauth_authcodes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_OAUTH_REFRESHTOKENS_ID` FOREIGN KEY (`id`) REFERENCES `oauth_clients` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_OAUTH_REFRESHTOKENS_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores OAuth access tokens'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `users` (
	`userid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal User ID',
	`email` VARCHAR(100) NOT NULL COMMENT 'User email',
	`password` VARCHAR(255) NOT NULL COMMENT 'Password hash' COLLATE 'utf8_bin',
	`registered` BIGINT(20) UNSIGNED NOT NULL COMMENT 'User registration date',
	PRIMARY KEY (`userid`),
	UNIQUE INDEX `UNQUE_EMAIL` (`email`)
)
COMMENT='Stores all user data'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;
