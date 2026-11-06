<?php

namespace ImageOptimizer\Service;

use ImageOptimizer\Core\Settings;
use ImageOptimizer\Repository\ImageRepository;
use ImageOptimizer\Repository\LogRepository;
use ImageOptimizer\Repository\QueueRepository;

class OptimizerService
{
    private string $rootPath;
    private BackupService $backupService;

    public function __construct()
    {
        $this->rootPath = rtrim(str_replace('\\', '/', dirname(__DIR__, 4)), '/');
        $this->backupService = new BackupService();
    }

    public function getAvailableEngines(): array
    {
        $engines = [];

        // Check Imagick
        if (class_exists('Imagick')) {
            $engines[] = 'Imagick';
        }

        // Check GD
        if (function_exists('gd_info')) {
            $gdInfo = gd_info();
            if (isset($gdInfo['WebP Support']) && $gdInfo['WebP Support']) {
                $engines[] = 'GD';
            }
        }

        // Check ImageMagick CLI (exec check)
        if (function_exists('exec')) {
            $output = [];
            $returnVar = 0;
            @exec('magick -version 2>&1', $output, $returnVar);
            if ($returnVar === 0) {
                $engines[] = 'ImageMagick CLI';
            } else {
                @exec('convert -version 2>&1', $output, $returnVar);
                if ($returnVar === 0) {
                    $engines[] = 'ImageMagick CLI';
                }
            }
        }

        return $engines;
    }

    public function getSelectedEngine(): string
    {
        $available = $this->getAvailableEngines();
        if ($available === []) {
            throw new \Exception('Sistemde WebP formatını destekleyen aktif bir görsel kütüphanesi (Imagick, GD, ImageMagick) bulunamadı.');
        }

        // Check settings preference
        $preference = Settings::get('engine_preference', 'auto');
        if ($preference !== 'auto' && in_array($preference, $available, true)) {
            return $preference;
        }

        // Auto select by priority
        if (in_array('Imagick', $available, true)) {
            return 'Imagick';
        }
        if (in_array('GD', $available, true)) {
            return 'GD';
        }
        return 'ImageMagick CLI';
    }

    public function convert(int $imageId): bool
    {
        $image = ImageRepository::findById($imageId);
        if (!$image) {
            return false;
        }

        $sourceFile = $this->rootPath . '/' . ltrim($image['file_path'], '/');
        if (!file_exists($sourceFile)) {
            $image['status'] = 'failed';
            $image['error_message'] = 'Görsel dosyası bulunamadı.';
            ImageRepository::save($image);
            QueueRepository::deleteByImageId($imageId);
            LogRepository::log($imageId, 'error', 'CONVERT', 'Görsel bulunamadı: ' . $image['file_path']);
            return false;
        }

        // Backup if enabled
        if (Settings::get('backup_originals', 'yes') === 'yes') {
            $this->backupService->backup($imageId, $image['file_path']);
        }

        // Determine destination file
        $destFile = $sourceFile . '.webp';

        // Retrieve settings
        $quality = (int) Settings::get('compression_quality', 85);
        $maxWidth = Settings::get('max_resize_width', '') !== '' ? (int) Settings::get('max_resize_width') : null;
        $maxHeight = Settings::get('max_resize_height', '') !== '' ? (int) Settings::get('max_resize_height') : null;
        
        $stripMetadata = Settings::get('strip_metadata', 'yes') === 'yes';
        $stripExif = Settings::get('strip_exif', 'yes') === 'yes';
        $stripGps = Settings::get('strip_gps', 'yes') === 'yes';
        $stripCamera = Settings::get('strip_camera_info', 'yes') === 'yes';

        try {
            $engine = $this->getSelectedEngine();
            $success = false;

            // Get original dimensions
            $sizeInfo = @getimagesize($sourceFile);
            $origWidth = $sizeInfo ? $sizeInfo[0] : null;
            $origHeight = $sizeInfo ? $sizeInfo[1] : null;

            if ($engine === 'Imagick') {
                $success = $this->convertWithImagick($sourceFile, $destFile, $quality, $maxWidth, $maxHeight, $stripMetadata || $stripExif || $stripGps || $stripCamera);
            } elseif ($engine === 'GD') {
                $success = $this->convertWithGD($sourceFile, $destFile, $quality, $maxWidth, $maxHeight);
            } elseif ($engine === 'ImageMagick CLI') {
                $success = $this->convertWithCLI($sourceFile, $destFile, $quality, $maxWidth, $maxHeight, $stripMetadata || $stripExif || $stripGps || $stripCamera);
            }

            if ($success && file_exists($destFile)) {
                $origSize = filesize($sourceFile);
                $optSize = filesize($destFile);

                // Smart conversion check: WebP must be smaller
                if ($optSize >= $origSize) {
                    @unlink($destFile);
                    $image['status'] = 'skipped';
                    $image['error_message'] = 'WebP formatı orijinal boyuttan daha büyük veya eşit.';
                    ImageRepository::save($image);
                    QueueRepository::deleteByImageId($imageId);
                    LogRepository::log($imageId, 'warning', 'CONVERT', 'Atlandı (WebP orijinal boyuttan daha büyük): ' . $image['file_path']);
                    return true;
                }

                // Get optimized dimensions
                $optSizeInfo = @getimagesize($destFile);
                $optWidth = $optSizeInfo ? $optSizeInfo[0] : null;
                $optHeight = $optSizeInfo ? $optSizeInfo[1] : null;

                // Update database record
                $image['status'] = 'converted';
                $image['optimized_size'] = $optSize;
                $image['original_width'] = $origWidth;
                $image['original_height'] = $origHeight;
                $image['optimized_width'] = $optWidth;
                $image['optimized_height'] = $optHeight;
                $image['engine_used'] = $engine;
                $image['error_message'] = null;
                ImageRepository::save($image);

                // Delete original after success if configured
                if (Settings::get('delete_originals_after_success', 'no') === 'yes') {
                    @unlink($sourceFile);
                    LogRepository::log($imageId, 'info', 'DELETE', 'Orijinal görsel başarıyla silindi: ' . $image['file_path']);
                }

                QueueRepository::deleteByImageId($imageId);
                LogRepository::log($imageId, 'success', 'CONVERT', sprintf("Başarıyla optimize edildi (%s). Kayıp: %s%%", $engine, round((($origSize - $optSize) / $origSize) * 100, 1)));
                return true;
            } else {
                throw new \Exception('Dönüştürme işlemi başarısız oldu (Bilinmeyen hata).');
            }

        } catch (\Throwable $e) {
            $image['status'] = 'failed';
            $image['error_message'] = $e->getMessage();
            ImageRepository::save($image);
            QueueRepository::deleteByImageId($imageId);
            LogRepository::log($imageId, 'error', 'CONVERT', 'Hata: ' . $e->getMessage());
            return false;
        }
    }

