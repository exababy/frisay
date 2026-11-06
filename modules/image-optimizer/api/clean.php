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
use ImageOptimizer\Repository\LogRepository;
use ImageOptimizer\Repository\BackupRepository;
use DB;

header('Content-Type: application/json; charset=utf-8');

$action = Tools::getValue('type');
$rootPath = rtrim(str_replace('\\', '/', dirname(__DIR__, 4)), '/');

if ($action === 'clear_queue') {
    QueueRepository::clear();
    LogRepository::log(null, 'warning', 'QUEUE', 'Kuyruk tamamen temizlendi.');
    echo json_encode(['success' => true, 'message' => 'Kuyruk başarıyla temizlendi.']);
} elseif ($action === 'delete_originals') {
    $images = DB::execute("SELECT id, file_path FROM `image_opt_images` WHERE status = 'converted'") ?: [];
    $count = 0;
    foreach ($images as $img) {
        $sourceFile = $rootPath . '/' . ltrim($img['file_path'], '/');
        if (file_exists($sourceFile) && file_exists($sourceFile . '.webp')) {
            if (@unlink($sourceFile)) {
                $count++;
            }
        }
    }
    LogRepository::log(null, 'warning', 'DELETE', "Optimize edilmiş {$count} adet orijinal görsel sunucudan silindi.");
    echo json_encode(['success' => true, 'message' => "Toplam {$count} adet orijinal görsel başarıyla silindi."]);
} elseif ($action === 'delete_webp') {
    $images = DB::execute("SELECT id, file_path FROM `image_opt_images` WHERE status = 'converted'") ?: [];
    $count = 0;
    foreach ($images as $img) {
        $webpFile = $rootPath . '/' . ltrim($img['file_path'], '/') . '.webp';
        if (file_exists($webpFile)) {
            if (@unlink($webpFile)) {
                $count++;
            }
        }
        // Update database record to pending
        DB::update('image_opt_images', [
            'status' => 'pending',
            'optimized_size' => null,
            'engine_used' => null
        ], 'id = :id', ['id' => (int)$img['id']]);

        // Re-queue
        QueueRepository::add((int)$img['id']);
    }
    LogRepository::log(null, 'warning', 'DELETE', "Üretilmiş {$count} adet WebP görseli silindi ve yeniden dönüştürme için kuyruğa eklendi.");
    echo json_encode(['success' => true, 'message' => "Toplam {$count} adet WebP görseli silindi ve kuyruk sıfırlandı."]);
} elseif ($action === 'reset_all') {
    // Delete all webp files
    $images = DB::execute("SELECT file_path FROM `image_opt_images`") ?: [];
    foreach ($images as $img) {
        $webpFile = $rootPath . '/' . ltrim($img['file_path'], '/') . '.webp';
        if (file_exists($webpFile)) {
            @unlink($webpFile);
        }
    }
    
    // Truncate tables
    DB::execute("TRUNCATE TABLE `image_opt_backups`");
    DB::execute("TRUNCATE TABLE `image_opt_queue`");
    DB::execute("TRUNCATE TABLE `image_opt_statistics`");
    DB::execute("TRUNCATE TABLE `image_opt_images`");
    DB::execute("TRUNCATE TABLE `image_opt_logs`");

    LogRepository::log(null, 'warning', 'RESET', 'Modül verileri ve optimize edilmiş görseller tamamen sıfırlandı.');
    echo json_encode(['success' => true, 'message' => 'Tüm veriler ve üretilen görseller sıfırlandı.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz temizleme işlemi.']);
}
exit;
