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

use ImageOptimizer\Repository\LogRepository;

$export = Tools::getValue('export');

if ($export !== '' && $export !== false) {
    // Perform logs export
    $logs = LogRepository::getLogs(10000, 0); // Export up to 10k logs

    if ($export === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=image_optimizer_logs_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Görsel ID', 'Seviye', 'İşlem', 'Mesaj', 'Tarih']);
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['image_id'],
                $log['level'],
                $log['action'],
                $log['message'],
                $log['created_at']
            ]);
        }
        fclose($output);
        exit;
    } elseif ($export === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=image_optimizer_logs_' . date('Y-m-d') . '.json');
        echo json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    } elseif ($export === 'txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename=image_optimizer_logs_' . date('Y-m-d') . '.txt');
        foreach ($logs as $log) {
            echo sprintf("[%s] [%s] [%s] %s\n", $log['created_at'], strtoupper($log['level']), $log['action'], $log['message']);
        }
        exit;
    }
}

// Normal paginated JSON response
header('Content-Type: application/json; charset=utf-8');

$limit = Tools::getValue('limit') ? max(10, min(100, (int)Tools::getValue('limit'))) : 50;
$page = Tools::getValue('page') ? max(1, (int)Tools::getValue('page')) : 1;
$offset = ($page - 1) * $limit;
$search = trim((string)Tools::getValue('search'));
$level = trim((string)Tools::getValue('level'));

$logs = LogRepository::getLogs($limit, $offset, $search, $level);
$total = LogRepository::countLogs($search, $level);

echo json_encode([
    'success' => true,
    'logs' => $logs,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'total_pages' => ceil($total / $limit)
]);
exit;
