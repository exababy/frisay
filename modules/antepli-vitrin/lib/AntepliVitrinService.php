<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

/**
 * Antepli Vitrin ayarları, kategori vitrin stilleri ve otomatik şema göçü.
 */
class AntepliVitrinService
{
	/** Modül sürümü — her yayında artırın; boot() farkı görüp migrate() çalıştırır. */
	public const VERSION = '1.1.0';

	/** Şema sürümü — tablo/kolon değişince artırın. */
	public const SCHEMA_VERSION = 1;

	public const THEME = 'antepli';
	public const FALLBACK_THEME = 'theme4';
	public const KEY_SCHEMA = 'AU_SCHEMA_VERSION';
	public const TABLE = 'antepli_vitrin_styles';

	/** @var array<string, mixed>|null */
	private static ?array $frontConfig = null;

	/** @var callable|null */
	private static $previousTemplateHandler = null;

	private static bool $fallbackRegistered = false;

	/* ------------------------------------------------------------------ */
	/* Şema / güncelleme                                                   */
	/* ------------------------------------------------------------------ */

	/**
	 * Hızlı kontrol: şema sürümü güncelse hiçbir şey yapmaz (tek SELECT).
	 */
	public static function migrateIfNeeded(): void
	{
		$stored = (string) Settings::get(self::KEY_SCHEMA);

		if ($stored === self::SCHEMA_VERSION . '@' . self::VERSION) {
			return;
		}

		self::migrate();
	}

	/**
	 * İdempotent göç: tabloyu oluşturur, eksik kolonları ekler, varsayılan ayarları yazar,
	 * modules tablosundaki sürümü günceller. Kaç kez çalışırsa çalışsın güvenlidir.
	 */
	public static function migrate(): void
	{
		DB::execute(
			'CREATE TABLE IF NOT EXISTS `' . self::TABLE . '` (
			  `id_category` int(11) NOT NULL,
			  `palette` varchar(32) NOT NULL DEFAULT \'\',
			  `emoji` varchar(16) NOT NULL DEFAULT \'\',
			  `tagline` varchar(190) NOT NULL DEFAULT \'\',
			  `ribbon` varchar(64) NOT NULL DEFAULT \'\',
			  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id_category`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
		);

		// Sonraki sürümlerde yeni kolonlar buraya eklenir (yeniden kurulum gerekmez).
		$columns = [
			'ribbon' => "ALTER TABLE `" . self::TABLE . "` ADD COLUMN `ribbon` varchar(64) NOT NULL DEFAULT '' AFTER `tagline`",
		];

		foreach ($columns as $column => $ddl) {
			$exists = DB::execute('SHOW COLUMNS FROM `' . self::TABLE . "` LIKE '" . preg_replace('/[^a-z0-9_]/', '', $column) . "'");

			if (!$exists) {
				DB::execute($ddl);
			}
		}

		foreach (self::settingDefaults() as $key => $default) {
			$current = DB::getValue('SELECT id FROM settings WHERE title = ? LIMIT 1', [$key]);

			if ($current === false) {
				Settings::set($key, $default);
			}
		}

		// Önceki sürümlerin varsayılan metinleri hiç değiştirilmemişse yenileriyle güncelle
		// (kullanıcının kendi yazdığı metinlere dokunulmaz).
		$defaults = self::settingDefaults();

		foreach (self::legacyDefaults() as $key => $oldValues) {
			$value = (string) Settings::get($key);

			if (in_array($value, $oldValues, true) && isset($defaults[$key])) {
				Settings::set($key, $defaults[$key]);
			}
		}

		// Instagram linki boşsa bir kereliğine markanın hesabını yaz (admin'den değiştirilebilir)
		if (trim((string) Settings::get('INSTAGRAM_LINK')) === '' && Settings::get('AU_IG_PREFILLED') === '') {
			Settings::set('INSTAGRAM_LINK', 'https://www.instagram.com/antepliustalar/');
			Settings::set('AU_IG_PREFILLED', '1');
		}

		DB::execute('UPDATE modules SET version = ? WHERE name = ?', [self::VERSION, 'antepli-vitrin']);
		Settings::set(self::KEY_SCHEMA, self::SCHEMA_VERSION . '@' . self::VERSION);
	}

