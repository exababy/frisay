CREATE TABLE IF NOT EXISTS `story_items` (
  `id_story` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `cover_file` varchar(255) NOT NULL DEFAULT '',
  `media_type` varchar(16) NOT NULL DEFAULT 'image',
  `media_file` varchar(255) NOT NULL DEFAULT '',
  `link_url` varchar(512) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_story`),
  KEY `active_position` (`active`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
