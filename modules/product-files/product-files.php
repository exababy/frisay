<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class ProductFilesModule extends ModuleBase
{
	public string $name = 'product-files';
	public string $title = 'Ürün Dosyaları ve Logoları';
	public string $version = '1.0.0';
	public string $description = 'Ürünlere PDF, resim veya TXT dokümanı ve ürün bilgi alanına logo ekler';
	public string $author = 'FShop';
	public array $displayHooks = [
		'product_tab' => 'Ürün dokümanı sekme başlığı',
		'product_tab_content' => 'Ürün dokümanı indirme bağlantısı',
		'product_inf' => 'Ürün bilgi alanında logo',
		'admin_product_button' => 'Admin ürün düzenleme alanları',
	];
	public array $defaultDisplayHooks = ['product_tab', 'product_tab_content', 'product_inf', 'admin_product_button'];
	public array $apiActions = ['save' => 'api/save.php'];

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
		// Modül daha önce kurulmuşsa yeni çoklu doküman tablosunu istekte ekle.
		DB::execute('CREATE TABLE IF NOT EXISTS `product_files_documents` (
			`id_document` INT NOT NULL AUTO_INCREMENT,
			`id_product` INT NOT NULL,
			`title` VARCHAR(255) NOT NULL,
			`file_path` VARCHAR(255) NOT NULL,
			`file_name` VARCHAR(255) NOT NULL,
			`date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id_document`),
			KEY `id_product` (`id_product`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;
		$message = '';
		if (Tools::isSubmit('saveProductFilesSettings')) {
			if (hash_equals((string) $adminToken, (string) Tools::getValue('token'))) {
				$title = trim(strip_tags((string) Tools::getValue('tab_title')));
				Settings::set('PRODUCT_FILES_TAB_TITLE', $title !== '' ? mb_substr($title, 0, 255) : 'Detaylı bilgi');
				$message = 'Sekme başlığı kaydedildi';
			} else {
				$message = 'Geçersiz istek';
			}
		}
		$smarty->assign([
			'productFilesTabTitle' => (string) (Settings::get('PRODUCT_FILES_TAB_TITLE') ?: 'Detaylı bilgi'),
			'productFilesAdminToken' => (string) $adminToken,
			'productFilesSettingsMessage' => $message,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		$id = (int) ($context['id_product'] ?? 0);
		if ($id < 1 || !in_array($hook, ['product_tab', 'product_tab_content', 'product_inf'], true)) {
			return null;
		}

		$rows = DB::getAssocRow('product_files_assets', 'id_product = ' . $id);
		$data = is_array($rows[0] ?? null) ? $rows[0] : [];
		$documents = DB::execute('SELECT * FROM product_files_documents WHERE id_product = ? ORDER BY id_document ASC', [$id]) ?: [];
		if (!$data) {
			$data = [];
		}

		if ($hook === 'product_tab' && !$documents) {
			return null;
		}
		if ($hook === 'product_tab_content' && !$documents) {
			return null;
		}
		if ($hook === 'product_inf' && empty($data['logo_path'])) {
			return null;
		}

		$html = $this->renderFrontTemplate($hook, [
			'id_product' => $id,
			'document_title' => (string) (Settings::get('PRODUCT_FILES_TAB_TITLE') ?: 'Detaylı bilgi'),
			'documents' => array_map(function (array $document): array {
				$document['url'] = $this->getUrl((string) $document['file_path']);
				return $document;
			}, $documents),
			'logo_url' => $this->getUrl((string) ($data['logo_path'] ?? '')),
			'logo_name' => (string) ($data['logo_name'] ?? ''),
		]);

		return $html !== '' ? $html : null;
	}

	public function renderAdminDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'admin_product_button') {
			return null;
		}

		global $adminToken;
		$id = (int) ($context['id_product'] ?? 0);
		$rows = $id > 0 ? DB::getAssocRow('product_files_assets', 'id_product = ' . $id) : [];
		$data = is_array($rows[0] ?? null) ? $rows[0] : [];
		$documents = $id > 0 ? (DB::execute('SELECT * FROM product_files_documents WHERE id_product = ? ORDER BY id_document ASC', [$id]) ?: []) : [];
		$html = $this->renderAdminTemplate('product_fields', [
			'id_product' => $id,
			'admin_token' => (string) $adminToken,
			'documents' => $documents,
			'logo_name' => (string) ($data['logo_name'] ?? ''),
			'api_url' => rtrim((string) Settings::get('DOMAIN'), '/') . '/api/module.php?m=product-files&action=save',
		]);
		return $html !== '' ? $html : null;
	}
}
