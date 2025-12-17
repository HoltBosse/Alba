CREATE TABLE `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(4) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `options` text DEFAULT NULL COMMENT 'options_json',
  `description` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;