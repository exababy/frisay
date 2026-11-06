<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

/**
 * İsteğe bağlı demo içerik: cupcake kategorileri + örnek ürünler + çizilmiş ürün görselleri.
 * Aynı link ile kayıt varsa tekrar oluşturmaz (iki kez basılsa da çoğaltmaz).
 */
class CupcakeVitrinDemo
{
	/** @return array<string, array{name: string, palette: string, emoji: string, tagline: string, ribbon: string, items: list<array{0: string, 1: float, 2: float, 3: string, 4: string}>}> */
	private static function data(): array
	{
		// [ürün adı, fiyat, eski fiyat, kısa açıklama, etiket]
		return [
			'islak-kekler' => [
				'name' => 'Islak Kekler',
				'palette' => 'cikolata',
				'emoji' => '🍫',
				'tagline' => 'Sıcak çikolata sosuyla ıslatılmış, yumuşacık dilimler',
				'ribbon' => 'Günün favorisi',
				'items' => [
					['Klasik Islak Kek', 85, 0, 'Bol kakaolu kek, sıcak çikolata sos ve hindistan cevizi.', 'Çok Satan'],
					['Fındıklı Islak Kek', 95, 110, 'Kavrulmuş fındık parçaları ile çıtır dokunuş.', ''],
					['Muzlu Islak Kek', 95, 0, 'Taze muz dilimleri ve bitter sos.', 'Yeni'],
					['Beyaz Çikolatalı Islak Kek', 105, 0, 'Beyaz çikolata ganaj ile hafif ve tatlı.', ''],
					['Islak Kek Kutusu (6\'lı)', 480, 540, 'Paylaşmalık 6 dilim, hediyelik kutuda.', 'Fırsat'],
				],
			],
			'cikolatali-cupcake' => [
				'name' => 'Çikolatalı Cupcake',
				'palette' => 'karamel',
				'emoji' => '🧁',
				'tagline' => 'Belçika çikolatası, ganaj ve kakao kreması',
				'ribbon' => '',
				'items' => [
					['Triple Chocolate Cupcake', 75, 0, 'Çikolatalı kek, çikolata dolgu, çikolata krema.', 'Çok Satan'],
					['Tuzlu Karamelli Cupcake', 79, 0, 'Ev yapımı tuzlu karamel ve kakaolu krema.', ''],
					['Oreo Cupcake', 79, 89, 'Kurabiye parçacıklı vanilyalı krema.', ''],
					['Fıstıklı Çikolata Cupcake', 89, 0, 'Antep fıstığı kreması ve bitter çikolata.', 'Yeni'],
					['Brownie Cupcake', 82, 0, 'İçi akışkan brownie, üstü parlak ganaj.', ''],
				],
			],
			'meyveli-cupcake' => [
				'name' => 'Meyveli Cupcake',
				'palette' => 'cilek',
				'emoji' => '🍓',
				'tagline' => 'Mevsim meyveleri, hafif kremalar',
				'ribbon' => 'Mevsimlik',
				'items' => [
					['Çilekli Cupcake', 72, 0, 'Taze çilek püresi ve mascarpone krema.', 'Çok Satan'],
					['Frambuazlı Cupcake', 78, 0, 'Frambuaz dolgulu, beyaz çikolata kremalı.', ''],
					['Yaban Mersinli Cupcake', 78, 85, 'Yaban mersini parçacıklı limonlu kek.', ''],
					['Limonlu Cupcake', 69, 0, 'Limon kremalı ferah lezzet.', 'Yeni'],
					['Vişneli Cupcake', 74, 0, 'Vişne reçeli dolgulu kakaolu kek.', ''],
				],
			],
			'red-velvet' => [
				'name' => 'Red Velvet',
				'palette' => 'red-velvet',
				'emoji' => '❤️',
				'tagline' => 'Kadife dokulu kırmızı kek, krem peynirli krema',
				'ribbon' => '',
				'items' => [
					['Red Velvet Cupcake', 79, 0, 'Klasik krem peynirli kremasıyla.', 'Çok Satan'],
					['Red Velvet Kalp Cupcake', 89, 0, 'Sevgiliye özel kalp süslemeli.', ''],
					['Red Velvet Mini Kutu (9\'lu)', 390, 450, 'Mini boy 9 adet, paylaşmalık.', 'Fırsat'],
					['Beyaz Çikolatalı Red Velvet', 85, 0, 'Beyaz çikolata parçacıklı.', ''],
				],
			],
			'ozel-gun-cupcake' => [
				'name' => 'Özel Gün Cupcake',
				'palette' => 'lavanta',
				'emoji' => '🎉',
				'tagline' => 'Doğum günü, baby shower ve kutlamalar için',
				'ribbon' => 'Kişiye özel',
				'items' => [
					['Doğum Günü Cupcake Kutusu (12\'li)', 840, 960, 'Renkli şeker süslemeli 12 adet cupcake.', 'Fırsat'],
					['Baby Shower Cupcake (6\'lı)', 450, 0, 'Pembe / mavi tema seçenekli.', ''],
					['İsimli Cupcake', 95, 0, 'Üzerine isim yazılı şeker hamuru plaka.', 'Yeni'],
					['Gökkuşağı Cupcake', 85, 0, 'Katmanlı renkli kek, bulut krema.', ''],
					['Unicorn Cupcake', 95, 0, 'Simli boynuz ve pastel krema.', ''],
				],
			],
		];
	}

