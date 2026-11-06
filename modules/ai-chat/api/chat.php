<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
	exit;
}

$token = (string) (Tools::getValue('token') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
$sessionToken = (string) ($_SESSION['csrf_token'] ?? '');

if ($sessionToken === '' || $token === '' || !hash_equals($sessionToken, $token)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Geçersiz istek'], JSON_UNESCAPED_UNICODE);
	exit;
}

if (Settings::get('AI_CHAT_ENABLED') === '0') {
	echo json_encode(['success' => false, 'message' => 'Canlı destek kapalı'], JSON_UNESCAPED_UNICODE);
	exit;
}

$message = trim((string) Tools::getValue('message'));
$historyRaw = Tools::getValue('history', '[]');

if (is_string($historyRaw)) {
	$decoded = json_decode($historyRaw, true);
} elseif (is_array($historyRaw)) {
	$decoded = $historyRaw;
} else {
	$decoded = [];
}

$history = [];

if (is_array($decoded)) {
	foreach (array_slice($decoded, -8) as $row) {
		if (!is_array($row)) {
			continue;
		}

		$role = (($row['role'] ?? '') === 'assistant') ? 'assistant' : 'user';
		$content = trim((string) ($row['content'] ?? ''));

		if ($content !== '') {
			$history[] = [
				'role' => $role,
				'content' => mb_substr($content, 0, 1000),
			];
		}
	}
}

$result = AiChatModule::reply($message, $history);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
