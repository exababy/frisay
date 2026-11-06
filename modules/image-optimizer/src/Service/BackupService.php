<?php

namespace ImageOptimizer\Service;

use ImageOptimizer\Repository\BackupRepository;
use ImageOptimizer\Repository\ImageRepository;
use ImageOptimizer\Repository\LogRepository;
use ImageOptimizer\Repository\QueueRepository;

class BackupService
{
    private string $rootPath;
    private string $backupDir;

    public function __construct()
    {
        $this->rootPath = rtrim(str_replace('\\', '/', dirname(__DIR__, 4)), '/');
        $this->backupDir = $this->rootPath . '/img/backups/image_opt_backups';
    }

    public function backup(int $imageId, string $relativePath): bool
    {
        $sourceFile = $this->rootPath . '/' . ltrim($relativePath, '/');
        if (!file_exists($sourceFile)) {
            return false;
        }

        if (!is_dir($this->backupDir)) {
            if (!@mkdir($this->backupDir, 0755, true)) {
                LogRepository::log($imageId, 'error', 'BACKUP', 'Yedekleme klasörü oluşturulamadı.');
                return false;
            }
        }

        // Create a unique backup filename to avoid collisions
        $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
        $backupFilename = $imageId . '_' . md5($relativePath) . '.' . $ext;
        $backupPath = $this->backupDir . '/' . $backupFilename;

        if (@copy($sourceFile, $backupPath)) {
            // Save in database
            $relativeBackupPath = 'img/backups/image_opt_backups/' . $backupFilename;
            BackupRepository::add($imageId, $relativeBackupPath);
            LogRepository::log($imageId, 'info', 'BACKUP', 'Orijinal görsel yedeklendi: ' . $relativePath);
            return true;
        }

        LogRepository::log($imageId, 'error', 'BACKUP', 'Yedek kopyalama başarısız oldu: ' . $relativePath);
        return false;
    }

    public function restore(int $imageId): bool
    {
        $backup = BackupRepository::findByImageId($imageId);
        if (!$backup) {
            return false;
        }

        $image = ImageRepository::findById($imageId);
        if (!$image) {
            return false;
        }

        $sourceFile = $this->rootPath . '/' . ltrim($image['file_path'], '/');
        $backupFile = $this->rootPath . '/' . ltrim($backup['backup_path'], '/');

        if (!file_exists($backupFile)) {
            LogRepository::log($imageId, 'error', 'RESTORE', 'Yedek dosyası bulunamadı: ' . $backup['backup_path']);
            return false;
        }

        // Check if there is a converted WebP file next to the original and remove it
        $webpFile = $sourceFile . '.webp';
        if (file_exists($webpFile)) {
            @unlink($webpFile);
        }

        // Copy backup back to original location
        $destDir = dirname($sourceFile);
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        if (@copy($backupFile, $sourceFile)) {
            // Update image status in DB
            $image['status'] = 'pending';
            $image['optimized_size'] = null;
            $image['engine_used'] = null;
            ImageRepository::save($image);

            // Re-add to queue
            QueueRepository::add($imageId);

            // Remove backup file and record
            @unlink($backupFile);
            BackupRepository::remove($imageId);

            LogRepository::log($imageId, 'success', 'RESTORE', 'Görsel yedekten başarıyla geri yüklendi: ' . $image['file_path']);
            return true;
        }

        LogRepository::log($imageId, 'error', 'RESTORE', 'Geri yükleme kopyalama hatası: ' . $image['file_path']);
        return false;
    }

    public function restoreAll(): int
    {
        $backups = BackupRepository::getAll();
        $count = 0;
        foreach ($backups as $b) {
            if ($this->restore((int)$b['image_id'])) {
                $count++;
            }
        }
        return $count;
    }

    public function deleteBackups(): int
    {
        $backups = BackupRepository::getAll();
        $count = 0;
        foreach ($backups as $b) {
            $backupFile = $this->rootPath . '/' . ltrim($b['backup_path'], '/');
            if (file_exists($backupFile)) {
                @unlink($backupFile);
            }
            BackupRepository::remove((int)$b['image_id']);
            $count++;
        }
        
        // Try to clear backup dir
        if (is_dir($this->backupDir)) {
            @rmdir($this->backupDir);
        }

        return $count;
    }
}
