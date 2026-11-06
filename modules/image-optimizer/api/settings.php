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

use ImageOptimizer\Core\Settings;
use ImageOptimizer\Repository\LogRepository;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF / Token
    $token = Tools::getValue('token');
    global $adminToken;
    if (!hash_equals($adminToken, (string)$token)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz CSRF token.']);
        exit;
    }

    $quality = max(10, min(100, (int)Tools::getValue('compression_quality', 85)));
    $maxWidth = Tools::getValue('max_resize_width') !== '' ? (int)Tools::getValue('max_resize_width') : '';
    $maxHeight = Tools::getValue('max_resize_height') !== '' ? (int)Tools::getValue('max_resize_height') : '';
    $keepTrans = Tools::getValue('keep_transparency', 'yes') === 'yes' ? 'yes' : 'no';
    
    $stripMeta = Tools::getValue('strip_metadata', 'yes') === 'yes' ? 'yes' : 'no';
    $stripExif = Tools::getValue('strip_exif', 'yes') === 'yes' ? 'yes' : 'no';
    $stripGps = Tools::getValue('strip_gps', 'yes') === 'yes' ? 'yes' : 'no';
    $stripCamera = Tools::getValue('strip_camera_info', 'yes') === 'yes' ? 'yes' : 'no';

    $keepOriginals = Tools::getValue('keep_original_files', 'yes') === 'yes' ? 'yes' : 'no';
    $backupOriginals = Tools::getValue('backup_originals', 'yes') === 'yes' ? 'yes' : 'no';
    $deleteOriginalsAfterSuccess = Tools::getValue('delete_originals_after_success', 'no') === 'yes' ? 'yes' : 'no';

    $excludeFolders = trim((string)Tools::getValue('exclude_folders'));
    $excludeFiles = trim((string)Tools::getValue('exclude_files'));
    $regexExclusions = trim((string)Tools::getValue('regex_exclusions'));

    $minKb = max(0, (int)Tools::getValue('min_kb', 0));
    $maxMb = max(1, (int)Tools::getValue('max_mb', 100));
    $batchSize = max(5, min(1000, (int)Tools::getValue('batch_size', 50)));

    $enginePref = trim((string)Tools::getValue('engine_preference', 'auto'));

    $scanLocs = Tools::getValue('scan_locations', []);
    if (!is_array($scanLocs)) {
        $scanLocs = ['img'];
    }

    Settings::saveAll([
        'compression_quality' => $quality,
        'max_resize_width' => $maxWidth,
        'max_resize_height' => $maxHeight,
        'keep_transparency' => $keepTrans,
        'strip_metadata' => $stripMeta,
        'strip_exif' => $stripExif,
        'strip_gps' => $stripGps,
        'strip_camera_info' => $stripCamera,
        'keep_original_files' => $keepOriginals,
        'backup_originals' => $backupOriginals,
        'delete_originals_after_success' => $deleteOriginalsAfterSuccess,
        'exclude_folders' => $excludeFolders,
        'exclude_files' => $excludeFiles,
        'regex_exclusions' => $regexExclusions,
        'min_kb' => $minKb,
        'max_mb' => $maxMb,
        'batch_size' => $batchSize,
        'engine_preference' => $enginePref,
        'scan_locations' => json_encode($scanLocs)
    ]);

    LogRepository::log(null, 'info', 'SETTINGS', 'Ayarlar başarıyla güncellendi.');
    echo json_encode(['success' => true, 'message' => 'Ayarlar başarıyla kaydedildi.']);
} else {
    echo json_encode([
        'success' => true,
        'settings' => Settings::getAll()
    ]);
}
exit;
