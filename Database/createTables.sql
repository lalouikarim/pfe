CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `verified` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `resettable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `roles_mask` int(10) unsigned NOT NULL DEFAULT '0',
  `registered` int(10) unsigned NOT NULL,
  `last_login` int(10) unsigned DEFAULT NULL,
  `force_logout` mediumint(7) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_confirmations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selector` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `email_expires` (`email`,`expires`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_remembered` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_resets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user_expires` (`user`,`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users_throttling` (
  `bucket` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `tokens` float unsigned NOT NULL,
  `replenished_at` int(10) unsigned NOT NULL,
  `expires_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`bucket`),
  KEY `expires_at` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teachers`(
 `id`             int(10) NOT NULL AUTO_INCREMENT ,
 `user_id`        int(10) NOT NULL ,
 `sign_up_status` int(2) NOT NULL ,
 `first_name`     varchar(20) NOT NULL ,
 `last_name`      varchar(45) NOT NULL ,
 `phone`          varchar(20) NOT NULL ,
 `card_photo`     varchar(50) NOT NULL ,
 `teacher_photo`  varchar(50) NOT NULL ,
 `cv_link`        varchar(100) NULL ,

PRIMARY KEY (`id`),
KEY `FK_65` (`user_id`),
CONSTRAINT `FK_63` FOREIGN KEY `FK_65` (`user_id`) REFERENCES `users` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `offers` (
 `id`         int(10) NOT NULL AUTO_INCREMENT ,
 `status`     int(2) NOT NULL ,
 `teacher_id` int(10) NOT NULL ,
 `state`      varchar(25) NOT NULL ,
 `commune`    varchar(25) NOT NULL ,
 `level`      varchar(25) NOT NULL ,
 `subject`    varchar(25) NOT NULL ,
 `price`      int(11) NOT NULL ,

PRIMARY KEY (`id`),
KEY `FK_68` (`teacher_id`),
CONSTRAINT `FK_66` FOREIGN KEY `FK_68` (`teacher_id`) REFERENCES `teachers` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ratings`(
 `id`       int(10) NOT NULL AUTO_INCREMENT ,
 `user_id`  int(10) NOT NULL ,
 `offer_id` int(10) NOT NULL ,
 `rating`   decimal(1,0) NOT NULL ,

PRIMARY KEY (`id`),
KEY `FK_12` (`user_id`),
CONSTRAINT `FK_10` FOREIGN KEY `FK_12` (`user_id`) REFERENCES `users` (`id`),
KEY `FK_31` (`offer_id`),
CONSTRAINT `FK_29` FOREIGN KEY `FK_31` (`offer_id`) REFERENCES `offers` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admins`(
 `id`       int(10) NOT NULL AUTO_INCREMENT ,
 `user_id`  int(10) NOT NULL ,

PRIMARY KEY (`id`),
KEY `FK_70` (`user_id`),
CONSTRAINT `FK_72` FOREIGN KEY `FK_70` (`user_id`) REFERENCES `users` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `offers_refusals`(
 `id`       int(10) NOT NULL AUTO_INCREMENT ,
 `offer_id`  int(10) NOT NULL ,
 `refusal_reason`  varchar(250) NOT NULL ,

PRIMARY KEY (`id`),
KEY `FK_80` (`offer_id`),
CONSTRAINT `FK_82` FOREIGN KEY `FK_80` (`offer_id`) REFERENCES `offers` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;