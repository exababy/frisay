<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

/**
 * İsteğe bağlı demo içerik: Antepli Ustalar ürün grupları + örnek ürünler.
 * Görseller markanın kendi videolarından alınmış karelerdir (assets/demo/).
 * Fiyatlar örnektir. Aynı link ile kayıt varsa tekrar oluşturmaz.
 */
class AntepliVitrinDemo
{
	/** @return array<string, array{name: string, palette: string, emoji: string, tagline: string, ribbon: string, items: list<array{0: string, 1: float, 2: float, 3: string, 4: string, 5: string}>}> */
	private static function data(): array
	{
		// [ürün adı, fiyat, eski fiyat, kısa açıklama, etiket, demo görseli]
		return [
			'kerebic' => [
				'name' => 'Kerebiç',
				'palette' => 'fistik',
				'emoji' => '🌰',
				'tagline' => 'Bol Antep fıstıklı, bir kere yiyen müdavimi olur',
				'ribbon' => 'İmza lezzet',
				'items' => [
					['Fıstıklı Kerebiç (1 kg)', 650, 0, 'Bol Antep fıstığı dolgulu, oluklu kabuk kerebiç.', 'Çok Satan', 'kerebic'],
					['Fıstıklı Kerebiç (500 g)', 340, 0, 'Yarım kiloluk paket; çay saatinin yıldızı.', '', 'kerebic-ic'],
					['Cevizli Kerebiç (1 kg)', 560, 0, 'Ceviz içli klasik Antep kerebiçi.', 'Yeni', 'kerebic-tepsi'],
				],
			],
			'yoresel-kahkeler' => [
				'name' => 'Yöresel Kahkeler',
				'palette' => 'susam',
				'emoji' => '🥯',
				'tagline' => 'Antep\'in kahkeleri takvim dinlemez',
				'ribbon' => '',
				'items' => [
					['Köylü Kahkesi (1 kg)', 300, 0, 'Mahlepli, susamlı ve çörek otlu halka kahke.', 'Çok Satan', 'koylu-kahke'],
					['Çubuk Kahke (1 kg)', 300, 0, 'Anteplilerin vazgeçilmezi, çıtır çubuk kahke.', '', 'cubuk-kahke'],
					['Susamlı Çubuk (1 kg)', 280, 0, 'Bol susamlı, çaya birebir.', '', 'susamli-cubuk'],
					['Susamsız Çubuk (1 kg)', 270, 0, 'Sade, tereyağlı çubuk.', '', 'cubuk-sari'],
					['Kakaolu Halka (1 kg)', 300, 330, 'Kakaolu, yumuşak dokulu halka.', 'Fırsat', 'halka'],
				],
			],
			'sekerli-kuru-pasta' => [
				'name' => 'Şekerli Kuru Pasta',
				'palette' => 'kakao',
				'emoji' => '🍪',
				'tagline' => 'Bayram kuru pastasız olmaz',
				'ribbon' => '',
				'items' => [
					['Karışık Şekerli Kuru Pasta (1 kg)', 350, 0, 'Çikolatalı, fıstıklı, hindistan cevizli karışık tabak.', 'Çok Satan', 'cikolatali'],
					['Susamlı Kurabiye (1 kg)', 320, 0, 'Bol susamlı, çıtır kenarlı kurabiye.', '', 'susamli-kurabiye'],
					['Çikolatalı Kurabiye (500 g)', 190, 0, 'Bitter çikolata parçacıklı.', 'Yeni', 'cikolatali'],
				],
			],
			'tuzlu-kuru-pasta' => [
				'name' => 'Tuzlu Kuru Pasta',
				'palette' => 'tereyagi',
				'emoji' => '🧀',
				'tagline' => 'Peynirli, çörek otlu, susamlı… çayın yanına',
				'ribbon' => '',
				'items' => [
					['Karışık Tuzlu Kuru Pasta (1 kg)', 300, 0, 'Peynirli, zeytinli, çörek otlu karışık tuzlular.', 'Çok Satan', 'tuzlu'],
					['Peynirli Burma (1 kg)', 320, 0, 'Tereyağlı hamur, bol peynir.', '', 'tuzlu'],
				],
			],
			'tahinli-ve-corekler' => [
				'name' => 'Tahinli & Çörekler',
				'palette' => 'karamel',
				'emoji' => '🥐',
				'tagline' => 'Tahinli, Tarsus çöreği ve kömbe… hepsi burada',
				'ribbon' => '',
				'items' => [
					['Tahinli Çörek (1 kg)', 340, 0, 'Bol tahinli, karamelize kenarlı.', '', 'tahinli'],
					['Tarsus Çöreği (1 kg)', 360, 0, 'Çıtır, susamlı yöresel çörek.', 'Yeni', 'tahinli'],
					['Kömbe (1 kg)', 320, 0, 'Mahlepli, tarçınlı geleneksel kömbe.', '', ''],
				],
			],
			'izmir-bombasi' => [
				'name' => 'İzmir Bombası',
				'palette' => 'visne',
				'emoji' => '💣',
				'tagline' => 'Geleneği bozduk: 5 farklı İzmir bombası',
				'ribbon' => '5 çeşit',
				'items' => [
					['Klasik İzmir Bombası', 45, 0, 'İçi akışkan çikolata dolgulu.', '', 'bomba-klasik'],
					['Antep Fıstıklı İzmir Bombası', 55, 0, 'Fıstık hamuru, çikolata dolgu.', 'Çok Satan', 'bomba-fistik'],
					['Vişneli İzmir Bombası', 50, 0, 'Vişne ve çikolata bir arada.', '', 'bomba-visne'],
					['Frambuazlı İzmir Bombası', 50, 0, 'Frambuazlı hamur, beyaz çikolata.', 'Yeni', 'bomba-frambuaz'],
					['Çikolatalı İzmir Bombası', 50, 0, 'İçi de dışı da çikolatalı.', '', ''],
				],
			],
			'spesiyal-ve-tartolet' => [
				'name' => 'Spesiyal & Tartolet',
				'palette' => 'nane',
				'emoji' => '🍓',
				'tagline' => 'Meyveli tartoletler ve spesiyal çeşitler',
				'ribbon' => '',
				'items' => [
					['Meyveli Tartolet Kutusu (12\'li)', 480, 0, 'Çilekli, karamelli, fıstıklı karışık tartolet.', 'Çok Satan', 'tartolet'],
					['Spesiyal Karışık (1 kg)', 520, 0, 'Ustalarımızın seçtiği spesiyal çeşitler.', '', 'tartolet'],
				],
			],
			'bayram-paketleri' => [
				'name' => 'Bayram Paketleri',
				'palette' => 'bayram',
				'emoji' => '🎁',
				'tagline' => 'Bayram, misafir ve kurumsal ikram kutuları',
				'ribbon' => 'Geçen yılın fiyatına',
				'items' => [
					['Şekerli Bayram Paketi (1 kg)', 350, 400, 'Karışık şekerli kuru pastalar, hediyelik kutuda.', 'Fırsat', 'paket'],
					['Tuzlu Bayram Paketi (1 kg)', 300, 0, 'Karışık tuzlu kuru pastalar, hediyelik kutuda.', '', 'paket'],
					['Karma Bayram Paketi (2 kg)', 640, 700, '1 kg şekerli + 1 kg tuzlu kuru pasta.', '', 'paket'],
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
				[$name, $price, $oldPrice, $short, $label, $photo] = $item;
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
					'description' => '<p>' . htmlspecialchars($short, ENT_QUOTES, 'UTF-8') . '</p><p>Antepli ustalarımızın elinden, her gün sıcak fırından. Hava almayan kapta oda sıcaklığında saklayınız.</p>',
					'product_link' => $slug,
					'price' => $price,
					'old_price' => $oldPrice,
					'doviz' => 'try',
					'doviz_price' => $price,
					'doviz_old_price' => $oldPrice,
					'vat' => 1,
					'active' => 1,
					'stock' => 50,
					'cargo_day' => 1,
					'label' => $label,
					'stock_code' => 'AU-' . strtoupper(substr(md5($slug), 0, 6)),
				];

				if (!DB::insert('products', self::filterColumns('products', $row))) {
					continue;
				}

				$idProduct = (int) DB::getValue('SELECT id_product FROM products WHERE product_link = ? LIMIT 1', [$slug]);

				if ($idProduct <= 0) {
					continue;
				}

				$createdProducts++;
				if ($photo !== '') {
					self::attachPhoto($idProduct, $photo);
				}
			}
		}

		if ($styles !== []) {
			AntepliVitrinService::saveStyles($styles);
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
		$id = (int) DB::getValue('SELECT id_brand FROM brands WHERE brand_link = ? LIMIT 1', ['antepli-ustalar']);

		if ($id > 0) {
			return $id;
		}

		DB::insert('brands', [
			'brand_name' => 'Antepli Ustalar',
			'brand_link' => 'antepli-ustalar',
			'active' => 1,
		]);

		return (int) DB::getValue('SELECT id_brand FROM brands WHERE brand_link = ? LIMIT 1', ['antepli-ustalar']);
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

	private static function attachPhoto(int $idProduct, string $photo): void
	{
		$src = dirname(__DIR__) . '/assets/demo/' . preg_replace('/[^a-z0-9\-]/', '', $photo) . '.jpg';
		$dir = dirname(__DIR__, 3) . '/img/products';

		if (!is_file($src) || !is_dir($dir) || !is_writable($dir)) {
			return;
		}

		DB::insert('images', ['id_product' => $idProduct, 'cover' => 1]);
		$idImage = (int) DB::getValue('SELECT id_image FROM images WHERE id_product = ? ORDER BY id_image DESC LIMIT 1', [$idProduct]);

		if ($idImage > 0) {
			@copy($src, $dir . '/' . $idImage . '.jpg');
		}
	}
}
