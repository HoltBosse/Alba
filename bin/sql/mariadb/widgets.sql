CREATE TABLE `widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(4) NOT NULL DEFAULT 1,
  `type` int(11) NOT NULL COMMENT 'id from widget_types',
  `ordering` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `position_control` tinyint(4) NOT NULL DEFAULT 1,
  `global_position` varchar(255) DEFAULT NULL,
  `page_list` varchar(255) DEFAULT NULL COMMENT 'csv string page ids',
  `note` varchar(255) DEFAULT NULL,
  `options` text NOT NULL,
  `domain` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;