<?php

if (!defined('IN_SCRIPT')) {
    exit;
}

if (!class_exists('Admin')) {
    require_once dirname(__DIR__, 3) . '/core/Admin.php';
}

if (!Admin::isLoggedIn()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

use ImageOptimizer\Service\QueueService;

header('Content-Type: application/json; charset=utf-8');

$queueService = new QueueService();
$count = $queueService->retryFailed();

echo json_encode([
    'success' => true,
    'message' => "Başarısız olan {$count} adet görsel tekrar kuyruğa alındı."
]);
exit;
