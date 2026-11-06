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
use DB;

header('Content-Type: application/json; charset=utf-8');

function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

$limit = Tools::getValue('limit') ? max(10, min(100, (int)Tools::getValue('limit'))) : 20;
$page = Tools::getValue('page') ? max(1, (int)Tools::getValue('page')) : 1;
$offset = ($page - 1) * $limit;
$search = trim((string)Tools::getValue('search'));
$status = trim((string)Tools::getValue('status'));
$sort = trim((string)Tools::getValue('sort', 'id DESC'));

// Sanitize sort column
$allowedSorts = [
    'id DESC' => 'id DESC',
    'id ASC' => 'id ASC',
    'original_size DESC' => 'original_size DESC',
    'original_size ASC' => 'original_size ASC',
    'optimized_size DESC' => 'optimized_size DESC',
    'optimized_size ASC' => 'optimized_size ASC',
];
if (!isset($allowedSorts[$sort])) {
    $sort = 'id DESC';
}

// Check single image details request
$imageId = Tools::getValue('id') ? (int)Tools::getValue('id') : 0;
if ($imageId > 0) {
    $image = ImageRepository::findById($imageId);
    if ($image) {
        $image['original_size_formatted'] = formatBytes($image['original_size']);
        $image['optimized_size_formatted'] = $image['optimized_size'] !== null ? formatBytes($image['optimized_size']) : '-';
        $image['saved_percentage'] = ($image['optimized_size'] !== null && $image['original_size'] > 0) 
            ? round((($image['original_size'] - $image['optimized_size']) / $image['original_size']) * 100, 1) 
            : 0;
        
        // Return full path url
        global $domain;
        $image['url'] = rtrim($domain, '/') . '/' . ltrim($image['file_path'], '/');
        $image['webp_url'] = $image['url'] . '.webp';
        
        echo json_encode([
            'success' => true,
            'image' => $image
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Görsel bulunamadı.']);
    }
    exit;
}

$images = ImageRepository::getAll($limit, $offset, $search, $status, $sort);
$total = ImageRepository::countAll($search, $status);

// Format output rows
foreach ($images as &$img) {
    $img['original_size_formatted'] = formatBytes($img['original_size']);
    $img['optimized_size_formatted'] = $img['optimized_size'] !== null ? formatBytes($img['optimized_size']) : '-';
    $img['saved_percentage'] = ($img['optimized_size'] !== null && $img['original_size'] > 0) 
        ? round((($img['original_size'] - $img['optimized_size']) / $img['original_size']) * 100, 1) 
        : 0;
}

if (ob_get_length()) { @ob_clean(); }
echo json_encode([
    'success' => true,
    'images' => $images,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'total_pages' => ceil($total / $limit)
]);
exit;