	/* ------------------------------------------------------------------ */
	/* Tema yedeği (template fallback)                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * antepli temasında olmayan bir .tpl istenirse (ör. FriSay güncellemesiyle gelen yeni
	 * bir sayfa) aynı dosyayı theme4 klasöründen yükler. Böylece güncellemeden sonra
	 * tema "şablon bulunamadı" hatası vermez.
	 */
	public static function registerTemplateFallback(): void
	{
		global $smarty;

		if (self::$fallbackRegistered || !is_object($smarty) || !method_exists($smarty, 'registerDefaultTemplateHandler')) {
			return;
		}

		$previous = $smarty->default_template_handler_func ?? null;
		self::$previousTemplateHandler = is_callable($previous) ? $previous : null;
		$smarty->registerDefaultTemplateHandler([self::class, 'templateFallback']);
		self::$fallbackRegistered = true;
	}

	/**
	 * @param mixed $content
	 * @param mixed $modified
	 * @return string|bool
	 */
	public static function templateFallback($type, $name, &$content, &$modified, $smarty)
	{
		if ($type === 'file' && is_string($name)) {
			// _THEME_BASE_DIR_ göreli ("templates/antepli/x.tpl") veya mutlak olabilir
			$normalized = '/' . ltrim(str_replace('\\', '/', $name), '/');
			$needle = '/templates/' . self::THEME . '/';
			$pos = strrpos($normalized, $needle);

			if ($pos !== false) {
				$rest = substr($normalized, $pos + strlen($needle));
				$candidate = dirname(__DIR__, 3) . '/templates/' . self::FALLBACK_THEME . '/' . $rest;

				if ($rest !== '' && strpos($rest, '..') === false && is_file($candidate)) {
					return $candidate;
				}
			}
		}

		if (self::$previousTemplateHandler) {
			return call_user_func_array(self::$previousTemplateHandler, [$type, $name, &$content, &$modified, $smarty]);
		}

		return false;
	}

	/* ------------------------------------------------------------------ */
	/* Ayarlar                                                             */
	/* ------------------------------------------------------------------ */

	/** @return array<string, string> */
	public static function settingDefaults(): array
	{
		return [
			'AU_ANNOUNCE' => 'Sıcak fırından, her gün taze · Kerebiç, kahke ve kuru pasta',
			'AU_HERO_EYEBROW' => 'Sıcak Fırından · Patisserie',
			'AU_HERO_TITLE' => 'Antep\'in ustalığı, her gün taze.',
			'AU_HERO_TEXT' => 'Fıstıklı kerebiçten köylü kahkesine, şekerli ve tuzlu kuru pastadan İzmir bombasına; ustalarımızın elinden, her gün sıcak fırından.',
			'AU_HERO_IMAGES' => '',
			'AU_HERO_CTA' => 'Vitrini keşfet',
			'AU_HERO_CTA2' => 'Bayram & toplu sipariş',
			'AU_HERO_CTA2_URL' => 'contact',
			'AU_BADGE_1' => 'Bol Antep fıstığı',
			'AU_BADGE_2' => 'Günlük üretim',
			'AU_BADGE_3' => 'Kiloyla & kutuyla',
			'AU_SHOWCASE_TITLE' => 'Vitrinlerimiz',
			'AU_SHOWCASE_TEXT' => 'Mağazamızdaki gibi: her çeşit kendi vitrininde, tepsi tepsi.',
			'AU_WHATSAPP' => '',
			'AU_WHATSAPP_TEXT' => 'Merhaba, sipariş vermek istiyorum.',
			'AU_SHOW_HOME_SLIDER' => '1',
			'AU_SHOW_SPRINKLES' => '1',
			'AU_SHOW_MARQUEE' => '1',
			'AU_MARQUEE' => 'Fıstıklı Kerebiç • Köylü Kahkesi • Çubuk Kahke • Tahinli • Tarsus Çöreği • Kömbe • İzmir Bombası • Şekerli Kuru Pasta • Tuzlu Kuru Pasta',
			'AU_FOOTER_TEXT' => 'Antep\'in köklü fırın geleneğini; kerebiçten köylü kahkesine, şekerli ve tuzlu kuru pastalara kadar her gün taze üretip özenle kutuluyoruz.',
		];
	}

