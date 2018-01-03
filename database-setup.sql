-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.29-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table alexa.accounthistory
CREATE TABLE IF NOT EXISTS `accounthistory` (
  `userid` int(10) unsigned NOT NULL COMMENT 'User the action is for',
  `action` int(10) unsigned NOT NULL COMMENT 'Action identifier',
  `time` bigint(20) unsigned NOT NULL COMMENT 'Timestamp',
  `ip` varchar(45) NOT NULL COMMENT 'IP that generated the action',
  `location` text COMMENT 'Geolocation of IP',
  KEY `id` (`userid`),
  KEY `time` (`time`),
  CONSTRAINT `FK_ACCOUNTHISTORY_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores account security history for users';

-- Data exporting was unselected.
-- Dumping structure for table alexa.logintokens
CREATE TABLE IF NOT EXISTS `logintokens` (
  `userid` int(10) unsigned NOT NULL COMMENT 'Internal user id',
  `token` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Randomly generated token',
  `expiry` bigint(20) unsigned NOT NULL COMMENT 'Expiration date of token',
  `hash` varchar(10) NOT NULL COMMENT 'A short hash for user reference',
  `timegenerated` bigint(20) unsigned NOT NULL COMMENT 'Time created',
  `ip` varchar(45) DEFAULT NULL COMMENT 'IP that generated the token',
  `location` text COMMENT 'Geolocation of IP',
  `browser` text COMMENT 'Browser information string',
  KEY `expiry` (`expiry`),
  KEY `id` (`userid`),
  KEY `hash` (`hash`),
  CONSTRAINT `FK_LOGINTOKENS_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores login tokens to keep user sessions.';

-- Data exporting was unselected.
-- Dumping structure for table alexa.oauth_accesstokens
CREATE TABLE IF NOT EXISTS `oauth_accesstokens` (
  `client` int(10) unsigned NOT NULL COMMENT 'ID of the client',
  `expiry` bigint(20) unsigned DEFAULT NULL COMMENT 'Expiry of the access token, in seconds since epoch',
  `scope` text NOT NULL COMMENT 'The scope of the token',
  `token` varchar(256) NOT NULL COMMENT 'Access token string',
  `userid` int(10) unsigned NOT NULL COMMENT 'Userid of the user the token is for',
  `authcode` int(11) unsigned DEFAULT NULL COMMENT 'The authorization code that generated the token',
  KEY `FK_OAUTH_ACCESSTOKENS_USERID` (`userid`),
  KEY `FK_OAUTH_ACCESSTOKENS_AUTHCODE` (`authcode`),
  KEY `client` (`client`),
  CONSTRAINT `FK_OAUTH_ACCESSTOKENS_AUTHCODE` FOREIGN KEY (`authcode`) REFERENCES `oauth_authcodes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_OAUTH_ACCESSTOKENS_CLIENT` FOREIGN KEY (`client`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_OAUTH_ACCESSTOKENS_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores OAuth access tokens';

-- Data exporting was unselected.
-- Dumping structure for table alexa.oauth_authcodes
CREATE TABLE IF NOT EXISTS `oauth_authcodes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The internal ID of the authorization code',
  `client` int(10) unsigned NOT NULL COMMENT 'Internal ID of the client',
  `userid` int(10) unsigned NOT NULL COMMENT 'ID of the user',
  `scope` text NOT NULL COMMENT 'The scope of the authorization code',
  `expiry` bigint(20) unsigned NOT NULL COMMENT 'Expiry of the authorization grant. Should be a small amount of time.',
  `redirect_uri` text COMMENT 'The redirect URI of the original authorization request.',
  `code` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'The authorization code given to the client',
  `used` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether the authorization code has been used. 1 for true, and 0 for false.',
  PRIMARY KEY (`id`),
  KEY `FK_OAUTH_AUTHCODES_USERID` (`userid`),
  KEY `FK_OAUTH_AUTHCODES_CLIENT` (`client`),
  CONSTRAINT `FK_OAUTH_AUTHCODES_CLIENTID` FOREIGN KEY (`client`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_OAUTH_AUTHCODES_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores OAuth authorization codes';

-- Data exporting was unselected.
-- Dumping structure for table alexa.oauth_clients
CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Internal ID of client',
  `client_id` text NOT NULL COMMENT '(OAuth) A random, permanent client ID. Not to be confused with the id column.',
  `client_secret` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'A random, non-permanent client secret',
  `url` text NOT NULL COMMENT 'URL to the client''s homepage',
  `name` varchar(25) NOT NULL COMMENT 'Friendly client name',
  `publisher` varchar(25) DEFAULT NULL COMMENT 'Client publisher',
  `icon` text COMMENT 'Client icon, stored in base64',
  `user` int(10) unsigned NOT NULL COMMENT 'The user that created the client',
  PRIMARY KEY (`id`),
  KEY `FK_OAUTH_CLIENTS_USER` (`user`),
  CONSTRAINT `FK_OAUTH_CLIENTS_USER` FOREIGN KEY (`user`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores information about OAuth clients\r\n';

-- Data exporting was unselected.
-- Dumping structure for table alexa.oauth_redirecturis
CREATE TABLE IF NOT EXISTS `oauth_redirecturis` (
  `client` int(10) unsigned NOT NULL COMMENT 'The client the the redirect URI belongs to',
  `uri` text NOT NULL COMMENT 'The redirect URI',
  KEY `FK_OAUTH_REDIRECTURIS_CLIENT` (`client`),
  CONSTRAINT `FK_OAUTH_REDIRECTURIS_CLIENT` FOREIGN KEY (`client`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores redirect URIs for OAuth clients';

-- Data exporting was unselected.
-- Dumping structure for table alexa.oauth_refreshtokens
CREATE TABLE IF NOT EXISTS `oauth_refreshtokens` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID of the client',
  `token` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Refresh token string',
  `userid` int(10) unsigned NOT NULL COMMENT 'Userid of the user the token is for',
  `authcode` int(10) unsigned DEFAULT NULL COMMENT 'Authorization code that created the token',
  `scope` int(10) unsigned DEFAULT NULL COMMENT 'The scope of the refresh token. Access tokens of more restrictive scopes may be requested.',
  PRIMARY KEY (`id`),
  KEY `FK_OAUTH_REFRESHTOKENS_USERID` (`userid`),
  KEY `FK_OAUTH_REFRESHTOKENS_AUTHCODE` (`authcode`),
  CONSTRAINT `FK_OAUTH_REFRESHTOKENS_AUTHCODE` FOREIGN KEY (`authcode`) REFERENCES `oauth_authcodes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_OAUTH_REFRESHTOKENS_ID` FOREIGN KEY (`id`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_OAUTH_REFRESHTOKENS_USERID` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores OAuth access tokens';

-- Data exporting was unselected.
-- Dumping structure for table alexa.users
CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The internal User ID',
  `email` varchar(100) NOT NULL COMMENT 'User email',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Password hash',
  `registered` bigint(20) unsigned NOT NULL COMMENT 'User registration date',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `UNQUE_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores all user data';

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
