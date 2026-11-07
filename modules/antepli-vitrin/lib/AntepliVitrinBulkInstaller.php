<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

/**
 * FriSay modüllerini toplu kurar.
 *
 * API anahtarı / hesap bilgisi gerektiren modüller (ödeme, pazaryeri, e-fatura, kargo,
 * reCAPTCHA, yapay zeka vb.) "kurulu ama pasif" bırakılır; böylece ayarları girilmeden
 * mağazada ödeme adımı veya formlar bozulmaz. Ayarları girdikten sonra Modüller
 * sayfasından tek tıkla etkinleştirilir.
 */
class AntepliVitrinBulkInstaller
{
	/** Kurulduktan sonra pasif bırakılacak modüller (ödeme modülleri ayrıca otomatik algılanır). */
	private const PASSIVE = [
		'ai-assistant', 'ai-chat', 'akakce', 'basitkargo', 'bifatura', 'bizimhesap',
		'facebook-pixel', 'fiyattrend', 'fx-pricing', 'google-analytics', 'google-login',
		'google-shopping', 'image-optimizer', 'instagram-gallery', 'mobil-app',
		'prestashop-import', 'recaptcha', 'shipink', 'shopier', 'smart-campaign',
		'trendyol', 'wapio', 'whatsapp', 'xml-import', 'abandoned-cart', 'customer-notify',
		'paytr-taksit', 'bulk-pricing',
	];

	/** Bu modüller toplu kurulumda hiç seçili gelmez (tema ile çakışır / gereksiz). */
	private const SKIP = ['antepli-vitrin', 'cupcake-vitrin', 'theme4'];

	/** Ödeme modülü olsa da kimlik bilgisi istemeyenler — etkin kurulabilir. */
	private const SAFE_PAYMENTS = ['bankwire'];

	/** @var array<string, string> */
	private const GROUPS = [
		'payment' => 'Ödeme',
		'integration' => 'Entegrasyon (API anahtarı gerekir)',
		'store' => 'Mağaza & Vitrin',
		'admin' => 'Yönetim araçları',
	];

	private const ADMIN_TOOLS = [
		'backup-pro', 'barcode-label', 'bulk-pricing', 'canned-responses', 'export-excel',
		'pos', 'image-optimizer', 'prestashop-import', 'ai-assistant', 'xml-import',
	];

	/** @return list<array<string, mixed>> */
	public static function getCatalog(): array
	{
		$out = [];

		foreach (Module::discover() as $name => $meta) {
			$name = (string) $name;
			$isPayment = self::isPaymentModule($name);
			$passive = self::isPassiveByDefault($name, $isPayment);

			if ($isPayment) {
				$group = 'payment';
			} elseif (in_array($name, self::ADMIN_TOOLS, true)) {
				$group = 'admin';
			} elseif (in_array($name, self::PASSIVE, true)) {
				$group = 'integration';
			} else {
				$group = 'store';
			}

			$out[] = [
				'name' => $name,
				'title' => (string) ($meta['title'] ?: $name),
				'description' => (string) ($meta['description'] ?? ''),
				'version' => (string) ($meta['version'] ?? ''),
				'installed' => (int) ($meta['installed'] ?? 0) === 1,
				'active' => (int) ($meta['active'] ?? 0) === 1,
				'passive_default' => $passive,
				'skip' => in_array($name, self::SKIP, true),
				'group' => $group,
				'group_label' => self::GROUPS[$group],
			];
		}

		$order = array_flip(array_keys(self::GROUPS));
		usort($out, static function (array $a, array $b) use ($order): int {
			return [$order[$a['group']], $a['title']] <=> [$order[$b['group']], $b['title']];
		});

		return $out;
	}

	/**
	 * @param list<string>|array<int|string, mixed> $names Boşsa kurulu olmayan tüm modüller.
	 * @return array{success: bool, message: string, logs: list<string>}
	 */
	public static function installMany(array $names): array
	{
		@set_time_limit(0);

		$catalog = [];

		foreach (self::getCatalog() as $row) {
			$catalog[$row['name']] = $row;
		}

		$names = array_values(array_filter(array_map('strval', $names), static function (string $n) use ($catalog): bool {
			return isset($catalog[$n]);
		}));

		if ($names === []) {
			foreach ($catalog as $name => $row) {
				if (!$row['installed'] && !$row['skip']) {
					$names[] = $name;
				}
			}
		}

		$logs = [];
		$ok = 0;
		$failed = 0;

		foreach ($names as $name) {
			$row = $catalog[$name];

			if ($row['installed']) {
				$logs[] = '• ' . $row['title'] . ': zaten kurulu';
				continue;
			}

			try {
				$result = Module::install($name);
			} catch (Throwable $e) {
				$result = ['success' => false, 'message' => $e->getMessage()];
			}

			if (empty($result['success'])) {
				$failed++;
				$logs[] = '✗ ' . $row['title'] . ': ' . (string) ($result['message'] ?? 'hata');
				continue;
			}

			$ok++;

			if ($row['passive_default']) {
				Module::setActive($name, false);
				$logs[] = '✓ ' . $row['title'] . ': kuruldu (pasif — ayarlarını girip etkinleştirin)';
			} else {
				$logs[] = '✓ ' . $row['title'] . ': kuruldu ve etkin';
			}
		}

		$message = $ok . ' modül kuruldu' . ($failed > 0 ? ', ' . $failed . ' modül kurulamadı' : '') . '.';

		return ['success' => $failed === 0, 'message' => $message, 'logs' => $logs];
	}

	private static function isPaymentModule(string $name): bool
	{
		$file = Module::path($name) . '/' . $name . '.php';

		if (!is_file($file)) {
			return false;
		}

		$src = (string) file_get_contents($file);

		return (bool) preg_match('/\$isPayment\s*=\s*true/', $src);
	}

	private static function isPassiveByDefault(string $name, bool $isPayment): bool
	{
		if ($isPayment) {
			return !in_array($name, self::SAFE_PAYMENTS, true);
		}

		return in_array($name, self::PASSIVE, true);
	}
}