    private function convertWithImagick(
        string $source,
        string $dest,
        int $quality,
        ?int $maxWidth,
        ?int $maxHeight,
        bool $stripMeta
    ): bool {
        $im = new \Imagick($source);

        // Strip metadata if configured
        if ($stripMeta) {
            $im->stripImage();
        }

        // Get transparency
        if ($im->getImageAlphaChannel()) {
            $im->setImageBackgroundColor(new \ImagickPixel('transparent'));
        }

        // Resize if needed
        $w = $im->getImageWidth();
        $h = $im->getImageHeight();
        if (($maxWidth && $w > $maxWidth) || ($maxHeight && $h > $maxHeight)) {
            $im->resizeImage($maxWidth ?: $w, $maxHeight ?: $h, \Imagick::FILTER_LANCZOS, 1, true);
        }

        // Convert format to WebP
        $im->setImageFormat('webp');
        $im->setImageCompressionQuality($quality);

        $result = $im->writeImage($dest);
        $im->clear();
        $im->destroy();

        return $result;
    }

    private function convertWithGD(
        string $source,
        string $dest,
        int $quality,
        ?int $maxWidth,
        ?int $maxHeight
    ): bool {
        // Load image resource
        $info = @getimagesize($source);
        if (!$info) {
            return false;
        }

        $type = $info[2];
        switch ($type) {
            case IMAGETYPE_JPEG:
                $im = @imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $im = @imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $im = @imagecreatefromgif($source);
                break;
            case IMAGETYPE_BMP:
                $im = @imagecreatefrombmp($source);
                break;
            default:
                return false;
        }

        if (!$im) {
            return false;
        }

        // Maintain transparency
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($im, false);
            imagesavealpha($im, true);
        }

        // Resize if needed
        $w = imagesx($im);
        $h = imagesy($im);
        if (($maxWidth && $w > $maxWidth) || ($maxHeight && $h > $maxHeight)) {
            $newW = $maxWidth ?: $w;
            $newH = $maxHeight ?: $h;
            
            // Calculate scale proportions
            $ratio = min($newW / $w, $newH / $h);
            $scaledW = (int) round($w * $ratio);
            $scaledH = (int) round($h * $ratio);

            $resized = imagecreatetruecolor($scaledW, $scaledH);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);

            imagecopyresampled($resized, $im, 0, 0, 0, 0, $scaledW, $scaledH, $w, $h);
            imagedestroy($im);
            $im = $resized;
        }

        $result = imagewebp($im, $dest, $quality);
        imagedestroy($im);

        return $result;
    }

    private function convertWithCLI(
        string $source,
        string $dest,
        int $quality,
        ?int $maxWidth,
        ?int $maxHeight,
        bool $stripMeta
    ): bool {
        $bin = 'magick';
        
        // Test convert or magick
        $output = [];
        $returnVar = 0;
        @exec('magick -version', $output, $returnVar);
        if ($returnVar !== 0) {
            @exec('convert -version', $output, $returnVar);
            if ($returnVar === 0) {
                $bin = 'convert';
            }
        }

        // Build command parameters
        $args = [];
        $args[] = escapeshellarg($source);

        if ($stripMeta) {
            $args[] = '-strip';
        }

        if ($maxWidth || $maxHeight) {
            $resizeArg = '';
            if ($maxWidth && $maxHeight) {
                $resizeArg = "{$maxWidth}x{$maxHeight}";
            } elseif ($maxWidth) {
                $resizeArg = "{$maxWidth}x";
            } else {
                $resizeArg = "x{$maxHeight}";
            }
            $args[] = '-resize ' . escapeshellarg($resizeArg . '>');
        }

        $args[] = '-quality ' . (int)$quality;
        $args[] = 'webp:' . escapeshellarg($dest);

        $cmd = $bin . ' ' . implode(' ', $args);
        
        $cliOutput = [];
        $cliReturn = 0;
        @exec($cmd, $cliOutput, $cliReturn);

        return $cliReturn === 0 && file_exists($dest);
    }
}
