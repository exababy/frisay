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

use ImageOptimizer\Service\BackupService;

header('Content-Type: application/json; charset=utf-8');

$backupService = new BackupService();
$imageId = Tools::getValue('image_id') ? (int)Tools::getValue('image_id') : 0;
$action = Tools::getValue('type', 'all');

if ($imageId > 0) {
    $res = $backupService->restore($imageId);
    echo json_encode([
        'success' => $res,
        'message' => $res ? 'Görsel başarıyla geri yüklendi.' : 'Geri yükleme başarısız oldu.'
    ]);
} elseif ($action === 'all') {
    $count = $backupService->restoreAll();
    echo json_encode([
        'success' => true,
        'message' => "Toplam {$count} adet görsel başarıyla yedekten geri yüklendi."
    ]);
} elseif ($action === 'delete_backups') {
    $count = $backupService->deleteBackups();
    echo json_encode([
        'success' => true,
        'message' => "Toplam {$count} adet yedek görsel sunucudan silindi."
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz parametreler.'
    ]);
}
exit;
