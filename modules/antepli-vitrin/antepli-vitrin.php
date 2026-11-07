<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/AntepliVitrinService.php';
require_once __DIR__ . '/lib/AntepliVitrinBulkInstaller.php';

/**
 * Antepli Vitrin — "antepli" (Antepli Ustalar) mağaza temasının yönetim modülü.
 *
 * - Her kategori için ayrı vitrin rengi / emoji / slogan
 * - Ana sayfa hero ve duyuru metinleri
 * - Tek tıkla tema etkinleştirme
 * - FriSay modüllerinin toplu kurulumu
 * - Demo vitrin: kerebiç, kahke, kuru pasta, İzmir bombası… (isteğe bağlı)
 *
 * Güncelleme dostu: şema sürümü ayarlarda tutulur, boot() sırasında
 * eksik tablo/kolonlar otomatik eklenir. Modülü yeniden kurmak gerekmez.
 */
class AntepliVitrinModule extends ModuleBase
{
	public string $name = 'antepli-vitrin';
	public string $title = 'Antepli Vitrin';
	public string $version = AntepliVitrinService::VERSION;
	public string $description = 'Antepli Ustalar vitrin teması: kategori vitrinleri, renkler, ana sayfa metinleri, toplu modül kurulumu';
	public string $author = 'Antepli Ustalar';

	public array $adminStylesheets = ['admin.css'];

	public function install(): bool
	{
		AntepliVitrinService::migrate();

		if (Theme::isValidName(AntepliVitrinService::THEME)) {
			Theme::setActiveTheme(AntepliVitrinService::THEME);
		}

		return true;
	}

	public function uninstall(): bool
	{
		// Tema etkin kalabilir: antepli teması modül olmadan da varsayılan değerlerle çalışır.
		DB::execute('DROP TABLE IF EXISTS `antepli_vitrin_styles`');
		Settings::set(AntepliVitrinService::KEY_SCHEMA, '');

		return true;
	}

	public function boot(): void
	{
		AntepliVitrinService::migrateIfNeeded();
		AntepliVitrinService::registerTemplateFallback();

		// theme4 CSS'i (FriSay güncellemesiyle) değiştiyse renk katmanını yenile
		try {
			require_once __DIR__ . '/lib/AntepliSkinBuilder.php';

			if (AntepliSkinBuilder::isStale()) {
				AntepliSkinBuilder::build();
			}
		} catch (Throwable $e) {
			error_log('antepli-skin: ' . $e->getMessage());
		}

		// Antepli teması aktifken Tema Edit (theme4) modülünün global renk/yazı tipi CSS'ini yükleme
		Module::registerHook('head.assets', static function (&$assets): void {
			if (!is_array($assets) || !class_exists('Theme', false) || Theme::currentName() !== AntepliVitrinService::THEME) {
				return;
			}

			foreach (['css', 'js'] as $type) {
				$assets[$type] = array_values(array_filter($assets[$type] ?? [], static function ($url): bool {
					return strpos((string) $url, '/modules/theme4/assets/') === false;
				}));
			}
		});

		Module::registerHook('smarty.assign', static function ($smarty = null): void {
			if (!$smarty || !defined('IN_SCRIPT') || defined('IN_ADMIN')) {
				return;
			}

			$smarty->assign('antepli', AntepliVitrinService::getFrontConfig());
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

				foreach (array_keys(AntepliVitrinService::settingDefaults()) as $key) {
					$values[$key] = (string) Tools::getValue($key, '');
				}

				AntepliVitrinService::saveSettings($values);
				$flash = 'Vitrin ayarları kaydedildi.';
				$tab = 'genel';
			} elseif (Tools::isSubmit('cvSaveStyles')) {
				$rows = $_POST['styles'] ?? [];
				AntepliVitrinService::saveStyles(is_array($rows) ? $rows : []);
				$flash = 'Kategori vitrinleri kaydedildi.';
				$tab = 'vitrin';
			} elseif (Tools::isSubmit('cvActivateTheme')) {
				$result = Theme::setActiveTheme(AntepliVitrinService::THEME);
				$flash = $result['success'] ? 'Antepli Ustalar teması etkinleştirildi.' : (string) $result['message'];
				$flashType = $result['success'] ? 'success' : 'danger';
				$tab = 'genel';
			} elseif (Tools::isSubmit('cvInstallAll')) {
				$selected = $_POST['modules'] ?? [];
				$result = AntepliVitrinBulkInstaller::installMany(is_array($selected) ? $selected : []);
				$bulkLog = $result['logs'];
				$flash = $result['message'];
				$flashType = $result['success'] ? 'success' : 'warning';
				$tab = 'moduller';
			} elseif (Tools::isSubmit('cvLoadDemo')) {
				require_once __DIR__ . '/lib/AntepliVitrinDemo.php';
				$result = AntepliVitrinDemo::load();
				$flash = $result['message'];
				$flashType = $result['success'] ? 'success' : 'danger';
				$tab = 'genel';
			}
		}

		$smarty->assign([
			'cvFlash' => $flash,
			'cvFlashType' => $flashType,
			'cvTab' => in_array($tab, ['vitrin', 'genel', 'moduller'], true) ? $tab : 'vitrin',
			'cvSettings' => AntepliVitrinService::getSettings(),
			'cvCategories' => AntepliVitrinService::getAdminCategories(),
			'cvPalettes' => AntepliVitrinService::palettes(),
			'cvThemeActive' => trim((string) Settings::get('THEME')) === AntepliVitrinService::THEME,
			'cvThemeExists' => Theme::isValidName(AntepliVitrinService::THEME),
			'cvModules' => AntepliVitrinBulkInstaller::getCatalog(),
			'cvBulkLog' => $bulkLog,
			'cvVersion' => AntepliVitrinService::VERSION,
			'cvLogoUrl' => AntepliVitrinService::logoUrl(),
		]);
	}
}
