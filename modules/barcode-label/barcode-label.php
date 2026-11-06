<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class BarcodeLabelModule extends ModuleBase
{
	public string $name = 'barcode-label';
	public string $title = 'Barkod & Etiket';
	public string $version = '1.0.0';
	public string $description = 'Ürün adı, fiyat, indirim ve barkodlu etiket tasarlayıp yazdırır (termal yazıcı veya A4 etiket kağıdı).';
	public string $author = 'FShop';

	public array $displayHooks = [];
	public array $defaultDisplayHooks = [];

	public array $adminStylesheets = ['label.css', 'admin.css'];
	public array $adminScripts = ['barcode.js', 'label-render.js', 'admin.js'];

	private const TABLE = 'barcode_label_templates';
	private const MAX_COPIES = 500;
	private const MAX_LABELS = 5000;

	private const TEXT_ELEMENTS = ['name', 'variant', 'brand', 'price', 'old_price', 'discount', 'sku', 'custom'];

	public function install(): bool
	{
		if (!$this->runSqlFile('install.sql')) {
			return false;
		}

		self::ensureDefaultTemplate();

		return true;
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		if (defined('IN_ADMIN')) {
			$this->registerAdminMenuLink('Barkod & Etiket', 'catalog', 96);
		}
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		self::ensureSchema();

		if ((string) Tools::getValue('bl_ajax') === 'search') {
			self::sendJson(['success' => true, 'items' => self::searchItems((string) Tools::getValue('q'))]);
		}

		$flash = '';
		$flashType = 'info';
		$selectedId = (int) Tools::getValue('template');

		if (Tools::isSubmit('blAction')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals((string) $adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$action = (string) Tools::getValue('blAction');

				switch ($action) {
					case 'save':
						$result = self::saveTemplate(
							(int) Tools::getValue('id_template'),
							(string) Tools::getValue('template_name'),
							(string) Tools::getValue('config')
						);
						$selectedId = (int) ($result['id'] ?? $selectedId);
						break;
					case 'duplicate':
						$result = self::duplicateTemplate((int) Tools::getValue('id_template'));
						$selectedId = (int) ($result['id'] ?? $selectedId);
						break;
					case 'delete':
						$result = self::deleteTemplate((int) Tools::getValue('id_template'));
						$selectedId = 0;
						break;
					case 'print':
						$this->printLabels(
							(int) Tools::getValue('id_template'),
							(string) Tools::getValue('items'),
							(string) Tools::getValue('config')
						);
						$result = ['success' => false, 'message' => 'Yazdırılacak ürün seçilmedi'];
						break;
					default:
						$result = ['success' => false, 'message' => 'Geçersiz işlem'];
				}

				$flash = (string) $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
			}
		}

		$templates = self::getTemplates();

		if ($templates === []) {
			self::ensureDefaultTemplate();
			$templates = self::getTemplates();
		}

		$current = null;

		foreach ($templates as $tpl) {
			if ($selectedId > 0 && (int) $tpl['id_template'] === $selectedId) {
				$current = $tpl;
				break;
			}
		}

		if ($current === null) {
			$current = $templates[0] ?? ['id_template' => 0, 'name' => 'Yeni şablon', 'config' => self::defaultConfig()];
		}

		$smarty->assign([
			'flash' => $flash,
			'flashType' => $flashType,
			'blTemplates' => $templates,
			'blCurrent' => $current,
			'blConfigJson' => json_encode($current['config'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
			'blSampleJson' => json_encode(self::sampleItem(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
			'blBaseUrl' => Admin::url($this->getAdminSlug()),
			'blSearchUrl' => Admin::url($this->getAdminSlug()) . '?bl_ajax=search',
		]);
	}

	/* ------------------------------------------------------------------ */
	/* Şablonlar                                                           */
	/* ------------------------------------------------------------------ */

	public static function ensureSchema(): void
	{
		$table = DB::execute("SHOW TABLES LIKE '" . self::TABLE . "'");

		if (empty($table)) {
			$sql = (string) @file_get_contents(__DIR__ . '/install.sql');

			if ($sql !== '') {
				DB::execute($sql);
			}
		}
	}

	public static function ensureDefaultTemplate(): void
	{
		self::ensureSchema();

		if ((int) DB::getValue('SELECT COUNT(*) FROM ' . self::TABLE) > 0) {
			return;
		}

		DB::insert(self::TABLE, [
			'name' => 'Raf etiketi 50x30',
			'config' => json_encode(self::defaultConfig(), JSON_UNESCAPED_UNICODE),
			'date_upd' => date('Y-m-d H:i:s'),
		]);

		$small = self::defaultConfig();
		$small['width'] = 40;
		$small['height'] = 20;
		$small['elements']['name'] = array_merge($small['elements']['name'], ['x' => 1.5, 'y' => 1, 'w' => 37, 'size' => 6.5, 'lines' => 1]);
		$small['elements']['price'] = array_merge($small['elements']['price'], ['x' => 1.5, 'y' => 4, 'w' => 20, 'size' => 10]);
		$small['elements']['old_price'] = array_merge($small['elements']['old_price'], ['x' => 21, 'y' => 4.6, 'w' => 17.5, 'size' => 6, 'align' => 'right']);
		$small['elements']['discount']['show'] = false;
		$small['elements']['barcode'] = array_merge($small['elements']['barcode'], ['x' => 2, 'y' => 8.5, 'w' => 36, 'h' => 10.5, 'textSize' => 6]);

		DB::insert(self::TABLE, [
			'name' => 'Ürün etiketi 40x20',
			'config' => json_encode($small, JSON_UNESCAPED_UNICODE),
			'date_upd' => date('Y-m-d H:i:s'),
		]);
	}

	/** @return list<array{id_template: int, name: string, config: array}> */
	public static function getTemplates(): array
	{
		$rows = DB::execute('SELECT * FROM ' . self::TABLE . ' ORDER BY id_template ASC') ?: [];
		$list = [];

		foreach ($rows as $row) {
			$decoded = json_decode((string) $row['config'], true);
			$list[] = [
				'id_template' => (int) $row['id_template'],
				'name' => (string) $row['name'],
				'config' => self::sanitizeConfig(is_array($decoded) ? $decoded : []),
			];
		}

		return $list;
	}

	public static function getTemplate(int $id): ?array
	{
		foreach (self::getTemplates() as $tpl) {
			if ($tpl['id_template'] === $id) {
				return $tpl;
			}
		}

		return null;
	}

	public static function saveTemplate(int $id, string $name, string $configJson): array
	{
		$name = trim(strip_tags($name));

		if ($name === '') {
			$name = 'Etiket şablonu';
		}

		$decoded = json_decode($configJson, true);

		if (!is_array($decoded)) {
			return ['success' => false, 'message' => 'Tasarım verisi okunamadı'];
		}

		$data = [
			'name' => mb_substr($name, 0, 128),
			'config' => json_encode(self::sanitizeConfig($decoded), JSON_UNESCAPED_UNICODE),
			'date_upd' => date('Y-m-d H:i:s'),
		];

		if ($id > 0 && DB::getRowSafe(self::TABLE, 'id_template = ?', [$id])) {
			DB::update(self::TABLE, $data, 'id_template = :where_id', ['where_id' => $id]);

			return ['success' => true, 'message' => 'Şablon kaydedildi', 'id' => $id];
		}

		$newId = (int) DB::insert(self::TABLE, $data);

		return $newId > 0
			? ['success' => true, 'message' => 'Yeni şablon oluşturuldu', 'id' => $newId]
			: ['success' => false, 'message' => 'Şablon kaydedilemedi'];
	}

	public static function duplicateTemplate(int $id): array
	{
		$tpl = self::getTemplate($id);

		if (!$tpl) {
			return ['success' => false, 'message' => 'Şablon bulunamadı'];
		}

		return self::saveTemplate(0, $tpl['name'] . ' (kopya)', (string) json_encode($tpl['config']));
	}

	public static function deleteTemplate(int $id): array
	{
		if ((int) DB::getValue('SELECT COUNT(*) FROM ' . self::TABLE) <= 1) {
			return ['success' => false, 'message' => 'Son şablon silinemez'];
		}

		DB::execute('DELETE FROM ' . self::TABLE . ' WHERE id_template = ?', [$id]);

		return ['success' => true, 'message' => 'Şablon silindi'];
	}

	public static function defaultConfig(): array
	{
		return [
			'width' => 50,
			'height' => 30,
			'font' => 'Arial',
			'border' => false,
			'mode' => 'thermal',
			'sheet' => [
				'cols' => 3,
				'rows' => 8,
				'marginTop' => 10,
				'marginLeft' => 7,
				'gapX' => 2.5,
				'gapY' => 0,
			],
			'elements' => [
				'name' => self::textDefaults(['show' => true, 'x' => 2, 'y' => 1.5, 'w' => 46, 'size' => 8, 'bold' => true, 'lines' => 2]),
				'variant' => self::textDefaults(['show' => false, 'x' => 2, 'y' => 8, 'w' => 46, 'size' => 6]),
				'brand' => self::textDefaults(['show' => false, 'x' => 2, 'y' => 8, 'w' => 46, 'size' => 6, 'prefix' => '']),
				'price' => self::textDefaults(['show' => true, 'x' => 2, 'y' => 8.5, 'w' => 24, 'size' => 14, 'bold' => true]),
				'old_price' => self::textDefaults(['show' => true, 'x' => 25, 'y' => 10, 'w' => 13, 'size' => 7, 'strike' => true]),
				'discount' => self::textDefaults(['show' => true, 'x' => 38.5, 'y' => 9.3, 'w' => 9.5, 'size' => 7, 'bold' => true, 'align' => 'center', 'invert' => true]),
				'sku' => self::textDefaults(['show' => false, 'x' => 2, 'y' => 26, 'w' => 46, 'size' => 5, 'prefix' => 'Stok: ']),
				'custom' => self::textDefaults(['show' => false, 'x' => 2, 'y' => 26, 'w' => 46, 'size' => 5, 'align' => 'center', 'text' => (string) Settings::get('SITE_NAME')]),
				'barcode' => [
					'show' => true,
					'x' => 3,
					'y' => 15.5,
					'w' => 44,
					'h' => 13,
					'showText' => true,
					'textSize' => 7,
					'format' => 'auto',
				],
			],
		];
	}

	private static function textDefaults(array $overrides): array
	{
		return array_merge([
			'show' => true,
			'x' => 2,
			'y' => 2,
			'w' => 40,
			'size' => 8,
			'bold' => false,
			'italic' => false,
			'align' => 'left',
			'lines' => 1,
			'strike' => false,
			'invert' => false,
			'prefix' => '',
			'text' => '',
		], $overrides);
	}

	public static function sanitizeConfig(array $in): array
	{
		$def = self::defaultConfig();
		$out = $def;

		$out['width'] = self::num($in['width'] ?? null, $def['width'], 10, 200);
		$out['height'] = self::num($in['height'] ?? null, $def['height'], 10, 200);
		$out['font'] = in_array($in['font'] ?? '', ['Arial', 'Helvetica', 'Verdana', 'Tahoma', 'Georgia', 'Courier New'], true)
			? (string) $in['font']
			: $def['font'];
		$out['border'] = !empty($in['border']);
		$out['mode'] = ($in['mode'] ?? '') === 'a4' ? 'a4' : 'thermal';

		$sheet = is_array($in['sheet'] ?? null) ? $in['sheet'] : [];
		$out['sheet'] = [
			'cols' => (int) self::num($sheet['cols'] ?? null, $def['sheet']['cols'], 1, 10),
			'rows' => (int) self::num($sheet['rows'] ?? null, $def['sheet']['rows'], 1, 30),
			'marginTop' => self::num($sheet['marginTop'] ?? null, $def['sheet']['marginTop'], 0, 50),
			'marginLeft' => self::num($sheet['marginLeft'] ?? null, $def['sheet']['marginLeft'], 0, 50),
			'gapX' => self::num($sheet['gapX'] ?? null, $def['sheet']['gapX'], 0, 30),
			'gapY' => self::num($sheet['gapY'] ?? null, $def['sheet']['gapY'], 0, 30),
		];

		$elements = is_array($in['elements'] ?? null) ? $in['elements'] : [];

		foreach (self::TEXT_ELEMENTS as $key) {
			$src = is_array($elements[$key] ?? null) ? $elements[$key] : [];
			$d = $def['elements'][$key];
			$out['elements'][$key] = [
				'show' => array_key_exists('show', $src) ? !empty($src['show']) : $d['show'],
				'x' => self::num($src['x'] ?? null, $d['x'], -50, 200),
				'y' => self::num($src['y'] ?? null, $d['y'], -50, 200),
				'w' => self::num($src['w'] ?? null, $d['w'], 2, 200),
				'size' => self::num($src['size'] ?? null, $d['size'], 3, 96),
				'bold' => array_key_exists('bold', $src) ? !empty($src['bold']) : $d['bold'],
				'italic' => !empty($src['italic']),
				'align' => in_array($src['align'] ?? '', ['left', 'center', 'right'], true) ? (string) $src['align'] : $d['align'],
				'lines' => (int) self::num($src['lines'] ?? null, $d['lines'], 1, 6),
				'strike' => array_key_exists('strike', $src) ? !empty($src['strike']) : $d['strike'],
				'invert' => array_key_exists('invert', $src) ? !empty($src['invert']) : $d['invert'],
				'prefix' => mb_substr(trim(strip_tags((string) ($src['prefix'] ?? $d['prefix']))), 0, 40),
				'text' => mb_substr(trim(strip_tags((string) ($src['text'] ?? $d['text']))), 0, 120),
			];
		}

		$src = is_array($elements['barcode'] ?? null) ? $elements['barcode'] : [];
		$d = $def['elements']['barcode'];
		$out['elements']['barcode'] = [
			'show' => array_key_exists('show', $src) ? !empty($src['show']) : $d['show'],
			'x' => self::num($src['x'] ?? null, $d['x'], -50, 200),
			'y' => self::num($src['y'] ?? null, $d['y'], -50, 200),
			'w' => self::num($src['w'] ?? null, $d['w'], 5, 200),
			'h' => self::num($src['h'] ?? null, $d['h'], 3, 200),
			'showText' => array_key_exists('showText', $src) ? !empty($src['showText']) : $d['showText'],
			'textSize' => self::num($src['textSize'] ?? null, $d['textSize'], 3, 30),
			'format' => in_array($src['format'] ?? '', ['auto', 'code128', 'ean13'], true) ? (string) $src['format'] : 'auto',
		];

		return $out;
	}

	/** @param mixed $value */
	private static function num($value, float $default, float $min, float $max): float
	{
		if ($value === null || $value === '' || !is_numeric($value)) {
			return $default;
		}

		return round(max($min, min($max, (float) $value)), 2);
	}

	/* ------------------------------------------------------------------ */
	/* Ürün verisi                                                         */
	/* ------------------------------------------------------------------ */

	/** @return list<array<string, mixed>> */
	public static function searchItems(string $query): array
	{
		$query = trim($query);

		if (Tools::strlen($query) < 2) {
			return [];
		}

		$like = '%' . $query . '%';
		$rows = DB::execute(
			'SELECT p.id_product FROM products p
			 WHERE p.product_name LIKE ? OR p.stock_code LIKE ? OR p.barcode LIKE ? OR p.id_product = ?
			 ORDER BY p.id_product DESC
			 LIMIT 20',
			[$like, $like, $like, (int) $query]
		) ?: [];

		$dash = strrpos($query, '-');

		if ($dash !== false && $dash > 0) {
			$byComposite = DB::execute(
				'SELECT id_product FROM products WHERE stock_code = ? LIMIT 5',
				[substr($query, 0, $dash)]
			) ?: [];
			$rows = array_merge($byComposite, $rows);
		}

		$items = [];

		foreach ($rows as $row) {
			foreach (self::buildItems((int) $row['id_product']) as $item) {
				$items[] = $item;
			}
		}

		$byVariationBarcode = DB::execute(
			'SELECT id_product, id_variation FROM product_variations WHERE barcode = ? OR sku = ? LIMIT 10',
			[$query, $query]
		) ?: [];

		foreach ($byVariationBarcode as $row) {
			foreach (self::buildItems((int) $row['id_product']) as $item) {
				if ((int) $item['id_variation'] === (int) $row['id_variation']) {
					array_unshift($items, $item);
				}
			}
		}

		$unique = [];

		foreach ($items as $item) {
			$unique[$item['key']] = $item;
		}

		return array_values(array_slice($unique, 0, 60));
	}

	/**
	 * Ürün + varyasyon satırları (varyasyon varsa her varyasyon ayrı etiket).
	 *
	 * @return list<array<string, mixed>>
	 */
	public static function buildItems(int $idProduct, int $onlyVariation = -1): array
	{
		$product = self::loadProduct($idProduct);

		if (!$product) {
			return [];
		}

		$base = [
			'id_product' => $idProduct,
			'id_variation' => 0,
			'name' => (string) $product['product_name'],
			'brand' => (string) ($product['brand_name'] ?? ''),
			'sku' => (string) ($product['stock_code'] ?? ''),
			'barcode' => (string) ($product['barcode'] ?? ''),
			'image' => (string) ($product['image_url'] ?? ''),
			'variant' => '',
		];

		$basePrice = (float) $product['price'];
		$oldPrice = (float) ($product['old_price'] ?? 0);
		$items = [];

		$variations = class_exists('ProductVariation') ? ProductVariation::getByProduct($idProduct, true) : [];

		if ($onlyVariation <= 0 && ($variations === [] || $onlyVariation === 0)) {
			$items[] = self::finalizeItem($base, $basePrice, $oldPrice);
		}

		foreach ($variations as $variation) {
			$idVariation = (int) $variation['id_variation'];

			if ($onlyVariation > 0 && $onlyVariation !== $idVariation) {
				continue;
			}

			if ($onlyVariation === 0) {
				break;
			}

			$item = $base;
			$item['id_variation'] = $idVariation;
			$item['variant'] = ProductVariation::formatLabel($variation);

			$varSku = trim((string) ($variation['sku'] ?? ''));
			$item['barcode'] = trim((string) ($variation['barcode'] ?? ''));

			if ($varSku !== '') {
				$item['sku'] = ($base['sku'] !== '' && strpos($varSku, $base['sku']) !== 0)
					? $base['sku'] . '-' . $varSku
					: $varSku;
			} elseif ($base['sku'] !== '') {
				$item['sku'] = $base['sku'] . '-' . $idVariation;
			}

			$items[] = self::finalizeItem($item, ProductVariation::getEffectivePrice($variation, $basePrice), $oldPrice);
		}

		return $items;
	}

	private static function finalizeItem(array $item, float $price, float $oldPrice): array
	{
		$hasDiscount = $oldPrice > $price && $price > 0;

		if (trim($item['barcode']) === '') {
			$item['barcode'] = trim($item['sku']) !== '' ? trim($item['sku']) : (string) (100000 + (int) $item['id_product']);
			$item['barcode_fallback'] = true;
		}

		$item['key'] = $item['id_product'] . '-' . $item['id_variation'];
		$item['price'] = Tools::displayPrice($price);
		$item['old_price'] = $hasDiscount ? Tools::displayPrice($oldPrice) : '';
		$item['discount'] = $hasDiscount ? '%' . Tools::getDiscount($oldPrice, $price) : '';

		return $item;
	}

	private static function loadProduct(int $idProduct): ?array
	{
		if ($idProduct <= 0) {
			return null;
		}

		$product = Product::getById($idProduct);

		if ($product) {
			return $product;
		}

		$rows = DB::execute(
			'SELECT p.*, b.brand_name FROM products p
			 LEFT JOIN brands b ON b.id_brand = p.id_brand
			 WHERE p.id_product = ? LIMIT 1',
			[$idProduct]
		);

		if (!$rows || !isset($rows[0])) {
			return null;
		}

		$row = $rows[0];
		$row['image_url'] = '';

		return $row;
	}

	private static function sampleItem(): array
	{
		return [
			'key' => 'sample',
			'name' => 'Örnek Ürün Adı Nemlendirici Krem 50 ml',
			'variant' => 'Renk: Beyaz, Beden: M',
			'brand' => 'Marka',
			'sku' => 'STK-00125',
			'barcode' => '8690000000012',
			'price' => Tools::displayPrice(129.90),
			'old_price' => Tools::displayPrice(159.90),
			'discount' => '%19',
		];
	}

	/* ------------------------------------------------------------------ */
	/* Yazdırma                                                            */
	/* ------------------------------------------------------------------ */

	private function printLabels(int $idTemplate, string $itemsJson, string $configJson): void
	{
		$decoded = json_decode($configJson, true);
		$tpl = self::getTemplate($idTemplate);
		$config = is_array($decoded)
			? self::sanitizeConfig($decoded)
			: ($tpl ? $tpl['config'] : self::defaultConfig());

		$requested = json_decode($itemsJson, true);

		if (!is_array($requested) || $requested === []) {
			return;
		}

		$labels = [];

		foreach ($requested as $req) {
			if (!is_array($req)) {
				continue;
			}

			$qty = (int) max(0, min(self::MAX_COPIES, (int) ($req['qty'] ?? 1)));
			$built = self::buildItems((int) ($req['id_product'] ?? 0), (int) ($req['id_variation'] ?? 0));

			if ($qty <= 0 || $built === []) {
				continue;
			}

			for ($i = 0; $i < $qty && count($labels) < self::MAX_LABELS; $i++) {
				$labels[] = $built[0];
			}
		}

		if ($labels === []) {
			return;
		}

		$flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
		$html = $this->renderAdminTemplate('print', [
			'printConfig' => $config,
			'printConfigJson' => json_encode($config, $flags),
			'printItemsJson' => json_encode($labels, $flags),
			'printCount' => count($labels),
			'labelCssUrl' => $this->getAssetUrl('css/label.css'),
			'barcodeJsUrl' => $this->getAssetUrl('js/barcode.js'),
			'renderJsUrl' => $this->getAssetUrl('js/label-render.js'),
		]);

		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		header('Content-Type: text/html; charset=UTF-8');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		echo $html;
		exit;
	}

	private static function sendJson(array $payload): void
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		header('Content-Type: application/json; charset=UTF-8');
		header('Cache-Control: no-store');
		echo json_encode($payload, JSON_UNESCAPED_UNICODE);
		exit;
	}
}