	/** @return array{success: bool, message: string} */
	public static function load(): array
	{
		$rootId = Category::getRootId();
		$brandId = self::ensureBrand();

		if ($brandId <= 0) {
			return ['success' => false, 'message' => 'Marka kaydı oluşturulamadı.'];
		}

		$createdCats = 0;
		$createdProducts = 0;
		$position = 0;
		$styles = [];

		foreach (self::data() as $link => $cat) {
			$position++;
			$idCategory = (int) DB::getValue('SELECT id_category FROM categories WHERE category_link = ? LIMIT 1', [$link]);

			if ($idCategory <= 0) {
				DB::insert('categories', [
					'id_parent' => $rootId,
					'category_name' => $cat['name'],
					'category_link' => $link,
					'meta_title' => $cat['name'],
					'meta_description' => $cat['tagline'],
					'active' => 1,
					'show_on_home' => 1,
					'home_position' => $position,
				]);
				$idCategory = (int) DB::getValue('SELECT id_category FROM categories WHERE category_link = ? LIMIT 1', [$link]);
				$createdCats++;
			} else {
				DB::execute('UPDATE categories SET show_on_home = 1, home_position = ? WHERE id_category = ?', [$position, $idCategory]);
			}

			if ($idCategory <= 0) {
				continue;
			}

			$styles[$idCategory] = [
				'palette' => $cat['palette'],
				'emoji' => $cat['emoji'],
				'tagline' => $cat['tagline'],
				'ribbon' => $cat['ribbon'],
			];

			$i = 0;

			foreach ($cat['items'] as $item) {
				$i++;
				[$name, $price, $oldPrice, $short, $label] = $item;
				$slug = self::slugify($name);
				$exists = (int) DB::getValue('SELECT id_product FROM products WHERE product_link = ? LIMIT 1', [$slug]);

				if ($exists > 0) {
					continue;
				}

				$row = [
					'id_category' => $idCategory,
					'id_brand' => $brandId,
					'product_name' => $name,
					'short_description' => $short,
					'meta_title' => $name,
					'meta_description' => $short,
					'description' => '<p>' . htmlspecialchars($short, ENT_QUOTES, 'UTF-8') . '</p><p>Her gün taze pişirilir, tek tek elde süslenir ve özel kutusunda gönderilir. Buzdolabında 3 gün tazeliğini korur; servisten 15 dakika önce çıkarmanızı öneririz.</p>',
					'product_link' => $slug,
					'price' => $price,
					'old_price' => $oldPrice,
					'doviz' => 'try',
					'doviz_price' => $price,
					'doviz_old_price' => $oldPrice,
					'vat' => 10,
					'active' => 1,
					'stock' => 50,
					'cargo_day' => 1,
					'label' => $label,
					'stock_code' => 'CV-' . strtoupper(substr(md5($slug), 0, 6)),
				];

				if (!DB::insert('products', self::filterColumns('products', $row))) {
					continue;
				}

				$idProduct = (int) DB::getValue('SELECT id_product FROM products WHERE product_link = ? LIMIT 1', [$slug]);

				if ($idProduct <= 0) {
					continue;
				}

				$createdProducts++;
				self::createImage($idProduct, $cat['palette'], $i);
			}
		}

		if ($styles !== []) {
			CupcakeVitrinService::saveStyles($styles);
		}

		if (class_exists('Performance', false)) {
			Performance::clearCaches();
		}

		return [
			'success' => true,
			'message' => 'Demo vitrin hazır: ' . $createdCats . ' kategori, ' . $createdProducts . ' ürün eklendi.',
		];
	}

