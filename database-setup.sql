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
AUTO_INCREMENT=6
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
