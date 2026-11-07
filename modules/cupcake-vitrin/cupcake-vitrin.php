<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

/**
 * Eski "Cupcake Vitrin" kurulumlarından "Antepli Vitrin"e otomatik geçiş.
 *
 * İlk sürümde tema "cupcake", modül "cupcake-vitrin" adıyla kurulmuştu. Bu adlar
 * "antepli" / "antepli-vitrin" olarak değişti. Eski kurulumda veritabanı hâlâ
 * THEME=cupcake gösterdiği için mağaza açılmaz (templates/cupcake yok).
 *
 * Bu küçük modül, eski kurulumda "etkin" kayıtlı olduğu için ilk istekte çalışır:
 *  - O isteğin şablonlarını templates/antepli'den yükler (sayfa hemen açılır)
 *  - THEME ayarını antepli yapar
 *  - CV_* ayarlarını AU_* olarak, kategori vitrin stillerini yeni tabloya taşır
 *  - antepli-vitrin modülünü kurulu/etkin işaretler
 *  - Kendi kaydını siler (bir daha çalışmaz)
 *
 * Yeni kurulumlarda hiçbir şey yapmaz; kurmanıza gerek yoktur.
 */
class CupcakeVitrinModule extends ModuleBase
{
	public string $name = 'cupcake-vitrin';
	public string $title = 'Cupcake → Antepli geçişi (otomatik)';
	public string $version = '1.0.1';
	public string $description = 'Yalnızca eski Cupcake Vitrin kurulumlarını Antepli Vitrin\'e taşır. Yeni kurulumda kurmanıza gerek yok.';
	public string $author = 'Antepli Ustalar';

	/** Eski palet anahtarı → yeni palet anahtarı */
	private const PALETTE_MAP = [
		'cilek' => 'visne',
		'cikolata' => 'kakao',
		'vanilya' => 'tereyagi',
		'fistik' => 'fistik',
		'yaban-mersini' => 'bogurtlen',
		'karamel' => 'karamel',
		'red-velvet' => 'visne',
		'limon' => 'tereyagi',
		'lavanta' => 'bogurtlen',
		'nane' => 'nane',
	];

	public function install(): bool
	{
		self::migrate();

		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public function boot(): void
	{
		self::registerTemplateRedirect();
		self::migrate();
	}

	/**
	 * Bu istek için templates/cupcake/* → templates/antepli/* yönlendirmesi.
	 */
	private static function registerTemplateRedirect(): void
	{
		global $smarty;

		if (!is_object($smarty) || !method_exists($smarty, 'registerDefaultTemplateHandler')) {
			return;
		}

		$root = dirname(__DIR__, 2);
		$previous = $smarty->default_template_handler_func ?? null;

		$smarty->registerDefaultTemplateHandler(static function ($type, $name, &$content, &$modified, $smartyObj) use ($root, $previous) {
			if ($type === 'file' && is_string($name)) {
				$normalized = '/' . ltrim(str_replace('\\', '/', $name), '/');
				$pos = strrpos($normalized, '/templates/cupcake/');

				if ($pos !== false) {
					$rest = substr($normalized, $pos + strlen('/templates/cupcake/'));

					foreach (['antepli', 'theme4'] as $theme) {
						$candidate = $root . '/templates/' . $theme . '/' . $rest;

						if ($rest !== '' && strpos($rest, '..') === false && is_file($candidate)) {
							return $candidate;
						}
					}
				}
			}

			if (is_callable($previous)) {
				return call_user_func_array($previous, [$type, $name, &$content, &$modified, $smartyObj]);
			}

			return false;
		});
	}

	public static function migrate(): void
	{
		try {
			$serviceFile = dirname(__DIR__) . '/antepli-vitrin/lib/AntepliVitrinService.php';

			if (!is_file($serviceFile)) {
				return;
			}

			require_once $serviceFile;
			AntepliVitrinService::migrate();

			// 1) Tema
			$theme = trim((string) Settings::get('THEME'));

			if (($theme === 'cupcake' || $theme === '') && is_dir(dirname(__DIR__, 2) . '/templates/antepli')) {
				Settings::set('THEME', 'antepli');

				if (class_exists('Theme', false)) {
					Theme::ensureColorsFile('antepli');
					Theme::ensureCustomCss('antepli');
				}
			}

			// 2) Ayarlar CV_* → AU_* (yalnızca yenisi henüz özelleştirilmemişse)
			$rows = DB::execute("SELECT title, value FROM settings WHERE title LIKE 'CV\\_%'") ?: [];
			$defaults = AntepliVitrinService::settingDefaults();

			foreach ($rows as $row) {
				$old = (string) $row['title'];

				if ($old === 'CV_SCHEMA_VERSION') {
					continue;
				}

				$new = 'AU_' . substr($old, 3);

				if (!array_key_exists($new, $defaults)) {
					continue;
				}

				// Metin ayarlarında yalnızca kullanıcı tarafından girilmiş değerleri taşı (WhatsApp, anahtarlar)
				if (in_array($new, ['AU_WHATSAPP', 'AU_SHOW_HOME_SLIDER', 'AU_SHOW_SPRINKLES', 'AU_SHOW_MARQUEE'], true)) {
					Settings::set($new, (string) $row['value']);
				}
			}

			DB::execute("DELETE FROM settings WHERE title LIKE 'CV\\_%'");

			// 3) Kategori vitrin stilleri
			$hasOld = DB::execute("SHOW TABLES LIKE 'cupcake_vitrin_styles'");

			if ($hasOld) {
				$styles = DB::execute('SELECT * FROM cupcake_vitrin_styles') ?: [];

				foreach ($styles as $style) {
					$palette = self::PALETTE_MAP[(string) ($style['palette'] ?? '')] ?? '';
					DB::execute(
						'INSERT IGNORE INTO `' . AntepliVitrinService::TABLE . '` (id_category, palette, emoji, tagline, ribbon) VALUES (?, ?, ?, ?, ?)',
						[(int) $style['id_category'], $palette, '', (string) ($style['tagline'] ?? ''), (string) ($style['ribbon'] ?? '')]
					);
				}

				DB::execute('DROP TABLE IF EXISTS cupcake_vitrin_styles');
			}

			// 4) Yeni modülü kurulu + etkin yap (Module::install bootstrap sırasında çağrılmaz)
			$exists = DB::getValue('SELECT id_module FROM modules WHERE name = ? LIMIT 1', ['antepli-vitrin']);

			if ($exists === false) {
				DB::insert('modules', [
					'name' => 'antepli-vitrin',
					'version' => AntepliVitrinService::VERSION,
					'active' => 1,
					'installed' => 1,
				]);
			} else {
				DB::execute('UPDATE modules SET installed = 1, active = 1 WHERE name = ?', ['antepli-vitrin']);
			}

			// 5) Kendi kaydını kaldır
			DB::execute('DELETE FROM module_display_hooks WHERE module_name = ?', ['cupcake-vitrin']);
			DB::execute('DELETE FROM modules WHERE name = ?', ['cupcake-vitrin']);

			if (class_exists('Performance', false)) {
				Performance::clearCaches();
			}
		} catch (Throwable $e) {
			error_log('cupcake-vitrin geçişi: ' . $e->getMessage());
		}
	}
}