	private static function ensureBrand(): int
	{
		$id = (int) DB::getValue('SELECT id_brand FROM brands WHERE brand_link = ? LIMIT 1', ['cupcake-atolyesi']);

		if ($id > 0) {
			return $id;
		}

		DB::insert('brands', [
			'brand_name' => 'Cupcake Atölyesi',
			'brand_link' => 'cupcake-atolyesi',
			'active' => 1,
		]);

		return (int) DB::getValue('SELECT id_brand FROM brands WHERE brand_link = ? LIMIT 1', ['cupcake-atolyesi']);
	}

	/**
	 * Şemada olmayan kolonları ayıklar (eski/yeni FriSay sürümleriyle uyum).
	 *
	 * @param array<string, mixed> $row
	 * @return array<string, mixed>
	 */
	private static function filterColumns(string $table, array $row): array
	{
		$cols = DB::execute('SHOW COLUMNS FROM `' . preg_replace('/[^a-z0-9_]/', '', $table) . '`') ?: [];
		$allowed = array_flip(array_column($cols, 'Field'));

		return array_intersect_key($row, $allowed);
	}

	private static function slugify(string $text): string
	{
		$map = ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u'];
		$text = strtr($text, $map);
		$text = strtolower($text);
		$text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

		return trim($text, '-');
	}

	/* ------------------------------------------------------------------ */
	/* GD ile cupcake çizimi                                               */
	/* ------------------------------------------------------------------ */

	/** @var array<string, array{bg: string, bg2: string, cake: string, cream: string, cream2: string, wrap: string, wrap2: string, top: string}> */
	private const LOOKS = [
		'cikolata' => ['bg' => '#f6e7da', 'bg2' => '#ead2bd', 'cake' => '#5a3221', 'cream' => '#6b3b26', 'cream2' => '#8a5236', 'wrap' => '#3f2418', 'wrap2' => '#57321f', 'top' => '#ffffff'],
		'karamel' => ['bg' => '#fbeee0', 'bg2' => '#f3d9bd', 'cake' => '#6b3d22', 'cream' => '#d99a5b', 'cream2' => '#efc28c', 'wrap' => '#b8692c', 'wrap2' => '#d4844a', 'top' => '#5a3221'],
		'cilek' => ['bg' => '#fff0f3', 'bg2' => '#ffd9e2', 'cake' => '#f3c98b', 'cream' => '#ff9bb5', 'cream2' => '#ffc4d3', 'wrap' => '#e2557b', 'wrap2' => '#f07a9a', 'top' => '#d7263d'],
		'red-velvet' => ['bg' => '#fdeef0', 'bg2' => '#f8d5da', 'cake' => '#9b1b30', 'cream' => '#fff7f2', 'cream2' => '#ffffff', 'wrap' => '#7d1626', 'wrap2' => '#a32036', 'top' => '#c8102e'],
		'lavanta' => ['bg' => '#f5efff', 'bg2' => '#e6dbff', 'cake' => '#f3d39b', 'cream' => '#c9a7f5', 'cream2' => '#e3d0ff', 'wrap' => '#8d63cf', 'wrap2' => '#a985e0', 'top' => '#ff6fa8'],
		'vanilya' => ['bg' => '#fff8ea', 'bg2' => '#fdebc8', 'cake' => '#f0c27b', 'cream' => '#fff3d6', 'cream2' => '#ffffff', 'wrap' => '#d79a3a', 'wrap2' => '#e8b65e', 'top' => '#e2557b'],
		'fistik' => ['bg' => '#f1f8e8', 'bg2' => '#ddeccb', 'cake' => '#e8c68a', 'cream' => '#a8cf7e', 'cream2' => '#c9e5a8', 'wrap' => '#6f9a45', 'wrap2' => '#88b35c', 'top' => '#4b2e1f'],
	];

