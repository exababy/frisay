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

use ImageOptimizer\Repository\ImageRepository;
use ImageOptimizer\Repository\QueueRepository;
use DB;

header('Content-Type: application/json; charset=utf-8');

$stats = ImageRepository::getStats();
$stats['pending'] = QueueRepository::countPending();

// Get daily statistics for charts (last 15 days)
$chartRows = DB::execute("SELECT * FROM `image_opt_statistics` ORDER BY `date` DESC LIMIT 15") ?: [];
$stats['chart_data'] = array_reverse($chartRows);

// Engine info
$engines = [];
if (class_exists('Imagick')) $engines[] = 'Imagick';
if (function_exists('gd_info')) {
    $gd = gd_info();
    if (!empty($gd['WebP Support'])) $engines[] = 'GD (WebP)';
}
$stats['server_engines'] = $engines;

if (ob_get_length()) { @ob_clean(); }
echo json_encode([
    'success' => true,
    'stats' => $stats
]);
exit;
