<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class WhatsappModule extends ModuleBase
{
	public string $name = 'whatsapp';
	public string $title = 'WhatsApp İletişim';
	public string $version = '2.1.2';
	public string $description = 'WhatsApp sohbet widget’ı — mesai saatleri ve wa.me yönlendirme';
	public string $author = 'FShop';

	public array $displayHooks = [
		'footer' => 'WhatsApp canlı destek widget’ı (sabit buton)',
	];

	public array $defaultDisplayHooks = ['footer'];

	public array $frontStylesheets = ['whatsapp.css'];
	public array $frontScripts = ['whatsapp.js'];
	public array $adminStylesheets = ['admin.css'];

	private const SET_ENABLED = 'WHATSAPP_ENABLED';
	private const SET_PHONE = 'WHATSAPP_PHONE';
	private const SET_NAME = 'WHATSAPP_NAME';
	private const SET_GREETING = 'WHATSAPP_GREETING';
	private const SET_HOURS = 'WHATSAPP_HOURS';

	/** @return array<int, string> */
	public static function dayLabels(): array
	{
		return [
			1 => 'Pazartesi',
			2 => 'Salı',
			3 => 'Çarşamba',
			4 => 'Perşembe',
			5 => 'Cuma',
			6 => 'Cumartesi',
			0 => 'Pazar',
		];
	}

	public function install(): bool
	{
		$this->runSqlFile('install.sql');
		$this->ensureDefaultSettings();

		return true;
	}

	public function uninstall(): bool
	{
		Settings::set(self::SET_ENABLED, '0');
		Settings::set(self::SET_PHONE, '');
		Settings::set(self::SET_NAME, '');
		Settings::set(self::SET_GREETING, '');
		Settings::set(self::SET_HOURS, '');

		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		$this->ensureDefaultSettings();
		$this->ensureFooterHook();
	}

	/** Theme4 vb. için footer_menu atamasını footer’a taşı */
	private function ensureFooterHook(): void
	{
		if (!class_exists('Module') || !Module::isInstalled($this->name)) {
			return;
		}

		$assigned = Module::getAssignedDisplayHooks($this->name);

		if (in_array('footer', $assigned, true)) {
			return;
		}

		Module::setDisplayHooks($this->name, ['footer']);
	}

	public function ensureDefaultSettings(): void
	{
		if (Settings::get(self::SET_ENABLED, '') === '') {
			Settings::set(self::SET_ENABLED, '1');
		}

		if (Settings::get(self::SET_PHONE, '') === '') {
			$legacy = $this->getLegacyPhone();
			Settings::set(self::SET_PHONE, $legacy !== '' ? $legacy : '');
		}

		if (Settings::get(self::SET_NAME, '') === '') {
			$site = trim((string) Settings::get('SITE_NAME', 'Destek'));
			Settings::set(self::SET_NAME, $site !== '' ? $site : 'Destek');
		}

		if (Settings::get(self::SET_GREETING, '') === '') {
			Settings::set(self::SET_GREETING, 'Merhaba 👋 Size nasıl yardımcı olabiliriz?');
		}

		if (Settings::get(self::SET_HOURS, '') === '') {
			Settings::set(self::SET_HOURS, json_encode($this->defaultHours(), JSON_UNESCAPED_UNICODE));
		}
	}

	/** @return array<int, array{open: bool, start: string, end: string}> */
	public function defaultHours(): array
	{
		$hours = [];

		foreach ([1, 2, 3, 4, 5] as $day) {
			$hours[$day] = ['open' => true, 'start' => '09:00', 'end' => '18:00'];
		}

		$hours[6] = ['open' => true, 'start' => '10:00', 'end' => '14:00'];
		$hours[0] = ['open' => false, 'start' => '09:00', 'end' => '18:00'];

		return $hours;
	}

	private function getLegacyPhone(): string
	{
		try {
			$row = DB::getRowSafe('whatsapp', 'id = 1');
			if ($row && !empty($row['phone'])) {
				return $this->normalizePhone((string) $row['phone']);
			}
		} catch (Throwable $e) {
			// tablo yoksa yok say
		}

		return '';
	}

	public function normalizePhone(string $phone): string
	{
		$digits = preg_replace('/\D+/', '', $phone) ?: '';

		if ($digits === '') {
			return '';
		}

		// 05xx… → 905xx…
		if (strlen($digits) === 11 && $digits[0] === '0') {
			$digits = '9' . $digits;
		}

		// 5xx… (10 hane) → 905xx…
		if (strlen($digits) === 10 && $digits[0] === '5') {
			$digits = '90' . $digits;
		}

		return $digits;
	}

	/** @return array<int, array{open: bool, start: string, end: string}> */
	public function getHoursConfig(): array
	{
		$raw = (string) Settings::get(self::SET_HOURS, '');
		$decoded = json_decode($raw, true);
		$defaults = $this->defaultHours();

		if (!is_array($decoded)) {
			return $defaults;
		}

		$out = [];

		foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
			$row = is_array($decoded[$day] ?? null) ? $decoded[$day] : ($decoded[(string) $day] ?? []);
			$start = $this->normalizeTime((string) ($row['start'] ?? $defaults[$day]['start']));
			$end = $this->normalizeTime((string) ($row['end'] ?? $defaults[$day]['end']));
			$open = !empty($row['open']);

			$out[$day] = [
				'open' => $open,
				'start' => $start,
				'end' => $end,
			];
		}

		return $out;
	}

	private function normalizeTime(string $time): string
	{
		$time = trim($time);

		if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m)) {
			$h = max(0, min(23, (int) $m[1]));
			$i = max(0, min(59, (int) $m[2]));

			return sprintf('%02d:%02d', $h, $i);
		}

		return '09:00';
	}

	/**
	 * @return array{
	 *   is_open: bool,
	 *   status_label: string,
	 *   reply_window: string,
	 *   reply_start: string,
	 *   reply_end: string,
	 *   offline_message: string,
	 *   day: int
	 * }
	 */
	public function getStatusNow(?int $timestamp = null): array
	{
		$ts = $timestamp ?? time();
		$day = (int) date('w', $ts);
		$nowMinutes = ((int) date('G', $ts)) * 60 + (int) date('i', $ts);
		$hours = $this->getHoursConfig();
		$today = $hours[$day] ?? ['open' => false, 'start' => '09:00', 'end' => '18:00'];

		$isOpen = false;

		if (!empty($today['open'])) {
			$start = $this->timeToMinutes((string) $today['start']);
			$end = $this->timeToMinutes((string) $today['end']);

			if ($end > $start) {
				$isOpen = $nowMinutes >= $start && $nowMinutes < $end;
			} else {
				// geceye sarkan (örn. 22:00–02:00)
				$isOpen = $nowMinutes >= $start || $nowMinutes < $end;
			}
		}

		$reply = $this->resolveReplyWindow($day, $hours, $isOpen);

		$offlineMessage = sprintf(
			'Mesai saatleri dışındayız. Mesaj atabilirsiniz; mesajınıza %s saatleri arasında cevap verilecek.',
			$reply['label']
		);

		return [
			'is_open' => $isOpen,
			'status_label' => $isOpen ? 'Online' : 'Mesai dışı',
			'reply_window' => $reply['label'],
			'reply_start' => $reply['start'],
			'reply_end' => $reply['end'],
			'offline_message' => $offlineMessage,
			'day' => $day,
		];
	}

	/**
	 * @param array<int, array{open: bool, start: string, end: string}> $hours
	 * @return array{label: string, start: string, end: string}
	 */
	private function resolveReplyWindow(int $fromDay, array $hours, bool $isOpenNow): array
	{
		if ($isOpenNow && !empty($hours[$fromDay]['open'])) {
			$start = (string) $hours[$fromDay]['start'];
			$end = (string) $hours[$fromDay]['end'];

			return [
				'label' => $start . ' – ' . $end,
				'start' => $start,
				'end' => $end,
			];
		}

		// Bugün kapalıysa veya mesai dışındaysa: bugünün saatleri (açıksa) veya sonraki açık gün
		if (!empty($hours[$fromDay]['open'])) {
			$start = (string) $hours[$fromDay]['start'];
			$end = (string) $hours[$fromDay]['end'];

			return [
				'label' => $start . ' – ' . $end,
				'start' => $start,
				'end' => $end,
			];
		}

		for ($i = 1; $i <= 7; $i++) {
			$d = ($fromDay + $i) % 7;
			if (!empty($hours[$d]['open'])) {
				$start = (string) $hours[$d]['start'];
				$end = (string) $hours[$d]['end'];
				$dayName = self::dayLabels()[$d] ?? '';

				return [
					'label' => trim($dayName . ' ' . $start . ' – ' . $end),
					'start' => $start,
					'end' => $end,
				];
			}
		}

		return [
			'label' => 'mesai saatleri',
			'start' => '09:00',
			'end' => '18:00',
		];
	}

	private function timeToMinutes(string $time): int
	{
		$parts = explode(':', $time);

		return ((int) ($parts[0] ?? 0)) * 60 + (int) ($parts[1] ?? 0);
	}

	public function buildWaLink(string $text = ''): string
	{
		$phone = $this->normalizePhone((string) Settings::get(self::SET_PHONE, ''));

		if ($phone === '') {
			return '';
		}

		$url = 'https://wa.me/' . rawurlencode($phone);

		$text = trim($text);
		if ($text !== '') {
			$url .= '?text=' . rawurlencode($text);
		}

		return $url;
	}

	public function isEnabled(): bool
	{
		return Settings::get(self::SET_ENABLED, '1') === '1'
			&& $this->normalizePhone((string) Settings::get(self::SET_PHONE, '')) !== '';
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'footer' || !$this->isEnabled()) {
			return null;
		}

		$status = $this->getStatusNow();
		$name = trim((string) Settings::get(self::SET_NAME, 'Destek'));
		$greeting = trim((string) Settings::get(self::SET_GREETING, 'Merhaba 👋 Size nasıl yardımcı olabiliriz?'));
		$avatar = '';

		if (class_exists('SiteAssets') && SiteAssets::logoExists('header')) {
			$avatar = SiteAssets::getLogoUrl('header');
		}

		$html = $this->renderFrontTemplate('footer', [
			'waName' => $name !== '' ? $name : 'Destek',
			'waGreeting' => $greeting,
			'waAvatar' => $avatar,
			'waInitial' => mb_strtoupper(mb_substr($name !== '' ? $name : 'W', 0, 1, 'UTF-8')),
			'waIsOpen' => !empty($status['is_open']),
			'waStatusLabel' => $status['status_label'],
			'waOfflineMessage' => $status['offline_message'],
			'waReplyWindow' => $status['reply_window'],
			'waBaseUrl' => $this->buildWaLink(''),
			'waNow' => date('H:i'),
		]);

		return $html !== '' ? $html : null;
	}

	/** @param array<string, mixed> $post */
	public function saveSettings(array $post): array
	{
		$enabled = !empty($post['wa_enabled']) ? '1' : '0';
		$phone = $this->normalizePhone((string) ($post['wa_phone'] ?? ''));
		$name = mb_substr(trim(strip_tags((string) ($post['wa_name'] ?? ''))), 0, 64);
		$greeting = mb_substr(trim(strip_tags((string) ($post['wa_greeting'] ?? ''))), 0, 255);

		if ($enabled === '1' && $phone === '') {
			return ['success' => false, 'message' => 'WhatsApp numarası gerekli (ülke kodu ile, örn. 905xxxxxxxxx)'];
		}

		if ($name === '') {
			$name = 'Destek';
		}

		if ($greeting === '') {
			$greeting = 'Merhaba 👋 Size nasıl yardımcı olabiliriz?';
		}

		$hours = [];
		$starts = is_array($post['wa_start'] ?? null) ? $post['wa_start'] : [];
		$ends = is_array($post['wa_end'] ?? null) ? $post['wa_end'] : [];
		$opens = is_array($post['wa_open'] ?? null) ? $post['wa_open'] : [];

		foreach ([1, 2, 3, 4, 5, 6, 0] as $day) {
			$key = (string) $day;
			$hours[$day] = [
				'open' => !empty($opens[$key]),
				'start' => $this->normalizeTime((string) ($starts[$key] ?? '09:00')),
				'end' => $this->normalizeTime((string) ($ends[$key] ?? '18:00')),
			];
		}

		Settings::set(self::SET_ENABLED, $enabled);
		Settings::set(self::SET_PHONE, $phone);
		Settings::set(self::SET_NAME, $name);
		Settings::set(self::SET_GREETING, $greeting);
		Settings::set(self::SET_HOURS, json_encode($hours, JSON_UNESCAPED_UNICODE));

		return ['success' => true, 'message' => 'Ayarlar kaydedildi'];
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$this->ensureDefaultSettings();

		$flash = '';
		$flashType = 'success';

		if (Tools::isSubmit('saveWhatsapp')) {
			$token = (string) Tools::getValue('token');

			if (!hash_equals((string) $adminToken, $token)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$result = $this->saveSettings($_POST);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
			}
		}

		$status = $this->getStatusNow();
		$hours = $this->getHoursConfig();
		$days = [];

		foreach ([1, 2, 3, 4, 5, 6, 0] as $day) {
			$days[] = [
				'id' => $day,
				'label' => self::dayLabels()[$day],
				'open' => !empty($hours[$day]['open']),
				'start' => $hours[$day]['start'],
				'end' => $hours[$day]['end'],
			];
		}

		$smarty->assign([
			'waFlash' => $flash,
			'waFlashType' => $flashType,
			'waEnabled' => Settings::get(self::SET_ENABLED, '1') === '1',
			'waPhone' => (string) Settings::get(self::SET_PHONE, ''),
			'waName' => (string) Settings::get(self::SET_NAME, 'Destek'),
			'waGreeting' => (string) Settings::get(self::SET_GREETING, ''),
			'waDays' => $days,
			'waStatusPreview' => $status,
			'waLinkPreview' => $this->buildWaLink('Merhaba'),
		]);
	}
}
