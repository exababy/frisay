CREATE TABLE IF NOT EXISTS `product_files_assets` (
	`id_product` INT NOT NULL,
	`logo_path` VARCHAR(255) NOT NULL DEFAULT '',
	`logo_name` VARCHAR(255) NOT NULL DEFAULT '',
	`date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_files_documents` (
	`id_document` INT NOT NULL AUTO_INCREMENT,
	`id_product` INT NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`file_path` VARCHAR(255) NOT NULL,
	`file_name` VARCHAR(255) NOT NULL,
	`date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_document`),
	KEY `id_product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
