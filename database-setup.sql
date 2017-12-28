CREATE TABLE `users` (
	`userid` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'The internal User ID',
	`email` VARCHAR(100) NOT NULL COMMENT 'User email',
	`password` VARCHAR(255) NOT NULL COMMENT 'Password hash' COLLATE 'utf8_bin',
	`registered` BIGINT(20) UNSIGNED NOT NULL COMMENT 'User registration date',
	PRIMARY KEY (`userid`),
	UNIQUE INDEX `email` (`email`)
)
COMMENT='Stores all user data'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;

CREATE TABLE `accounthistory` (
	`userid` BIGINT(20) UNSIGNED NOT NULL COMMENT 'User the action is for',
	`action` INT(10) UNSIGNED NOT NULL COMMENT 'Action identifier',
	`time` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Timestamp',
	`ip` VARCHAR(45) NOT NULL COMMENT 'IP that generated the action',
	`location` TEXT NULL COMMENT 'Geolocation of IP',
	INDEX `id` (`userid`),
	INDEX `time` (`time`),
	CONSTRAINT `ACCOUNTHISTORY_FK_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores account security history for users'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `logintokens` (
	`userid` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Internal user id',
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
	CONSTRAINT `LOGINTOKENS_FK_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores login tokens to keep user sessions.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `oauth_apps` (
	`appid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Internal ID of service',
	`name` VARCHAR(25) NOT NULL COMMENT 'Friendly app name',
	`publisher` VARCHAR(25) NULL DEFAULT NULL COMMENT 'App publisher',
	`icon` LONGBLOB NULL COMMENT 'App icon, stored in base64',
	`redirect_uri` TEXT NOT NULL COMMENT 'Redirect URI of the app',
	PRIMARY KEY (`appid`)
)
COMMENT='Stores information about OAuth "Apps" or "Services"'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `oauth_tokens` (
	`appid` INT(10) UNSIGNED NOT NULL COMMENT 'ID of the app',
	`token` VARCHAR(256) NOT NULL COMMENT 'Access token string',
	`userid` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Userid of the user the token is for',
	PRIMARY KEY (`appid`),
	UNIQUE INDEX `UNIQUE_APP_USER` (`appid`, `userid`),
	INDEX `FK_OAUTH_TOKENS_USERID` (`userid`),
	CONSTRAINT `FK_OAUTH_TOKENS_APPID` FOREIGN KEY (`appid`) REFERENCES `oauth_apps` (`appid`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_OAUTH_TOKENS_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores OAuth access tokens'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
