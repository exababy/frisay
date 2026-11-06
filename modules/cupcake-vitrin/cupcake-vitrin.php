<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/CupcakeVitrinService.php';
require_once __DIR__ . '/lib/CupcakeVitrinBulkInstaller.php';

/**
 * Cupcake Vitrin — "cupcake" mağaza temasının yönetim modülü.
 *
 * - Her kategori için ayrı vitrin rengi / emoji / slogan
 * - Ana sayfa hero ve duyuru metinleri
 * - Tek tıkla tema etkinleştirme
 * - FriSay modüllerinin toplu kurulumu
 * - Demo cupcake vitrini (isteğe bağlı)
 *
 * Güncelleme dostu: şema sürümü ayarlarda tutulur, boot() sırasında
 * eksik tablo/kolonlar otomatik eklenir. Modülü yeniden kurmak gerekmez.
 */
class CupcakeVitrinModule extends ModuleBase
{
	public string $name = 'cupcake-vitrin';
	public string $title = 'Cupcake Vitrin';
	public string $version = CupcakeVitrinService::VERSION;
	public string $description = 'Cupcake vitrin teması: kategori vitrinleri, renkler, hero metinleri, toplu modül kurulumu';
	public string $author = 'Cupcake Vitrin';

	public array $adminStylesheets = ['admin.css'];

	public function install(): bool
	{
		CupcakeVitrinService::migrate();

		if (Theme::isValidName(CupcakeVitrinService::THEME)) {
			Theme::setActiveTheme(CupcakeVitrinService::THEME);
		}

		return true;
	}

	public function uninstall(): bool
	{
		// Tema etkin kalabilir: cupcake teması modül olmadan da varsayılan değerlerle çalışır.
		DB::execute('DROP TABLE IF EXISTS `cupcake_vitrin_styles`');
		Settings::set(CupcakeVitrinService::KEY_SCHEMA, '');

		return true;
	}

	public function boot(): void
	{
		CupcakeVitrinService::migrateIfNeeded();
		CupcakeVitrinService::registerTemplateFallback();

		Module::registerHook('smarty.assign', static function ($smarty = null): void {
			if (!$smarty || !defined('IN_SCRIPT') || defined('IN_ADMIN')) {
				return;
			}

			$smarty->assign('cupcake', CupcakeVitrinService::getFrontConfig());
		});
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$flashType = 'success';
		$bulkLog = [];
		$tab = (string) Tools::getValue('tab', 'vitrin');

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals((string) $adminToken, $postToken)) {
				$flash = 'Geçersiz istek (oturum süresi dolmuş olabilir). Sayfayı yenileyip tekrar deneyin.';
				$flashType = 'danger';
			} elseif (Tools::isSubmit('cvSaveSettings')) {
				$values = [];

				foreach (array_keys(CupcakeVitrinService::settingDefaults()) as $key) {
					$values[$key] = (string) Tools::getValue($key, '');
				}

				CupcakeVitrinService::saveSettings($values);
				$flash = 'Vitrin ayarları kaydedildi.';
				$tab = 'genel';
			} elseif (Tools::isSubmit('cvSaveStyles')) {
				$rows = $_POST['styles'] ?? [];
				CupcakeVitrinService::saveStyles(is_array($rows) ? $rows : []);
				$flash = 'Kategori vitrinleri kaydedildi.';
				$tab = 'vitrin';
			} elseif (Tools::isSubmit('cvActivateTheme')) {
				$result = Theme::setActiveTheme(CupcakeVitrinService::THEME);
				$flash = $result['success'] ? 'Cupcake teması etkinleştirildi.' : (string) $result['message'];
				$flashType = $result['success'] ? 'success' : 'danger';
				$tab = 'genel';
			} elseif (Tools::isSubmit('cvInstallAll')) {
				$selected = $_POST['modules'] ?? [];
				$result = CupcakeVitrinBulkInstaller::installMany(is_array($selected) ? $selected : []);
				$bulkLog = $result['logs'];
				$flash = $result['message'];
				$flashType = $result['success'] ? 'success' : 'warning';
				$tab = 'moduller';
			} elseif (Tools::isSubmit('cvLoadDemo')) {
				require_once __DIR__ . '/lib/CupcakeVitrinDemo.php';
				$result = CupcakeVitrinDemo::load();
				$flash = $result['message'];
				$flashType = $result['success'] ? 'success' : 'danger';
				$tab = 'genel';
			}
		}

		$smarty->assign([
			'cvFlash' => $flash,
			'cvFlashType' => $flashType,
			'cvTab' => in_array($tab, ['vitrin', 'genel', 'moduller'], true) ? $tab : 'vitrin',
			'cvSettings' => CupcakeVitrinService::getSettings(),
			'cvCategories' => CupcakeVitrinService::getAdminCategories(),
			'cvPalettes' => CupcakeVitrinService::palettes(),
			'cvThemeActive' => trim((string) Settings::get('THEME')) === CupcakeVitrinService::THEME,
			'cvThemeExists' => Theme::isValidName(CupcakeVitrinService::THEME),
			'cvModules' => CupcakeVitrinBulkInstaller::getCatalog(),
			'cvBulkLog' => $bulkLog,
			'cvVersion' => CupcakeVitrinService::VERSION,
		]);
	}
}
