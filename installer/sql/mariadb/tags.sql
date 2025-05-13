CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` int(11) NOT NULL DEFAULT '1',
  `public` tinyint(2) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `image` int(11) DEFAULT NULL,
  `filter` int(11) NOT NULL DEFAULT '1' COMMENT '0 admin only 1 exclusive 2 inclusive',
  `description` mediumtext DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT 0,
  `custom_fields` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;