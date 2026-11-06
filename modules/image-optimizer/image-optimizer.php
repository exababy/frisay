<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class ImageOptimizerModule extends ModuleBase
{
	public string $name = 'image-optimizer';
	public string $title = 'Görsel Optimizasyon Yöneticisi';
	public string $version = '1.0.0';
	public string $description = 'Sitenizdeki tüm resimleri otomatik veya manuel olarak WebP formatına dönüştürerek disk alanı kazanmanızı sağlar.';
	public string $author = 'Can MEMİŞ';

	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = ['admin.js'];

	public array $apiActions = [
		'scan' => 'api/scan.php',
		'images' => 'api/images.php',
		'convert' => 'api/convert.php',
		'pause' => 'api/pause.php',
		'resume' => 'api/resume.php',
		'stats' => 'api/stats.php',
		'logs' => 'api/logs.php',
		'settings' => 'api/settings.php',
		'restore' => 'api/restore.php',
		'retry' => 'api/retry.php',
		'clean' => 'api/clean.php',
	];

	public function install(): bool
	{
		return $this->runSqlFile('install.sql');
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		// Register PSR-4 Autoloader
		require_once __DIR__ . '/src/Autoloader.php';
		\ImageOptimizer\Autoloader::register();

		// Automatic background/inline monitoring check (run throttled in Admin)
		if (defined('IN_ADMIN')) {
			// Ensure module registration in FShop DB modules table
			DB::execute("INSERT IGNORE INTO `modules` (`name`, `version`, `active`, `installed`) VALUES ('image-optimizer', '1.0.0', 1, 1)");
			DB::execute("UPDATE `modules` SET `active` = 1, `installed` = 1 WHERE `name` = 'image-optimizer'");

			// Drop unique index on hash if it exists
			try {
				$indices = DB::execute("SHOW INDEX FROM `image_opt_images` WHERE Key_name = 'idx_hash'") ?: [];
				if ($indices !== [] && isset($indices[0]['Non_unique']) && (int)$indices[0]['Non_unique'] === 0) {
					DB::execute("ALTER TABLE `image_opt_images` DROP INDEX `idx_hash`");
					DB::execute("ALTER TABLE `image_opt_images` ADD KEY `idx_hash` (`hash`)");
				}
			} catch (\Throwable $e) {}

			// Auto fix schema if old table structure exists
			$hasOldColumn = DB::execute("SHOW COLUMNS FROM `image_opt_settings` LIKE 'key'") ?: [];
			if ($hasOldColumn !== []) {
				DB::execute("DROP TABLE IF EXISTS `image_opt_settings`");
				$inst = new self();
				$inst->install();
			}

			$countImages = (int) DB::getValue("SELECT COUNT(*) FROM `image_opt_images`");
			$lastCheck = (int) \ImageOptimizer\Core\Settings::get('last_auto_scan', 0);
			
			if ($countImages === 0 || time() - $lastCheck > 600) { // 10 minutes throttle or initial empty
				\ImageOptimizer\Core\Settings::set('last_auto_scan', time());
				
				// Fast scan in background
				$scanner = new \ImageOptimizer\Service\ScannerService();
				$scanner->scan();
			}
		}

		// Hook into FShop product updates to queue new product images
		Module::registerHook('product.updated', function(int $productId) {
			$images = DB::execute("SELECT id FROM images WHERE id_product = ?", [$productId]) ?: [];
			foreach ($images as $img) {
				$imageId = (int) $img['id'];
				$relativePath = 'img/products/' . $imageId . '.jpg';
				
				// Check and save in database
				$sourceFile = dirname(__DIR__, 2) . '/' . $relativePath;
				if (file_exists($sourceFile)) {
					$hash = hash_file('sha256', $sourceFile);
					$size = filesize($sourceFile);

					$existing = \ImageOptimizer\Repository\ImageRepository::findByPath($relativePath);
					if ($existing) {
						if ($existing['hash'] !== $hash) {
							$existing['hash'] = $hash;
							$existing['original_size'] = $size;
							$existing['status'] = 'pending';
							$existing['error_message'] = null;
							\ImageOptimizer\Repository\ImageRepository::save($existing);
							\ImageOptimizer\Repository\QueueRepository::add((int)$existing['id']);
						}
					} else {
						$newId = \ImageOptimizer\Repository\ImageRepository::save([
							'file_path' => $relativePath,
							'hash' => $hash,
							'original_size' => $size,
							'status' => 'pending',
							'category' => 'products'
						]);
						if ($newId) {
							\ImageOptimizer\Repository\QueueRepository::add((int)$newId);
						}
					}
				}
			}
		});
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		// Make sure classes are loaded
		require_once __DIR__ . '/src/Autoloader.php';
		\ImageOptimizer\Autoloader::register();

		// Fetch and assign settings for bindings
		$settings = \ImageOptimizer\Core\Settings::getAll();
		
		// Decode locations
		$locations = [];
		if (isset($settings['scan_locations'])) {
			$locations = json_decode($settings['scan_locations'], true) ?: ['img'];
		}

		$smarty->assign([
			'adminToken' => $adminToken ?? '',
			'optSettings' => $settings,
			'optLocations' => $locations,
		]);
	}
}
