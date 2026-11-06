<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class AiChatModule extends ModuleBase
{
	public string $name = 'ai-chat';
	public string $title = 'Yapay Zeka Canlı Destek';
	public string $version = '1.1.0';
	public string $description = 'WhatsApp tarzı yapay zeka müşteri hizmetleri sohbet penceresi';
	public string $author = 'FShop';

	public array $displayHooks = [
		'footer' => 'Sağ altta canlı destek sohbet widget\'ı',
	];
	public array $defaultDisplayHooks = ['footer'];

	public array $frontStylesheets = ['front.css'];
	public array $frontScripts = ['front.js'];

	public array $apiActions = [
		'chat' => 'api/chat.php',
	];

	/** @return array<string, array{label:string,base_url:string,model:string,free:bool,key_url:string,hint:string}> */
	public static function providers(): array
	{
		return [
			'groq' => [
				'label' => 'Groq (ücretsiz kota)',
				'base_url' => 'https://api.groq.com/openai/v1',
				'model' => 'llama-3.3-70b-versatile',
				'free' => true,
				'key_url' => 'https://console.groq.com/keys',
				'hint' => 'Ücretsiz API anahtarı alın. Hızlı Llama modelleri.',
			],
			'gemini' => [
				'label' => 'Google Gemini (ücretsiz kota)',
				'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai',
				'model' => 'gemini-2.0-flash',
				'free' => true,
				'key_url' => 'https://aistudio.google.com/apikey',
				'hint' => 'Google AI Studio’dan ücretsiz API anahtarı alın.',
			],
			'openrouter_free' => [
				'label' => 'OpenRouter ücretsiz model',
				'base_url' => 'https://openrouter.ai/api/v1',
				'model' => 'meta-llama/llama-3.2-3b-instruct:free',
				'free' => true,
				'key_url' => 'https://openrouter.ai/keys',
				'hint' => 'Ücretsiz modeller için OpenRouter anahtarı (ücretsiz kayıt).',
			],
			'openai' => [
				'label' => 'OpenAI (ücretli)',
				'base_url' => 'https://api.openai.com/v1',
				'model' => 'gpt-4o-mini',
				'free' => false,
				'key_url' => 'https://platform.openai.com/api-keys',
				'hint' => 'Ücretli OpenAI hesabı gerekir.',
			],
			'custom' => [
				'label' => 'Özel / kendi sunucum',
				'base_url' => '',
				'model' => '',
				'free' => false,
				'key_url' => '',
				'hint' => 'OpenAI uyumlu kendi endpoint’iniz (Ollama, LiteLLM vb.).',
			],
			'rules' => [
				'label' => 'API’siz (basit kurallar)',
				'base_url' => '',
				'model' => '',
				'free' => true,
				'key_url' => '',
				'hint' => 'Anahtar gerekmez. İletişim / kargo için hazır cevaplar.',
			],
		];
	}

	public function install(): bool
	{
		$defaults = [
			'AI_CHAT_ENABLED' => '1',
			'AI_CHAT_PROVIDER' => 'groq',
			'AI_CHAT_TITLE' => 'Canlı Destek',
			'AI_CHAT_WELCOME' => 'Merhaba, ben yapay zeka asistanı, size nasıl yardımcı olabilirim?',
			'AI_CHAT_MODEL' => 'llama-3.3-70b-versatile',
			'AI_CHAT_BASE_URL' => 'https://api.groq.com/openai/v1',
			'AI_CHAT_API_KEY' => '',
			'AI_CHAT_SYSTEM' => "Sen bir e-ticaret mağazasının nazik ve kısa cevaplı müşteri hizmetleri asistanısın.\n"
				. "Yalnızca mağaza, sipariş, kargo, iade ve ürün bilgileri hakkında yardımcı ol.\n"
				. "Bilmediğin konularda tahmin etme; iletişim bilgilerini öner.\n"
				. "Türkçe cevap ver. Kısa ve net ol.",
		];

		foreach ($defaults as $key => $value) {
			if (Settings::get($key) === '') {
				Settings::set($key, $value);
			}
		}

		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$flashType = 'success';
		$providers = self::providers();

		if (Tools::isSubmit('saveAiChat')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$provider = trim((string) Tools::getValue('provider', 'groq'));

				if (!isset($providers[$provider])) {
					$provider = 'groq';
				}

				Settings::set('AI_CHAT_ENABLED', Tools::getValue('enabled') ? '1' : '0');
				Settings::set('AI_CHAT_PROVIDER', $provider);
				Settings::set('AI_CHAT_TITLE', mb_substr(trim((string) Tools::getValue('title')), 0, 64));
				Settings::set('AI_CHAT_WELCOME', mb_substr(trim((string) Tools::getValue('welcome')), 0, 500));
				Settings::set('AI_CHAT_SYSTEM', mb_substr(trim((string) Tools::getValue('system_prompt')), 0, 4000));

				if ($provider === 'custom') {
					Settings::set('AI_CHAT_MODEL', mb_substr(trim((string) Tools::getValue('model')), 0, 128));
					Settings::set('AI_CHAT_BASE_URL', mb_substr(trim((string) Tools::getValue('base_url')), 0, 255));
				} elseif ($provider === 'rules') {
					Settings::set('AI_CHAT_MODEL', '');
					Settings::set('AI_CHAT_BASE_URL', '');
				} else {
					$preset = $providers[$provider];
					Settings::set('AI_CHAT_BASE_URL', $preset['base_url']);
					Settings::set('AI_CHAT_MODEL', $preset['model']);
				}

				$apiKey = trim((string) Tools::getValue('api_key'));

				if ($apiKey !== '' && $apiKey !== '********') {
					Settings::set('AI_CHAT_API_KEY', $apiKey);
				}

				$flash = 'Ayarlar kaydedildi';
			}
		}

		$provider = (string) (Settings::get('AI_CHAT_PROVIDER') ?: 'groq');

		if (!isset($providers[$provider])) {
			$provider = 'groq';
		}

		$hasKey = trim((string) Settings::get('AI_CHAT_API_KEY')) !== '';
		$providerList = [];

		foreach ($providers as $key => $row) {
			$providerList[] = array_merge($row, ['key' => $key]);
		}

		$smarty->assign([
			'flash' => $flash,
			'flashType' => $flashType,
			'aiChatEnabled' => Settings::get('AI_CHAT_ENABLED') !== '0',
			'aiChatProvider' => $provider,
			'aiChatProviders' => $providerList,
			'aiChatTitle' => Settings::get('AI_CHAT_TITLE') ?: 'Canlı Destek',
			'aiChatWelcome' => Settings::get('AI_CHAT_WELCOME'),
			'aiChatModel' => Settings::get('AI_CHAT_MODEL') ?: ($providers[$provider]['model'] ?? ''),
			'aiChatBaseUrl' => Settings::get('AI_CHAT_BASE_URL') ?: ($providers[$provider]['base_url'] ?? ''),
			'aiChatSystem' => Settings::get('AI_CHAT_SYSTEM'),
			'aiChatHasKey' => $hasKey,
			'aiChatApiKeyMasked' => $hasKey ? '********' : '',
			'aiChatNeedsKey' => $provider !== 'rules',
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'footer') {
			return null;
		}

		if (Settings::get('AI_CHAT_ENABLED') === '0') {
			return null;
		}

		global $domain;

		$html = $this->renderFrontTemplate('footer', [
			'aiChatTitle' => Settings::get('AI_CHAT_TITLE') ?: 'Canlı Destek',
			'aiChatWelcome' => Settings::get('AI_CHAT_WELCOME')
				?: 'Merhaba, ben yapay zeka asistanı, size nasıl yardımcı olabilirim?',
			'aiChatApiUrl' => rtrim((string) $domain, '/') . '/api/module.php?m=ai-chat&action=chat',
			'csrfToken' => (string) ($_SESSION['csrf_token'] ?? ''),
		]);

		return $html !== '' ? $html : null;
	}

	/** @param array<int, array{role:string,content:string}> $history */
	public static function reply(string $message, array $history = []): array
	{
		$message = trim($message);

		if ($message === '') {
			return ['success' => false, 'message' => 'Mesaj boş olamaz'];
		}

		if (mb_strlen($message) > 1000) {
			return ['success' => false, 'message' => 'Mesaj çok uzun'];
		}

		$identifier = self::rateLimitId();

		if (class_exists('RateLimit', false)
			&& RateLimit::isLimited('ai_chat', $identifier, 20, 600)
		) {
			return ['success' => false, 'message' => 'Çok fazla istek. Lütfen biraz sonra tekrar deneyin.'];
		}

		if (class_exists('RateLimit', false)) {
			RateLimit::record('ai_chat', $identifier);
		}

		$orderContext = self::findOrderContext($message);
		$storeContext = self::buildStoreContext();
		$provider = (string) (Settings::get('AI_CHAT_PROVIDER') ?: 'groq');
		$apiKey = trim((string) Settings::get('AI_CHAT_API_KEY'));

		if ($provider === 'rules' || $apiKey === '') {
			return [
				'success' => true,
				'reply' => self::fallbackReply($message, $orderContext, $storeContext),
				'mode' => 'fallback',
			];
		}

		$providers = self::providers();

		if ($provider !== 'custom' && isset($providers[$provider])) {
			$base = $providers[$provider]['base_url'];
			$model = $providers[$provider]['model'];
		} else {
			$base = rtrim((string) (Settings::get('AI_CHAT_BASE_URL') ?: 'https://api.openai.com/v1'), '/');
			$model = trim((string) (Settings::get('AI_CHAT_MODEL') ?: 'gpt-4o-mini'));
		}

		$system = trim((string) Settings::get('AI_CHAT_SYSTEM'));

		if ($system === '') {
			$system = 'Sen bir e-ticaret müşteri hizmetleri asistanısın. Türkçe, kısa ve nazik cevap ver.';
		}

		$system .= "\n\n--- Mağaza bilgileri ---\n" . $storeContext;

		if ($orderContext !== '') {
			$system .= "\n\n--- Sipariş / kargo bilgisi ---\n" . $orderContext;
		}

		$messages = [
			['role' => 'system', 'content' => $system],
		];

		foreach (array_slice($history, -8) as $row) {
			$role = ($row['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
			$content = trim((string) ($row['content'] ?? ''));

			if ($content !== '') {
				$messages[] = ['role' => $role, 'content' => mb_substr($content, 0, 1000)];
			}
		}

		$messages[] = ['role' => 'user', 'content' => $message];

		$result = self::callChatApi($messages, $apiKey, $base, $model);

		if (empty($result['success'])) {
			return [
				'success' => true,
				'reply' => self::fallbackReply($message, $orderContext, $storeContext),
				'mode' => 'fallback',
				'warning' => (string) ($result['message'] ?? ''),
			];
		}

		return [
			'success' => true,
			'reply' => (string) $result['reply'],
			'mode' => 'ai',
		];
	}

	private static function rateLimitId(): string
	{
		if (session_status() === PHP_SESSION_ACTIVE && session_id() !== '') {
			return 'ai-chat:' . session_id();
		}

		return 'ai-chat:' . substr(hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? '0')), 0, 24);
	}

	private static function buildStoreContext(): string
	{
		$lines = [
			'Mağaza: ' . (Settings::get('SITE_NAME') ?: 'Mağaza'),
			'Site: ' . Settings::get('DOMAIN'),
		];

		$email = trim((string) Settings::get('CONTACT_EMAIL'));
		$phone = trim((string) Settings::get('CONTACT_PHONE'));
		$address = trim((string) Settings::get('CONTACT_ADDRESS'));
		$city = trim((string) Settings::get('CONTACT_CITY'));

		if ($email !== '') {
			$lines[] = 'E-posta: ' . $email;
		}

		if ($phone !== '') {
			$lines[] = 'Telefon: ' . $phone;
		}

		if ($address !== '') {
			$lines[] = 'Adres: ' . $address . ($city !== '' ? ', ' . $city : '');
		}

		$lines[] = 'Müşteri hesabından sipariş takibi yapılabilir (Hesabım > Siparişler).';
		$lines[] = 'Sipariş numarası / referans verilirse kargo durumunu kontrol edebilirsin.';

		return implode("\n", $lines);
	}

	private static function findOrderContext(string $message): string
	{
		if (!preg_match('/\b([A-Z0-9]{6,20})\b/u', mb_strtoupper($message), $m)) {
			return '';
		}

		$ref = $m[1];

		$row = DB::execute(
			'SELECT id_order, reference, status, cargo_company, tracking_number, date_add
			 FROM orders
			 WHERE reference = ?
			 LIMIT 1',
			[$ref]
		);

		if (!$row || !isset($row[0])) {
			return '';
		}

		$order = $row[0];
		$status = (int) ($order['status'] ?? 0);
		$label = class_exists('Order', false) ? Order::getStatusLabel($status) : (string) $status;
		$cargo = trim((string) ($order['cargo_company'] ?? ''));
		$tracking = trim((string) ($order['tracking_number'] ?? ''));

		$out = [
			'Sipariş referansı: ' . $order['reference'],
			'Durum: ' . $label,
			'Tarih: ' . ($order['date_add'] ?? ''),
		];

		if ($cargo !== '') {
			$out[] = 'Kargo firması: ' . $cargo;
		}

		if ($tracking !== '') {
			$out[] = 'Takip no: ' . $tracking;

			if (class_exists('Cargo', false)) {
				$url = Cargo::buildTrackingUrl($tracking, $cargo);

				if ($url !== '') {
					$out[] = 'Takip linki: ' . $url;
				}
			}
		} else {
			$out[] = 'Henüz takip numarası tanımlanmamış.';
		}

		return implode("\n", $out);
	}

	/** @param array<int, array{role:string,content:string}> $messages */
	private static function callChatApi(array $messages, string $apiKey = '', string $base = '', string $model = ''): array
	{
		if ($apiKey === '') {
			$apiKey = trim((string) Settings::get('AI_CHAT_API_KEY'));
		}

		if ($base === '') {
			$base = rtrim((string) (Settings::get('AI_CHAT_BASE_URL') ?: 'https://api.openai.com/v1'), '/');
		} else {
			$base = rtrim($base, '/');
		}

		if ($model === '') {
			$model = trim((string) (Settings::get('AI_CHAT_MODEL') ?: 'gpt-4o-mini'));
		}

		$payload = json_encode([
			'model' => $model,
			'messages' => $messages,
			'temperature' => 0.4,
			'max_tokens' => 500,
		], JSON_UNESCAPED_UNICODE);

		if ($payload === false) {
			return ['success' => false, 'message' => 'İstek oluşturulamadı'];
		}

		$url = $base . '/chat/completions';
		$response = null;
		$error = '';
		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $apiKey,
		];

		if (stripos($base, 'openrouter.ai') !== false) {
			$headers[] = 'HTTP-Referer: ' . rtrim((string) Settings::get('DOMAIN'), '/');
			$headers[] = 'X-Title: FShop AI Chat';
		}

		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			curl_setopt_array($ch, [
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_POSTFIELDS => $payload,
				CURLOPT_TIMEOUT => 45,
				CURLOPT_CONNECTTIMEOUT => 10,
			]);
			$response = curl_exec($ch);
			$error = curl_error($ch);
			$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($response === false) {
				return ['success' => false, 'message' => 'API bağlantı hatası: ' . $error];
			}

			if ($code >= 400) {
				$decoded = json_decode((string) $response, true);
				$apiMsg = is_array($decoded) ? (string) ($decoded['error']['message'] ?? $decoded['message'] ?? '') : '';

				return ['success' => false, 'message' => $apiMsg !== '' ? $apiMsg : ('API HTTP ' . $code)];
			}
		} else {
			$ctx = stream_context_create([
				'http' => [
					'method' => 'POST',
					'header' => implode("\r\n", $headers) . "\r\n",
					'content' => $payload,
					'timeout' => 45,
					'ignore_errors' => true,
				],
			]);
			$response = @file_get_contents($url, false, $ctx);

			if ($response === false) {
				return ['success' => false, 'message' => 'API bağlantı hatası'];
			}
		}

		$data = json_decode((string) $response, true);
		$reply = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

		if ($reply === '') {
			return ['success' => false, 'message' => 'Boş AI yanıtı'];
		}

		return ['success' => true, 'reply' => $reply];
	}

	private static function fallbackReply(string $message, string $orderContext, string $storeContext): string
	{
		$lower = mb_strtolower($message);

		if ($orderContext !== '') {
			return "Siparişinizi buldum:\n\n" . $orderContext . "\n\nBaşka bir konuda yardımcı olabilir miyim?";
		}

		if (preg_match('/iletişim|telefon|mail|e-?posta|adres|nerede|ulaş/u', $lower)) {
			$bits = [];
			$email = trim((string) Settings::get('CONTACT_EMAIL'));
			$phone = trim((string) Settings::get('CONTACT_PHONE'));
			$address = trim((string) Settings::get('CONTACT_ADDRESS'));

			if ($phone !== '') {
				$bits[] = 'Telefon: ' . $phone;
			}

			if ($email !== '') {
				$bits[] = 'E-posta: ' . $email;
			}

			if ($address !== '') {
				$bits[] = 'Adres: ' . $address;
			}

			if ($bits) {
				return "İletişim bilgilerimiz:\n" . implode("\n", $bits);
			}
		}

		if (preg_match('/kargo|sipariş|takip|nerede/u', $lower)) {
			return 'Siparişinizi takip etmek için sipariş numaranızı (referans) yazabilirsiniz. '
				. 'Ayrıca Hesabım > Siparişler bölümünden de takip edebilirsiniz.';
		}

		if (preg_match('/iade|iptal|değişim/u', $lower)) {
			return 'İade ve iptal işlemleri için Hesabım üzerinden ilgili siparişi seçebilir '
				. 'veya iletişim kanallarımızdan bize ulaşabilirsiniz.';
		}

		$site = Settings::get('SITE_NAME') ?: 'mağazamız';
		$phone = trim((string) Settings::get('CONTACT_PHONE'));
		$extra = $phone !== '' ? ' Telefon: ' . $phone : '';

		return "{$site} yapay zeka asistanıyım. Sipariş, kargo veya iletişim hakkında sorabilirsiniz.{$extra}";
	}
}
