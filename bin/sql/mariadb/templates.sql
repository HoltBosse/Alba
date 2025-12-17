CREATE TABLE `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;