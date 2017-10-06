CREATE TABLE `accounthistory` (
	`userid` BIGINT(20) UNSIGNED NOT NULL COMMENT 'user the action is for',
	`action` INT(10) UNSIGNED NOT NULL COMMENT 'what the action was, see docs for codes',
	`time` BIGINT(20) UNSIGNED NOT NULL COMMENT 'time since epoch in secs',
	`ip` VARCHAR(45) NOT NULL COMMENT 'supports ipv4 mapped ip46\'s',
	`location` TEXT NULL COMMENT 'generated based on ip',
	INDEX `id` (`userid`),
	INDEX `time` (`time`)
)
COMMENT='Stores account security history for users'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `logintokens` (
	`userid` BIGINT(20) UNSIGNED NOT NULL COMMENT 'user id',
	`token` VARCHAR(60) NOT NULL COMMENT 'random bytes' COLLATE 'utf8_bin',
	`expiry` BIGINT(20) UNSIGNED NOT NULL COMMENT 'may be deleted at any time past expiry.',
	`hash` VARCHAR(10) NOT NULL COMMENT 'a hash of the token information, is shown to user',
	`timegenerated` BIGINT(20) UNSIGNED NOT NULL COMMENT 'when the token was generated',
	`ip` VARCHAR(45) NULL DEFAULT NULL COMMENT 'ip that generated the token',
	`location` TEXT NULL COMMENT 'determined from ip',
	`browser` TEXT NULL COMMENT 'browser info if available',
	INDEX `expiry` (`expiry`),
	INDEX `id` (`userid`),
	INDEX `hash` (`hash`)
)
COMMENT='Stores login tokens to keep user sessions.'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `users` (
	`userid` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'internal reference only',
	`email` VARCHAR(100) NOT NULL,
	`password` VARCHAR(255) NOT NULL COMMENT '60 character hash using bcrypt' COLLATE 'utf8_bin',
	`registered` BIGINT(20) UNSIGNED NOT NULL COMMENT 'date the user signed up',
	PRIMARY KEY (`userid`),
	UNIQUE INDEX `email` (`email`)
)
COMMENT='Stores all user data'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
