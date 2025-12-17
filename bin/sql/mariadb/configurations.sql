CREATE TABLE `configurations` (
  `name` varchar(255) NOT NULL,
  `configuration` text NOT NULL,
  `domain` int(11) NOT NULL DEFAULT 0,
  UNIQUE KEY `name_domain` (`name`,`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;