	/** @return array<string, list<string>> Eski sürümlerin varsayılanları */
	private static function legacyDefaults(): array
	{
		return [
			'AU_ANNOUNCE' => ['Sıcak fırından, her gün taze · Kerebiç, kahke ve kuru pasta çeşitleri 🍪'],
			'AU_HERO_TITLE' => ['Antep\'in ustalığı her gün taze'],
			'AU_HERO_TEXT' => ['Fıstıklı kerebiçten köylü kahkesine, şekerli ve tuzlu kuru pastadan İzmir bombasına; her lezzet kendi vitrininde. Kaydır, seç, sepete at.'],
			'AU_HERO_CTA' => ['Vitrine göz at'],
			'AU_SHOWCASE_TEXT' => ['Mağazamızdaki gibi: her çeşit kendi vitrininde, tepsi tepsi. Rafları kaydırarak keşfet.'],
		];
	}

	/** @return array<string, string> */
	public static function getSettings(): array
	{
		$rows = DB::execute("SELECT title, value FROM settings WHERE title LIKE 'AU\\_%'") ?: [];
		$stored = [];

		foreach ($rows as $row) {
			$stored[(string) $row['title']] = (string) $row['value'];
		}

		$out = [];

		foreach (self::settingDefaults() as $key => $default) {
			$out[$key] = array_key_exists($key, $stored) ? $stored[$key] : $default;
		}

		return $out;
	}

	/** @param array<string, string> $values */
	public static function saveSettings(array $values): void
	{
		$checkboxes = ['AU_SHOW_HOME_SLIDER', 'AU_SHOW_SPRINKLES', 'AU_SHOW_MARQUEE'];

		foreach (self::settingDefaults() as $key => $default) {
			if (in_array($key, $checkboxes, true)) {
				Settings::set($key, !empty($values[$key]) ? '1' : '0');
				continue;
			}

			$value = trim((string) ($values[$key] ?? ''));

			if ($key === 'AU_WHATSAPP') {
				$value = preg_replace('/\D+/', '', $value) ?? '';
			}

			if ($key === 'AU_HERO_CTA2_URL') {
				$value = self::sanitizeLink($value);
			}

			Settings::set($key, mb_substr(strip_tags($value), 0, 1200));
		}

		self::$frontConfig = null;
	}

	private static function sanitizeLink(string $url): string
	{
		$url = trim($url);

		if ($url === '') {
			return '';
		}

		if (preg_match('#^https?://#i', $url)) {
			return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
		}

		return preg_replace('#[^a-zA-Z0-9/_\-\?=&\.\#]#', '', ltrim($url, '/')) ?? '';
	}

	/* ------------------------------------------------------------------ */
	/* Vitrin paletleri                                                    */
	/* ------------------------------------------------------------------ */

	/** @return array<string, array{label: string, swatch: string, accent: string}> */
	public static function palettes(): array
	{
		return [
			'susam' => ['label' => 'Susam (açık kum)', 'swatch' => '#f2e8d6', 'accent' => '#a8804a'],
			'fistik' => ['label' => 'Fıstık (adaçayı yeşili)', 'swatch' => '#e6e9dc', 'accent' => '#7a8a55'],
			'kakao' => ['label' => 'Kakao (koyu)', 'swatch' => '#3a2216', 'accent' => '#e3c28d'],
			'tereyagi' => ['label' => 'Tereyağı (krem)', 'swatch' => '#f6efdf', 'accent' => '#b8955a'],
			'karamel' => ['label' => 'Ceviz', 'swatch' => '#ead9c3', 'accent' => '#9c6b3f'],
			'nane' => ['label' => 'Mağaza yeşili', 'swatch' => '#e0e8e2', 'accent' => '#6e8b78'],
			'visne' => ['label' => 'Bordo (koyu)', 'swatch' => '#4a1b21', 'accent' => '#e0b7a8'],
			'tahin' => ['label' => 'Tahin', 'swatch' => '#ede6db', 'accent' => '#9a8468'],
			'bogurtlen' => ['label' => 'Mürdüm', 'swatch' => '#e8e0e6', 'accent' => '#8a6a86'],
			'bayram' => ['label' => 'Espresso (koyu)', 'swatch' => '#2b160a', 'accent' => '#f5d7a1'],
		];
	}

