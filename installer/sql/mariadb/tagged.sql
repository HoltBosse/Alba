CREATE TABLE `tagged` (
  `tag_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `content_type_id` int(11) NOT NULL COMMENT 'Important: -1 signifies MEDIA, -2 signifies USERS',
  UNIQUE KEY `tag_id_content_id_content_type_id` (`tag_id`,`content_id`,`content_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;