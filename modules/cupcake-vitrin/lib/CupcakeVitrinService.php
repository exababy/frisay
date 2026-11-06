<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

/**
 * Cupcake Vitrin ayarları, kategori vitrin stilleri ve otomatik şema göçü.
 */
class CupcakeVitrinService
{
	/** Modül sürümü — her yayında artırın; boot() farkı görüp migrate() çalıştırır. */
	public const VERSION = '1.0.0';

	/** Şema sürümü — tablo/kolon değişince artırın. */
	public const SCHEMA_VERSION = 1;

	public const THEME = 'cupcake';
	public const FALLBACK_THEME = 'theme4';
	public const KEY_SCHEMA = 'CV_SCHEMA_VERSION';
	public const TABLE = 'cupcake_vitrin_styles';

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

		DB::execute('UPDATE modules SET version = ? WHERE name = ?', [self::VERSION, 'cupcake-vitrin']);
		Settings::set(self::KEY_SCHEMA, self::SCHEMA_VERSION . '@' . self::VERSION);
	}

	/* ------------------------------------------------------------------ */
	/* Tema yedeği (template fallback)                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * cupcake temasında olmayan bir .tpl istenirse (ör. FriSay güncellemesiyle gelen yeni
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
			// _THEME_BASE_DIR_ göreli ("templates/cupcake/x.tpl") veya mutlak olabilir
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
			'CV_ANNOUNCE' => 'Bugün saat 14:00\'e kadar verilen siparişler aynı gün teslim 🧁',
			'CV_HERO_EYEBROW' => 'Her sabah taze, el yapımı',
			'CV_HERO_TITLE' => 'Mutluluğun en tatlı hali',
			'CV_HERO_TEXT' => 'Islak keklerden çikolatalı cupcake\'lere kadar her lezzet kendi vitrininde. Kaydır, seç, sepete at.',
			'CV_HERO_CTA' => 'Vitrine göz at',
			'CV_HERO_CTA2' => 'Özel sipariş ver',
			'CV_HERO_CTA2_URL' => 'contact',
			'CV_BADGE_1' => '%100 tereyağı',
			'CV_BADGE_2' => 'Katkısız',
			'CV_BADGE_3' => 'Aynı gün teslimat',
			'CV_SHOWCASE_TITLE' => 'Vitrinlerimiz',
			'CV_SHOWCASE_TEXT' => 'Her kategori ayrı bir vitrin. Rafları kaydırarak tüm lezzetleri keşfet.',
			'CV_WHATSAPP' => '',
			'CV_WHATSAPP_TEXT' => 'Merhaba, cupcake siparişi vermek istiyorum.',
			'CV_SHOW_HOME_SLIDER' => '1',
			'CV_SHOW_SPRINKLES' => '1',
			'CV_SHOW_MARQUEE' => '1',
			'CV_MARQUEE' => 'Islak Kek • Çikolatalı Cupcake • Red Velvet • Frambuazlı • Karamelli • Fıstıklı • Doğum Günü Kutuları',
			'CV_FOOTER_TEXT' => 'Butik pastanemizde her gün taze pişen cupcake ve kekler, özenle kutulanıp kapınıza gelir.',
		];
	}

	/** @return array<string, string> */
	public static function getSettings(): array
	{
		$rows = DB::execute("SELECT title, value FROM settings WHERE title LIKE 'CV\\_%'") ?: [];
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
		$checkboxes = ['CV_SHOW_HOME_SLIDER', 'CV_SHOW_SPRINKLES', 'CV_SHOW_MARQUEE'];

		foreach (self::settingDefaults() as $key => $default) {
			if (in_array($key, $checkboxes, true)) {
				Settings::set($key, !empty($values[$key]) ? '1' : '0');
				continue;
			}

			$value = trim((string) ($values[$key] ?? ''));

			if ($key === 'CV_WHATSAPP') {
				$value = preg_replace('/\D+/', '', $value) ?? '';
			}

			if ($key === 'CV_HERO_CTA2_URL') {
				$value = self::sanitizeLink($value);
			}

			Settings::set($key, mb_substr(strip_tags($value), 0, 600));
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
			'cilek' => ['label' => 'Çilekli Pembe', 'swatch' => '#ffd3de', 'accent' => '#e2557b'],
			'cikolata' => ['label' => 'Bitter Çikolata', 'swatch' => '#4a2c1f', 'accent' => '#f0b979'],
			'vanilya' => ['label' => 'Vanilya Kreması', 'swatch' => '#fff1d6', 'accent' => '#d79a3a'],
			'fistik' => ['label' => 'Antep Fıstığı', 'swatch' => '#e1efcf', 'accent' => '#6f9a45'],
			'yaban-mersini' => ['label' => 'Yaban Mersini', 'swatch' => '#dfe2ff', 'accent' => '#5960c8'],
			'karamel' => ['label' => 'Tuzlu Karamel', 'swatch' => '#f4d6b6', 'accent' => '#b8692c'],
			'red-velvet' => ['label' => 'Red Velvet', 'swatch' => '#7d1626', 'accent' => '#ffd5dc'],
			'limon' => ['label' => 'Limonlu Ferah', 'swatch' => '#fff5b0', 'accent' => '#c9a114'],
			'lavanta' => ['label' => 'Lavanta', 'swatch' => '#ebdfff', 'accent' => '#8d63cf'],
			'nane' => ['label' => 'Naneli Pastel', 'swatch' => '#d4f4ea', 'accent' => '#2f9d7f'],
		];
	}

	/**
	 * Kategori adına göre akıllı varsayılan palet (ayar yapılmasa da vitrinler farklı görünür).
	 */
	public static function guessPalette(string $categoryName, int $index = 0): string
	{
		$name = mb_strtolower($categoryName, 'UTF-8');
		$map = [
			'red velvet' => 'red-velvet',
			'kırmızı' => 'red-velvet',
			'ıslak' => 'cikolata',
			'islak' => 'cikolata',
			'çikolata' => 'cikolata',
			'cikolata' => 'cikolata',
			'kakao' => 'cikolata',
			'brownie' => 'cikolata',
			'çilek' => 'cilek',
			'cilek' => 'cilek',
			'frambuaz' => 'cilek',
			'meyve' => 'cilek',
			'vişne' => 'cilek',
			'fıstık' => 'fistik',
			'fistik' => 'fistik',
			'vegan' => 'fistik',
			'glutensiz' => 'nane',
			'nane' => 'nane',
			'vanilya' => 'vanilya',
			'klasik' => 'vanilya',
			'karamel' => 'karamel',
			'muz' => 'limon',
			'limon' => 'limon',
			'portakal' => 'limon',
			'yaban' => 'yaban-mersini',
			'mersin' => 'yaban-mersini',
			'böğürtlen' => 'lavanta',
			'lavanta' => 'lavanta',
			'doğum' => 'lavanta',
			'özel gün' => 'lavanta',
			'kutu' => 'karamel',
			'hediye' => 'karamel',
		];

		foreach ($map as $needle => $palette) {
			if (mb_strpos($name, $needle) !== false) {
				return $palette;
			}
		}

		$keys = ['cilek', 'vanilya', 'fistik', 'yaban-mersini', 'karamel', 'lavanta', 'nane', 'limon'];

		return $keys[$index % count($keys)];
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
	 * Mağaza şablonuna verilen {$cupcake} dizisi.
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

		$whatsapp = (string) ($settings['CV_WHATSAPP'] ?? '');
		$waLink = '';

		if ($whatsapp !== '') {
			$waLink = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode((string) ($settings['CV_WHATSAPP_TEXT'] ?? ''));
		}

		$marquee = array_values(array_filter(array_map('trim', explode('•', (string) ($settings['CV_MARQUEE'] ?? '')))));

		self::$frontConfig = [
			'enabled' => true,
			'version' => self::VERSION,
			'announce' => $settings['CV_ANNOUNCE'],
			'hero_eyebrow' => $settings['CV_HERO_EYEBROW'],
			'hero_title' => $settings['CV_HERO_TITLE'],
			'hero_text' => $settings['CV_HERO_TEXT'],
			'hero_cta' => $settings['CV_HERO_CTA'],
			'hero_cta2' => $settings['CV_HERO_CTA2'],
			'hero_cta2_url' => $settings['CV_HERO_CTA2_URL'],
			'badges' => array_values(array_filter([$settings['CV_BADGE_1'], $settings['CV_BADGE_2'], $settings['CV_BADGE_3']])),
			'showcase_title' => $settings['CV_SHOWCASE_TITLE'],
			'showcase_text' => $settings['CV_SHOWCASE_TEXT'],
			'whatsapp_link' => $waLink,
			'show_home_slider' => $settings['CV_SHOW_HOME_SLIDER'] === '1',
			'show_sprinkles' => $settings['CV_SHOW_SPRINKLES'] === '1',
			'show_marquee' => $settings['CV_SHOW_MARQUEE'] === '1',
			'marquee' => $marquee,
			'footer_text' => $settings['CV_FOOTER_TEXT'],
			'styles' => $styles,
		];

		return self::$frontConfig;
	}
}
