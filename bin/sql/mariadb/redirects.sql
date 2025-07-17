CREATE TABLE `redirects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint NOT NULL,
  `old_url` varchar(2048) CHARACTER SET utf8mb4 NOT NULL,
  `new_url` varchar(2048) CHARACTER SET utf8mb4 DEFAULT NULL,
  `referer` varchar(2048) CHARACTER SET utf8mb4 DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `hits` int unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int unsigned NOT NULL DEFAULT '0',
  `header` smallint NOT NULL DEFAULT '301',
  PRIMARY KEY (`id`),
  KEY `link_modifed` (`updated`),
  KEY `old_url` (`old_url`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;