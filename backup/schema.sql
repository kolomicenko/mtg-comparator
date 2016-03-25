-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `1915239_mtg`;
CREATE DATABASE `1915239_mtg` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `1915239_mtg`;

DROP TABLE IF EXISTS `card`;
CREATE TABLE `card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `is_foil` bit(1) NOT NULL,
  `edition_id` int(11) NOT NULL,
  `quality` enum('MINT','LIGHTLY','HEAVILY','DAMAGED') COLLATE utf8_unicode_ci NOT NULL,
  `language` enum('English','Japanese','Portuguese','Chinese','Italian','Spanish','French','German','Russian','Korean') COLLATE utf8_unicode_ci NOT NULL,
  `price` float NOT NULL,
  `pieces` int(11) NOT NULL,
  `direction` enum('SELL','BUY') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `edition`;
CREATE TABLE `edition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_shop_id` (`name`,`shop_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `editions_pair`;
CREATE TABLE `editions_pair` (
  `edition1` int(11) NOT NULL,
  `edition2` int(11) NOT NULL,
  PRIMARY KEY (`edition1`,`edition2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `shop`;
CREATE TABLE `shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `direction` set('BUY','SELL') COLLATE utf8_unicode_ci NOT NULL,
  `currency` enum('KC','USD') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `shop` (`id`, `name`, `direction`, `currency`) VALUES
(1, 'Fireball', 'SELL', 'USD'),
(2, 'Cerny Rytir',  'BUY',  'KC');

-- 2015-07-21 17:26:01