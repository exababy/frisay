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

use ImageOptimizer\Service\ScannerService;

header('Content-Type: application/json; charset=utf-8');

$customFolder = Tools::getValue('folder');

$scanner = new ScannerService();
$stats = $scanner->scan($customFolder);

echo json_encode([
    'success' => true,
    'message' => 'Tarama işlemi başarıyla tamamlandı.',
    'stats' => $stats
]);
exit;
