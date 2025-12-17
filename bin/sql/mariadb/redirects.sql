CREATE TABLE `redirects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(4) NOT NULL,
  `old_url` varchar(2048) NOT NULL,
  `new_url` varchar(2048) DEFAULT NULL,
  `referer` varchar(2048) DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL DEFAULT 0,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(10) unsigned NOT NULL DEFAULT 0,
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(10) unsigned NOT NULL DEFAULT 0,
  `header` smallint(6) NOT NULL DEFAULT 301,
  `domain` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `link_modifed` (`updated`),
  KEY `old_url` (`old_url`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;