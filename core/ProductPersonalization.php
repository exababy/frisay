<?php

/** Product-specific customer input fields (text, date and image). */
class ProductPersonalization
{
	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}
		self::$schemaReady = true;
		DB::execute("CREATE TABLE IF NOT EXISTS `product_personalization_fields` (
			`id_field` int(11) NOT NULL AUTO_INCREMENT,
			`id_product` int(11) NOT NULL,
			`field_label` varchar(128) NOT NULL,
			`field_type` varchar(16) NOT NULL DEFAULT 'text',
			`required` tinyint(1) NOT NULL DEFAULT 0,
			`sort_order` int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id_field`), KEY `id_product` (`id_product`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}

	public static function getByProduct(int $idProduct): array
	{
		self::ensureSchema();
		if ($idProduct <= 0) {
			return [];
		}
		return DB::execute('SELECT * FROM product_personalization_fields WHERE id_product = ? ORDER BY sort_order ASC, id_field ASC', [$idProduct]) ?: [];
	}

	public static function getForStorefront(int $idProduct): array
	{
		return array_map(static function (array $field): array {
			return [
				'id' => (int) $field['id_field'],
				'label' => (string) $field['field_label'],
				'type' => (string) $field['field_type'],
				'required' => (int) $field['required'] === 1,
			];
		}, self::getByProduct($idProduct));
	}

	public static function formatFormRow(array $field): array
	{
		return [
			'label' => (string) ($field['field_label'] ?? ''),
			'type' => (string) ($field['field_type'] ?? 'text'),
			'required' => (int) ($field['required'] ?? 0) === 1,
		];
	}

	public static function saveForProduct(int $idProduct, array $fields): ?array
	{
		self::ensureSchema();
		if ($idProduct <= 0) {
			return ['success' => false, 'message' => 'Ürün kişiselleştirme alanları kaydedilemedi'];
		}
		$allowedTypes = ['text', 'date', 'image'];
		$normalized = [];
		foreach ($fields as $index => $field) {
			if (!is_array($field)) {
				continue;
			}
			$label = trim((string) ($field['label'] ?? ''));
			$type = (string) ($field['type'] ?? 'text');
			if ($label === '') {
				continue;
			}
			if (!in_array($type, $allowedTypes, true)) {
				return ['success' => false, 'message' => 'Geçersiz kişiselleştirme alanı türü'];
			}
			$normalized[] = ['label' => mb_substr($label, 0, 128), 'type' => $type, 'required' => !empty($field['required']) ? 1 : 0, 'sort' => (int) $index];
		}
		DB::execute('DELETE FROM product_personalization_fields WHERE id_product = ?', [$idProduct]);
		foreach ($normalized as $field) {
			DB::insert('product_personalization_fields', [
				'id_product' => $idProduct,
				'field_label' => $field['label'],
				'field_type' => $field['type'],
				'required' => $field['required'],
				'sort_order' => $field['sort'],
			]);
		}
		return null;
	}

	/** Validate submitted values and move uploaded images into a public, random-name directory. */
	public static function collect(int $idProduct, $rawValues, array $files): array
	{
		$fields = self::getByProduct($idProduct);
		$values = is_array($rawValues) ? $rawValues : [];
		$out = [];
		foreach ($fields as $field) {
			$id = (int) $field['id_field'];
			$label = (string) $field['field_label'];
			$type = (string) $field['field_type'];
			$value = trim((string) ($values[$id] ?? ''));
			if ($type === 'image') {
				$value = '';
			}
			$file = $files['personalization_files'] ?? [];
			$fileError = (int) ($file['error'][$id] ?? UPLOAD_ERR_NO_FILE);
			if ($type === 'image' && $fileError !== UPLOAD_ERR_NO_FILE) {
				$uploaded = self::storeImage($file, $id);
				if (empty($uploaded['success'])) {
					return ['success' => false, 'message' => (string) ($uploaded['message'] ?? 'Görsel yüklenemedi')];
				}
				$value = (string) $uploaded['path'];
			}
			if (!empty($field['required']) && $value === '') {
				return ['success' => false, 'message' => $label . ' alanı zorunludur'];
			}
			if ($value === '') {
				continue;
			}
			if ($type === 'text') {
				$value = mb_substr(strip_tags($value), 0, 500);
			} elseif ($type === 'date') {
				$date = DateTime::createFromFormat('!Y-m-d', $value);
				if (!$date || $date->format('Y-m-d') !== $value) {
					return ['success' => false, 'message' => $label . ' için geçerli bir tarih seçin'];
				}
			} elseif ($type === 'image' && strpos($value, 'img/personalizations/') !== 0) {
				return ['success' => false, 'message' => $label . ' görselini yeniden yükleyin'];
			}
			$out[] = ['label' => $label, 'type' => $type, 'value' => $value];
		}
		return ['success' => true, 'values' => $out];
	}

	private static function storeImage(array $files, int $fieldId): array
	{
		$error = (int) ($files['error'][$fieldId] ?? UPLOAD_ERR_NO_FILE);
		$tmp = (string) ($files['tmp_name'][$fieldId] ?? '');
		$size = (int) ($files['size'][$fieldId] ?? 0);
		if ($error !== UPLOAD_ERR_OK || $tmp === '' || !is_uploaded_file($tmp)) {
			return ['success' => false, 'message' => 'Kişiselleştirme görseli yüklenemedi'];
		}
		if ($size <= 0 || $size > 5 * 1024 * 1024) {
			return ['success' => false, 'message' => 'Kişiselleştirme görseli en fazla 5 MB olabilir'];
		}
		$mime = '';
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo) {
			$mime = (string) finfo_file($finfo, $tmp);
			finfo_close($finfo);
		}
		$map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
		if (!isset($map[$mime]) || @getimagesize($tmp) === false) {
			return ['success' => false, 'message' => 'Sadece JPG, PNG veya WEBP görsel yükleyebilirsiniz'];
		}
		$dir = dirname(__DIR__) . '/img/personalizations';
		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return ['success' => false, 'message' => 'Görsel yükleme klasörü oluşturulamadı'];
		}
		$name = bin2hex(random_bytes(20)) . '.' . $map[$mime];
		if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
			return ['success' => false, 'message' => 'Görsel kaydedilemedi'];
		}
		return ['success' => true, 'path' => 'img/personalizations/' . $name];
	}

	public static function formatLabel(array $values): string
	{
		$parts = [];
		foreach ($values as $item) {
			if (!is_array($item)) {
				continue;
			}
			$value = (string) ($item['value'] ?? '');
			if (($item['type'] ?? '') === 'image' && $value !== '') {
				$value = 'Görsel yüklendi';
			}
			if ($value !== '') {
				$parts[] = (string) ($item['label'] ?? 'Kişiselleştirme') . ': ' . $value;
			}
		}
		return implode(', ', $parts);
	}
}
