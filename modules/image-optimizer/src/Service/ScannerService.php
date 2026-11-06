<?php

namespace ImageOptimizer\Service;

use ImageOptimizer\Core\Settings;
use ImageOptimizer\Repository\ImageRepository;
use ImageOptimizer\Repository\QueueRepository;
use ImageOptimizer\Repository\LogRepository;

class ScannerService
{
    private string $rootPath;
    private array $supportedExtensions = ['jpg', 'jpeg', 'png', 'bmp', 'tif', 'tiff', 'gif'];

    public function __construct()
    {
        // Root path is the parent of modules directory
        $this->rootPath = rtrim(str_replace('\\', '/', dirname(__DIR__, 4)), '/');
    }

    public function scan(?string $customFolder = null): array
    {
        $stats = [
            'found' => 0,
            'added' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        // Retrieve scanning configuration
        $excludeFoldersRaw = Settings::get('exclude_folders', 'cache,logs,backup,vendor,node_modules,tmp,temp,.git');
        $excludeFolders = array_map('trim', explode(',', $excludeFoldersRaw));

        $excludeFilesRaw = Settings::get('exclude_files', '');
        $excludeFiles = array_filter(array_map('trim', explode(',', $excludeFilesRaw)));

        $regexExclusions = Settings::get('regex_exclusions', '');

        $minKb = (int) Settings::get('min_kb', 0);
        $maxMb = (int) Settings::get('max_mb', 100);

        // Determine targets to scan
        $targets = [];
        if ($customFolder !== null && $customFolder !== '') {
            $fullPath = $this->rootPath . '/' . ltrim($customFolder, '/');
            if (is_dir($fullPath)) {
                $targets[] = $fullPath;
            }
        } else {
            $locationsJson = Settings::get('scan_locations', '["img","modules","templates"]');
            $locations = json_decode($locationsJson, true) ?: ['img', 'modules', 'templates'];
            foreach ($locations as $loc) {
                $fullPath = $this->rootPath . '/' . ltrim($loc, '/');
                if (is_dir($fullPath)) {
                    $targets[] = $fullPath;
                }
            }
        }

        if ($targets === []) {
            LogRepository::log(null, 'error', 'SCAN', 'Taranacak hedef klasör bulunamadı.');
            return $stats;
        }

        LogRepository::log(null, 'info', 'SCAN', 'Görsel tarama işlemi başlatıldı.');

        foreach ($targets as $target) {
            $this->scanDirectory($target, $excludeFolders, $excludeFiles, $regexExclusions, $minKb, $maxMb, $stats);
        }

        LogRepository::log(
            null,
            'success',
            'SCAN',
            sprintf("Tarama tamamlandı. Bulunan: %d, Yeni: %d, Güncellenen: %d, Atlanan: %d", $stats['found'], $stats['added'], $stats['updated'], $stats['skipped'])
        );

        return $stats;
    }

    private function scanDirectory(
        string $dir,
        array $excludeFolders,
        array $excludeFiles,
        string $regexExclusions,
        int $minKb,
        int $maxMb,
        array &$stats
    ): void {
        $normalizedDir = str_replace('\\', '/', $dir);
        $files = @scandir($normalizedDir);
        if ($files === false) {
            $stats['errors']++;
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = str_replace('\\', '/', $normalizedDir . '/' . $file);
            $relativePath = ltrim(str_replace($this->rootPath, '', $fullPath), '/');

            if (is_dir($fullPath)) {
                // Check folder exclusions
                if (in_array($file, $excludeFolders, true)) {
                    continue;
                }
                $this->scanDirectory($fullPath, $excludeFolders, $excludeFiles, $regexExclusions, $minKb, $maxMb, $stats);
            } else {
                $stats['found']++;

                // Get file extension
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (!in_array($ext, $this->supportedExtensions, true)) {
                    $stats['skipped']++;
                    continue;
                }

                // Check file exclusions
                if (in_array($file, $excludeFiles, true)) {
                    $stats['skipped']++;
                    continue;
                }

                // Check regex exclusion
                if ($regexExclusions !== '' && @preg_match($regexExclusions, $relativePath)) {
                    $stats['skipped']++;
                    continue;
                }

                // Check size boundaries
                $size = @filesize($fullPath);
                if ($size === false) {
                    $stats['skipped']++;
                    continue;
                }

                $sizeKb = $size / 1024;
                $sizeMb = $sizeKb / 1024;

                if (($minKb > 0 && $sizeKb < $minKb) || ($maxMb > 0 && $sizeMb > $maxMb)) {
                    $stats['skipped']++;
                    continue;
                }

                // Calculate hash
                $hash = hash_file('sha256', $fullPath);
                if ($hash === false) {
                    $stats['errors']++;
                    continue;
                }

                // Determine category
                $category = 'other';
                if (strpos($relativePath, 'img/products') === 0) {
                    $category = 'products';
                } elseif (strpos($relativePath, 'img/') === 0) {
                    $category = 'img';
                } elseif (strpos($relativePath, 'modules/') === 0) {
                    $category = 'modules';
                } elseif (strpos($relativePath, 'templates/') === 0) {
                    $category = 'templates';
                }

                // Check db
                $existing = ImageRepository::findByPath($relativePath);
                if ($existing) {
                    if ($existing['hash'] !== $hash) {
                        // Image modified! Mark as pending for reconversion
                        $existing['hash'] = $hash;
                        $existing['original_size'] = $size;
                        $existing['status'] = 'pending';
                        $existing['error_message'] = null;
                        
                        ImageRepository::save($existing);
                        QueueRepository::add((int)$existing['id']);
                        $stats['updated']++;
                    }
                } else {
                    // New image!
                    $imgId = ImageRepository::save([
                        'file_path' => $relativePath,
                        'hash' => $hash,
                        'original_size' => $size,
                        'status' => 'pending',
                        'category' => $category
                    ]);

                    if ($imgId) {
                        QueueRepository::add((int)$imgId);
                        $stats['added']++;
                    } else {
                        $stats['errors']++;
                    }
                }
            }
        }
    }
}
