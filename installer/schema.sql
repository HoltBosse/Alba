

DROP TABLE IF EXISTS `configurations`;
CREATE TABLE `configurations` (
  `name` varchar(255) NOT NULL,
  `configuration` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `controller_basic_html`;
CREATE TABLE `controller_basic_html` (
  `id` int(11) NOT NULL,
  `state` tinyint(2) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `content_type` int(11) NOT NULL COMMENT 'content_types table',
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` int(11) NOT NULL DEFAULT 0,
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `markup` mediumtext,
  `og_description` mediumtext,
  `seo_keywords` mediumtext,
  `og_title` mediumtext,
  `og_image` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `content_fields`;

DROP TABLE IF EXISTS `content_types`;
CREATE TABLE `content_types` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `controller_location` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `state` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `content_views`;
CREATE TABLE `content_views` (
  `id` int(11) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `value` varchar(64) NOT NULL,
  `display` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `alt` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `mimetype` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `content_type` int(11) DEFAULT NULL,
  `content_view` int(11) DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent` int(11) NOT NULL DEFAULT '-1',
  `template` int(11) NOT NULL DEFAULT '1',
  `content_view_configuration` text,
  `page_options` text NOT NULL COMMENT 'seo and og settings',
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `page_widget_overrides`;
CREATE TABLE `page_widget_overrides` (
  `page_id` int(11) NOT NULL,
  `position` varchar(255) NOT NULL,
  `widgets` varchar(255) DEFAULT NULL COMMENT 'csv list of widget ids'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tagged`;
CREATE TABLE `tagged` (
  `tag_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `content_type_id` int(11) NOT NULL COMMENT 'Important: -1 signifies MEDIA, -2 signifies USERS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  `public` tinyint(2) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `image` int(11) DEFAULT NULL,
  `filter` int(11) NOT NULL DEFAULT '1' COMMENT '0 admin only 1 exclusive 2 inclusive',
  `description` mediumtext DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT 0,
  `custom_fields` text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tag_content_type`;
CREATE TABLE `tag_content_type` (
  `content_type_id` int(11) NOT NULL COMMENT '-1 for media',
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `description` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `state` tinyint(2) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_key_expires` timestamp NULL DEFAULT NULL,
  `reset_key` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE `user_groups` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `widgets`;
CREATE TABLE `widgets` (
  `id` int(11) NOT NULL,
  `state` tinyint(2) NOT NULL DEFAULT '1',
  `type` int(11) NOT NULL COMMENT 'id from widget_types',
  `ordering` int(11) NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `position_control` tinyint(2) NOT NULL DEFAULT '1',
  `global_position` varchar(255) DEFAULT NULL,
  `page_list` varchar(255) DEFAULT NULL COMMENT 'csv string page ids',
  `note` varchar(255) DEFAULT NULL,
  `options` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `widget_types`;
CREATE TABLE `widget_types` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` int(11) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `options` text COMMENT 'options_json',
  `description` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT 1,
  `title` varchar(64) NOT NULL,
  `content_type` int(11) NOT NULL COMMENT '-1 media, -2 user, -3 tag',
  `parent` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `content_versions`;
CREATE TABLE `content_versions` (
  `id` int NOT NULL,
  `content_id` int NOT NULL,
  `created_by` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fields_json` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `redirects`;
CREATE TABLE `redirects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint NOT NULL,
  `old_url` varchar(2048) CHARACTER SET utf8mb4 NOT NULL,
  `new_url` varchar(2048) CHARACTER SET utf8mb4 DEFAULT NULL,
  `referer` varchar(2048) CHARACTER SET utf8mb4 DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `hits` int unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int unsigned NOT NULL DEFAULT '0',
  `header` smallint NOT NULL DEFAULT '301',
  PRIMARY KEY (`id`),
  KEY `link_modifed` (`updated`),
  KEY `old_url` (`old_url`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `configurations`
  ADD PRIMARY KEY (`name`);

ALTER TABLE `controller_basic_html`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `content_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `content_views`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mimetype` (`mimetype`);

ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `page_widget_overrides`
  ADD UNIQUE KEY `page_id` (`page_id`,`position`);

ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `user_groups`
  ADD UNIQUE KEY `user_id` (`user_id`,`group_id`);

ALTER TABLE `widgets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `widget_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `plugins`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `content_versions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `controller_basic_html`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `content_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `content_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `widget_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `plugins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `content_versions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;




