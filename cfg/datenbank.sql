# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.10)
# Datenbank: empok_nor
# Erstellt am: 2016-10-31 15:46:24 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Export von Tabelle wi_blacklist
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wi_blacklist`;

CREATE TABLE `wi_blacklist` (
  `id_blacklist` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nick` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `id_forum` bigint(20) NOT NULL,
  `grund` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`id_blacklist`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;



# Export von Tabelle wi_buerge
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wi_buerge`;

CREATE TABLE `wi_buerge` (
  `buerge_id` bigint(20) NOT NULL,
  `wichtel_nick` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `wichtel_id` bigint(20) NOT NULL,
  `buerge_forum_id` bigint(20) NOT NULL,
  `buerge_forum_nick` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Export von Tabelle wi_geschenk
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wi_geschenk`;

CREATE TABLE `wi_geschenk` (
  `geschenk_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `beschreibung` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `empfangen` timestamp NULL DEFAULT NULL,
  `wichtel_id` bigint(20) NOT NULL,
  `level` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `art` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `gesendet` timestamp NULL DEFAULT NULL,
  `post_art` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `post_id` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `partner_id` bigint(20) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`geschenk_id`),
  UNIQUE KEY `geschenk_id` (`geschenk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;



# Export von Tabelle wi_wichtel
# ------------------------------------------------------------

DROP TABLE IF EXISTS `wi_wichtel`;

CREATE TABLE `wi_wichtel` (
  `wichtel_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` bigint(20) NOT NULL,
  `nick` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `email` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `name` varchar(256) CHARACTER SET utf8 DEFAULT '',
  `adresse` varchar(256) CHARACTER SET utf8 DEFAULT '',
  `adrzusatz` varchar(256) CHARACTER SET utf8 DEFAULT '',
  `plz` varchar(50) CHARACTER SET utf8 DEFAULT '',
  `ort` varchar(256) CHARACTER SET utf8 DEFAULT '',
  `land` varchar(265) CHARACTER SET utf8 DEFAULT '',
  `notizen` text CHARACTER SET utf8,
  PRIMARY KEY (`wichtel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
