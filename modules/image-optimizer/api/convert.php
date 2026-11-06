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

if ($queueService->isPaused()) {
    echo json_encode([
        'success' => false,
        'paused' => true,
        'message' => 'Dönüştürme işlemi duraklatıldı.'
    ]);
    exit;
}

$customBatch = Tools::getValue('batch_size') ? (int)Tools::getValue('batch_size') : null;
$stats = $queueService->processBatch($customBatch);

echo json_encode([
    'success' => true,
    'stats' => $stats
]);
exit;
