CREATE TABLE `tag_content_type` (
  `content_type_id` int(11) NOT NULL COMMENT '-1 for media',
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;