CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `content_type` int(11) DEFAULT NULL,
  `content_view` int(11) DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent` int(11) NOT NULL DEFAULT '-1',
  `template` int(11) NOT NULL DEFAULT '1',
  `content_view_configuration` text,
  `page_options` text NOT NULL COMMENT 'seo and og settings',
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;