CREATE TABLE `widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(2) NOT NULL DEFAULT '1',
  `type` int(11) NOT NULL COMMENT 'id from widget_types',
  `ordering` int(11) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `position_control` tinyint(2) NOT NULL DEFAULT '1',
  `global_position` varchar(255) DEFAULT NULL,
  `page_list` varchar(255) DEFAULT NULL COMMENT 'csv string page ids',
  `note` varchar(255) DEFAULT NULL,
  `options` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;