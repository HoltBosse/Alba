CREATE TABLE `page_widget_overrides` (
  `page_id` int(11) NOT NULL,
  `position` varchar(255) NOT NULL,
  `widgets` varchar(255) DEFAULT NULL COMMENT 'csv list of widget ids',
  UNIQUE KEY `page_id` (`page_id`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;