	/**
	 * Kategori adına göre akıllı varsayılan palet (ayar yapılmasa da vitrinler farklı görünür).
	 */
	public static function guessPalette(string $categoryName, int $index = 0): string
	{
		$name = mb_strtolower($categoryName, 'UTF-8');
		$map = [
			'kerebiç' => 'fistik',
			'kerebic' => 'fistik',
			'fıstık' => 'fistik',
			'fistik' => 'fistik',
			'bomba' => 'visne',
			'vişne' => 'visne',
			'visne' => 'visne',
			'frambuaz' => 'bogurtlen',
			'böğürtlen' => 'bogurtlen',
			'bayram' => 'bayram',
			'paket' => 'bayram',
			'kutu' => 'bayram',
			'hediye' => 'bayram',
			'kurumsal' => 'bayram',
			'tahin' => 'tahin',
			'kahke' => 'susam',
			'susam' => 'susam',
			'simit' => 'susam',
			'çubuk' => 'susam',
			'tuzlu' => 'tereyagi',
			'peynir' => 'tereyagi',
			'poğaça' => 'tereyagi',
			'tarsus' => 'karamel',
			'kömbe' => 'karamel',
			'kombe' => 'karamel',
			'çörek' => 'karamel',
			'ceviz' => 'karamel',
			'spesiyal' => 'nane',
			'tartolet' => 'nane',
			'pasta' => 'kakao',
			'şekerli' => 'kakao',
			'çikolata' => 'kakao',
			'kakao' => 'kakao',
			'kurabiye' => 'kakao',
		];

		foreach ($map as $needle => $palette) {
			if (mb_strpos($name, $needle) !== false) {
				return $palette;
			}
		}

		$keys = ['susam', 'fistik', 'kakao', 'tereyagi', 'karamel', 'nane', 'tahin', 'visne'];

		return $keys[$index % count($keys)];
	}

	/** @return list<string> */
	private static function parseImageList(string $raw): array
	{
		$out = [];

		foreach (preg_split('/[\r\n,]+/', $raw) ?: [] as $url) {
			$url = trim($url);

			if ($url !== '' && preg_match('#^(https?://|/)#i', $url)) {
				$out[] = $url;
			}

			if (count($out) >= 3) {
				break;
			}
		}

		return $out;
	}

	/**
	 * Logo: admin › Ayarlar › Logo'dan yüklenmiş özel logo varsa o, yoksa temanın Antepli Ustalar logosu.
	 */
	public static function logoUrl(): string
	{
		global $domain;

		$stored = trim((string) Settings::get('LOGO_HEADER'));

		if ($stored !== '' && class_exists('SiteAssets', false) && SiteAssets::logoExists('header')) {
			return SiteAssets::resolveLogoUrl('header');
		}

		return rtrim((string) $domain, '/') . '/templates/' . self::THEME . '/img/logo.png';
	}

	/** @var array<int, string>|null */
	private static ?array $autoPalettes = null;

	/**
	 * Tüm kategoriler için ada göre otomatik palet (admin ve mağaza aynı sonucu görür).
	 *
	 * @return array<int, string>
	 */
	public static function autoPalettes(): array
	{
		if (self::$autoPalettes !== null) {
			return self::$autoPalettes;
		}

		$rows = DB::execute('SELECT id_category, category_name FROM categories ORDER BY id_category ASC') ?: [];
		$out = [];
		$index = 0;

		foreach ($rows as $row) {
			$out[(int) $row['id_category']] = self::guessPalette((string) $row['category_name'], $index);
			$index++;
		}

		self::$autoPalettes = $out;

		return $out;
	}

	/** @return array<int, array<string, string>> */
	public static function getStyles(): array
	{
		$rows = DB::execute('SELECT * FROM `' . self::TABLE . '`') ?: [];
		$out = [];

		foreach ($rows as $row) {
			$out[(int) $row['id_category']] = [
				'palette' => (string) $row['palette'],
				'emoji' => (string) $row['emoji'],
				'tagline' => (string) $row['tagline'],
				'ribbon' => (string) ($row['ribbon'] ?? ''),
			];
		}

		return $out;
	}