	private static function createImage(int $idProduct, string $palette, int $variant): void
	{
		if (!function_exists('imagecreatetruecolor')) {
			return;
		}

		$look = self::LOOKS[$palette] ?? self::LOOKS['vanilya'];
		$dir = dirname(__DIR__, 3) . '/img/products';

		if (!is_dir($dir) || !is_writable($dir)) {
			return;
		}

		DB::insert('images', ['id_product' => $idProduct, 'cover' => 1]);
		$idImage = (int) DB::getValue('SELECT id_image FROM images WHERE id_product = ? ORDER BY id_image DESC LIMIT 1', [$idProduct]);

		if ($idImage <= 0) {
			return;
		}

		$s = 2; // süper örnekleme
		$w = 800 * $s;
		$h = 800 * $s;
		$im = imagecreatetruecolor($w, $h);
		imageantialias($im, true);

		// Arka plan dikey degrade
		[$r1, $g1, $b1] = self::rgb($look['bg']);
		[$r2, $g2, $b2] = self::rgb($look['bg2']);

		for ($y = 0; $y < $h; $y++) {
			$t = $y / $h;
			$c = imagecolorallocate($im, (int) ($r1 + ($r2 - $r1) * $t), (int) ($g1 + ($g2 - $g1) * $t), (int) ($b1 + ($b2 - $b1) * $t));
			imageline($im, 0, $y, $w, $y, $c);
		}

		$cx = (int) ($w / 2);

		// Gölge
		imagefilledellipse($im, $cx, 650 * $s, 420 * $s, 60 * $s, self::col($im, '#000000', 105));

		// Kâğıt kalıp (yamuk) + çizgiler
		$wrapTop = 430 * $s;
		$wrapBottom = 650 * $s;
		$poly = [
			$cx - 190 * $s, $wrapTop,
			$cx + 190 * $s, $wrapTop,
			$cx + 140 * $s, $wrapBottom,
			$cx - 140 * $s, $wrapBottom,
		];
		self::polygon($im, $poly, self::col($im, $look['wrap']));

		for ($k = -6; $k <= 6; $k += 2) {
			$xt = $cx + $k * 30 * $s;
			$xb = $cx + $k * 22 * $s;
			$stripe = [$xt - 13 * $s, $wrapTop, $xt + 13 * $s, $wrapTop, $xb + 10 * $s, $wrapBottom, $xb - 10 * $s, $wrapBottom];
			self::polygon($im, $stripe, self::col($im, $look['wrap2']));
		}

		// Kek kubbesi
		imagefilledellipse($im, $cx, $wrapTop, 400 * $s, 90 * $s, self::col($im, $look['cake']));

		// Krema: üst üste halkalar (varyanta göre biraz farklı)
		$layers = [
			[0, 395, 380, 120],
			[0, 345, 330, 110],
			[0, 295, 260, 100],
			[0, 250, 180, 90],
			[0, 212, 100, 70],
		];
		$shift = ($variant % 2 === 0) ? 6 : -6;

		foreach ($layers as $idx => $layer) {
			[, $ly, $lw, $lh] = $layer;
			$color = $idx % 2 === 0 ? $look['cream'] : $look['cream2'];
			imagefilledellipse($im, $cx + $shift * $idx * $s, $ly * $s, $lw * $s, $lh * $s, self::col($im, $color));
		}

		// Parlama
		imagefilledellipse($im, $cx - 80 * $s, 330 * $s, 70 * $s, 26 * $s, self::col($im, '#ffffff', 80));

		// Süsleme: kiraz / çilek / şeker
		$topColor = self::col($im, $look['top']);

		if ($variant % 3 === 1) {
			imagefilledellipse($im, $cx + 10 * $s, 165 * $s, 70 * $s, 70 * $s, $topColor);
			imagesetthickness($im, 6 * $s);
			imagearc($im, $cx + 50 * $s, 120 * $s, 90 * $s, 120 * $s, 180, 260, self::col($im, '#4c7a2a'));
			imagefilledellipse($im, $cx - 5 * $s, 150 * $s, 18 * $s, 12 * $s, self::col($im, '#ffffff', 60));
		} elseif ($variant % 3 === 2) {
			self::polygon($im, [$cx - 40 * $s, 200 * $s, $cx + 40 * $s, 200 * $s, $cx, 120 * $s], $topColor);
			imagefilledellipse($im, $cx, 150 * $s, 26 * $s, 26 * $s, self::col($im, '#ffffff', 90));
		} else {
			imagefilledellipse($im, $cx, 175 * $s, 60 * $s, 50 * $s, $topColor);
		}

		// Rengarenk şekerler (deterministik)
		mt_srand($idProduct * 7 + $variant);
		$sprinkles = ['#ff5d8f', '#ffd166', '#06d6a0', '#118ab2', '#ffffff', '#ef476f', '#8338ec'];

		for ($n = 0; $n < 26; $n++) {
			$angle = mt_rand(0, 360);
			$rad = mt_rand(20, 170) * $s;
			$sx = (int) ($cx + cos(deg2rad($angle)) * $rad);
			$sy = (int) (300 * $s + sin(deg2rad($angle)) * $rad * 0.45);
			$len = 12 * $s;
			$a2 = deg2rad(mt_rand(0, 180));
			imagesetthickness($im, 7 * $s);
			imageline($im, $sx, $sy, (int) ($sx + cos($a2) * $len), (int) ($sy + sin($a2) * $len), self::col($im, $sprinkles[$n % count($sprinkles)]));
		}

		imagesetthickness($im, 1);

		$out = imagecreatetruecolor(800, 800);
		imagecopyresampled($out, $im, 0, 0, 0, 0, 800, 800, $w, $h);
		imagejpeg($out, $dir . '/' . $idImage . '.jpg', 88);
		imagedestroy($im);
		imagedestroy($out);
	}

	/**
	 * @param resource|\GdImage $im
	 * @param list<int|float> $points
	 */
	private static function polygon($im, array $points, int $color): void
	{
		$points = array_map('intval', $points);

		if (PHP_VERSION_ID >= 80000) {
			imagefilledpolygon($im, $points, $color);
		} else {
			imagefilledpolygon($im, $points, (int) (count($points) / 2), $color);
		}
	}

	/** @return array{0: int, 1: int, 2: int} */
	private static function rgb(string $hex): array
	{
		$hex = ltrim($hex, '#');

		return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
	}

	/** @param resource|\GdImage $im */
	private static function col($im, string $hex, int $alpha = 0): int
	{
		[$r, $g, $b] = self::rgb($hex);

		return $alpha > 0 ? imagecolorallocatealpha($im, $r, $g, $b, $alpha) : imagecolorallocate($im, $r, $g, $b);
	}
}
