CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(64) NOT NULL,
  `display` varchar(64) NOT NULL,
  `backend` text DEFAULT NULL,
  `domain` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;