	/** @param array<int|string, mixed> $rows */
	public static function saveStyles(array $rows): void
	{
		$palettes = self::palettes();

		foreach ($rows as $idCategory => $row) {
			$idCategory = (int) $idCategory;

			if ($idCategory <= 0 || !is_array($row)) {
				continue;
			}

			$palette = (string) ($row['palette'] ?? '');
			$palette = isset($palettes[$palette]) ? $palette : '';
			$emoji = mb_substr(trim(strip_tags((string) ($row['emoji'] ?? ''))), 0, 8);
			$tagline = mb_substr(trim(strip_tags((string) ($row['tagline'] ?? ''))), 0, 190);
			$ribbon = mb_substr(trim(strip_tags((string) ($row['ribbon'] ?? ''))), 0, 64);

			DB::execute(
				'INSERT INTO `' . self::TABLE . '` (id_category, palette, emoji, tagline, ribbon, date_upd)
				 VALUES (?, ?, ?, ?, ?, NOW())
				 ON DUPLICATE KEY UPDATE palette = VALUES(palette), emoji = VALUES(emoji),
				 tagline = VALUES(tagline), ribbon = VALUES(ribbon), date_upd = NOW()',
				[$idCategory, $palette, $emoji, $tagline, $ribbon]
			);

			if (array_key_exists('show_on_home', $row) || isset($row['_home_present'])) {
				Category::setShowOnHome($idCategory, !empty($row['show_on_home']));
			}
		}

		self::$frontConfig = null;
		self::$autoPalettes = null;
	}

	/** @return list<array<string, mixed>> */
	public static function getAdminCategories(): array
	{
		$rows = DB::execute(
			'SELECT id_category, id_parent, category_name, category_link, active, show_on_home, home_position
			 FROM categories ORDER BY show_on_home DESC, home_position ASC, category_name ASC'
		) ?: [];
		$styles = self::getStyles();
		$auto = self::autoPalettes();
		$rootId = class_exists('Category', false) ? Category::getRootId() : 0;
		$out = [];

		foreach ($rows as $row) {
			$id = (int) $row['id_category'];

			if ($id === $rootId) {
				continue;
			}

			$style = $styles[$id] ?? ['palette' => '', 'emoji' => '', 'tagline' => '', 'ribbon' => ''];
			$row['style'] = $style;
			$row['auto_palette'] = $auto[$id] ?? self::guessPalette((string) $row['category_name']);
			$out[] = $row;
		}

		return $out;
	}

	/**
	 * Mağaza şablonuna verilen {$antepli} dizisi.
	 *
	 * @return array<string, mixed>
	 */
	public static function getFrontConfig(): array
	{
		if (self::$frontConfig !== null) {
			return self::$frontConfig;
		}

		$settings = self::getSettings();
		$styles = self::getStyles();

		// Ayar yapılmamış kategoriler için ada göre otomatik palet
		foreach (self::autoPalettes() as $id => $autoPalette) {
			$current = $styles[$id] ?? ['palette' => '', 'emoji' => '', 'tagline' => '', 'ribbon' => ''];

			if ($current['palette'] === '') {
				$current['palette'] = $autoPalette;
			}

			$styles[$id] = $current;
		}

		$whatsapp = (string) ($settings['AU_WHATSAPP'] ?? '');
		$waLink = '';

		if ($whatsapp !== '') {
			$waLink = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode((string) ($settings['AU_WHATSAPP_TEXT'] ?? ''));
		}

		$marquee = array_values(array_filter(array_map('trim', explode('•', (string) ($settings['AU_MARQUEE'] ?? '')))));

		self::$frontConfig = [
			'enabled' => true,
			'version' => self::VERSION,
			'announce' => $settings['AU_ANNOUNCE'],
			'hero_eyebrow' => $settings['AU_HERO_EYEBROW'],
			'hero_title' => $settings['AU_HERO_TITLE'],
			'hero_text' => $settings['AU_HERO_TEXT'],
			'hero_cta' => $settings['AU_HERO_CTA'],
			'hero_cta2' => $settings['AU_HERO_CTA2'],
			'hero_cta2_url' => $settings['AU_HERO_CTA2_URL'],
			'badges' => array_values(array_filter([$settings['AU_BADGE_1'], $settings['AU_BADGE_2'], $settings['AU_BADGE_3']])),
			'showcase_title' => $settings['AU_SHOWCASE_TITLE'],
			'showcase_text' => $settings['AU_SHOWCASE_TEXT'],
			'whatsapp_link' => $waLink,
			'show_home_slider' => $settings['AU_SHOW_HOME_SLIDER'] === '1',
			'show_sprinkles' => $settings['AU_SHOW_SPRINKLES'] === '1',
			'show_marquee' => $settings['AU_SHOW_MARQUEE'] === '1',
			'marquee' => $marquee,
			'footer_text' => $settings['AU_FOOTER_TEXT'],
			'logo_url' => self::logoUrl(),
			'hero_images' => self::parseImageList((string) ($settings['AU_HERO_IMAGES'] ?? '')),
			'styles' => $styles,
		];

		return self::$frontConfig;
	}
}
