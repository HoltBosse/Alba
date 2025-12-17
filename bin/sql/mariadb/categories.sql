CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` int(11) NOT NULL DEFAULT 1,
  `title` varchar(64) NOT NULL,
  `content_type` int(11) NOT NULL COMMENT '-1 media, -2 user, -3 tag',
  `parent` int(11) NOT NULL DEFAULT 0,
  `custom_fields` text DEFAULT NULL,
  `domain` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;