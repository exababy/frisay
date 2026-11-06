CREATE TABLE IF NOT EXISTS `image_opt_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `file_path` VARCHAR(512) NOT NULL,
  `hash` VARCHAR(64) NOT NULL,
  `original_size` INT NOT NULL,
  `optimized_size` INT DEFAULT NULL,
  `original_width` INT DEFAULT NULL,
  `original_height` INT DEFAULT NULL,
  `optimized_width` INT DEFAULT NULL,
  `optimized_height` INT DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `error_message` TEXT DEFAULT NULL,
  `engine_used` VARCHAR(50) DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'other',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_hash` (`hash`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `idx_file_path` (`file_path`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `image_opt_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `image_id` INT DEFAULT NULL,
  `level` VARCHAR(10) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `image_opt_queue` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `image_id` INT NOT NULL,
  `priority` INT NOT NULL DEFAULT 0,
  `locked` TINYINT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `idx_image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `image_opt_settings`;
CREATE TABLE `image_opt_settings` (
  `setting_key` VARCHAR(64) PRIMARY KEY,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `image_opt_statistics` (
  `date` DATE PRIMARY KEY,
  `saved_bytes` BIGINT NOT NULL DEFAULT 0,
  `converted_count` INT NOT NULL DEFAULT 0,
  `failed_count` INT NOT NULL DEFAULT 0,
  `skipped_count` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `image_opt_backups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `image_id` INT NOT NULL,
  `backup_path` VARCHAR(512) NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `idx_image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `image_opt_settings` (`setting_key`, `setting_value`) VALUES
('compression_quality', '85'),
('max_resize_width', ''),
('max_resize_height', ''),
('keep_transparency', 'yes'),
('strip_metadata', 'yes'),
('strip_exif', 'yes'),
('strip_gps', 'yes'),
('strip_camera_info', 'yes'),
('keep_original_files', 'yes'),
('backup_originals', 'yes'),
('delete_originals_after_success', 'no'),
('scan_locations', '["img","modules","templates"]'),
('exclude_folders', 'cache,logs,backup,vendor,node_modules,tmp,temp,.git'),
('exclude_files', ''),
('regex_exclusions', ''),
('min_kb', '0'),
('max_mb', '100'),
('batch_size', '50');
