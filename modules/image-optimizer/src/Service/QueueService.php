<?php

namespace ImageOptimizer\Service;

use ImageOptimizer\Core\Settings;
use ImageOptimizer\Repository\ImageRepository;
use ImageOptimizer\Repository\QueueRepository;
use ImageOptimizer\Repository\LogRepository;
use DB;

class QueueService
{
    private OptimizerService $optimizerService;

    public function __construct()
    {
        $this->optimizerService = new OptimizerService();
    }

    public function isPaused(): bool
    {
        return Settings::get('queue_paused', 'no') === 'yes';
    }

    public function pause(): void
    {
        Settings::set('queue_paused', 'yes');
        LogRepository::log(null, 'info', 'QUEUE', 'Kuyruk duraklatıldı.');
    }

    public function resume(): void
    {
        Settings::set('queue_paused', 'no');
        LogRepository::log(null, 'info', 'QUEUE', 'Kuyruk devam ettirildi.');
    }

    public function processBatch(?int $customBatchSize = null): array
    {
        $result = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'processed' => 0,
            'remaining' => 0,
            'memory_used' => 0
        ];

        if ($this->isPaused()) {
            return $result;
        }

        // Determine batch size with memory monitoring
        $batchSize = $customBatchSize ?: (int) Settings::get('batch_size', 50);
        $batchSize = $this->adjustBatchSizeForMemory($batchSize);

        $jobs = QueueRepository::popBatch($batchSize);
        if ($jobs === []) {
            $result['remaining'] = QueueRepository::countPending();
            return $result;
        }

        // Lock jobs
        $jobIds = array_column($jobs, 'id');
        QueueRepository::lockJobs($jobIds);

        foreach ($jobs as $job) {
            $imageId = (int) $job['image_id'];
            
            // Check memory limit before processing each image
            if ($this->isMemoryCritical()) {
                LogRepository::log(null, 'warning', 'QUEUE', 'Kritik bellek limiti nedeniyle yığın işlemi erken kesildi.');
                break;
            }

            $img = ImageRepository::findById($imageId);
            if ($img) {
                $statusBefore = $img['status'];
                $sizeBefore = $img['original_size'];

                // Attempt conversion
                $success = $this->optimizerService->convert($imageId);

                // Reload image state
                $imgAfter = ImageRepository::findById($imageId);
                $statusAfter = $imgAfter ? $imgAfter['status'] : 'failed';
                $sizeAfter = ($imgAfter && $imgAfter['optimized_size'] !== null) ? $imgAfter['optimized_size'] : $sizeBefore;

                if ($statusAfter === 'converted') {
                    $result['success']++;
                    $savedBytes = max(0, $sizeBefore - $sizeAfter);
                    $this->updateDailyStats($savedBytes, 1, 0, 0);
                } elseif ($statusAfter === 'skipped') {
                    $result['skipped']++;
                    $this->updateDailyStats(0, 0, 0, 1);
                } else {
                    $result['failed']++;
                    $this->updateDailyStats(0, 0, 1, 0);
                }
            } else {
                // Delete invalid job from queue
                QueueRepository::deleteByImageId($imageId);
            }

            $result['processed']++;
        }

        // Delete processed jobs
        QueueRepository::deleteJobs($jobIds);

        $result['remaining'] = QueueRepository::countPending();
        $result['memory_used'] = memory_get_peak_usage(true);

        return $result;
    }

    public function retryFailed(): int
    {
        $failedImages = DB::execute("SELECT id FROM `image_opt_images` WHERE status = 'failed'") ?: [];
        $count = 0;
        foreach ($failedImages as $img) {
            $imageId = (int) $img['id'];
            // Reset status in optimized_images
            DB::update('image_opt_images', ['status' => 'pending', 'error_message' => null], 'id = :id', ['id' => $imageId]);
            // Add back to queue
            QueueRepository::add($imageId);
            $count++;
        }

        if ($count > 0) {
            LogRepository::log(null, 'info', 'QUEUE', "Başarısız olan {$count} görsel tekrar kuyruğa eklendi.");
        }

        return $count;
    }

    private function adjustBatchSizeForMemory(int $batchSize): int
    {
        $limit = $this->getMemoryLimit();
        if ($limit <= 0) return $batchSize; // No limit or unable to read

        $current = memory_get_usage(true);
        $usagePercentage = ($current / $limit) * 100;

        if ($usagePercentage > 85) {
            return max(5, (int) round($batchSize * 0.25)); // High critical usage, run 25% of batch
        } elseif ($usagePercentage > 70) {
            return max(10, (int) round($batchSize * 0.5)); // Warning usage, run 50% of batch
        }

        return $batchSize;
    }

    private function isMemoryCritical(): bool
    {
        $limit = $this->getMemoryLimit();
        if ($limit <= 0) return false;

        $current = memory_get_usage(true);
        $usagePercentage = ($current / $limit) * 100;

        return $usagePercentage > 90; // Critical threshold
    }

    private function getMemoryLimit(): float
    {
        $limit = ini_get('memory_limit');
        if (!$limit || $limit === '-1') {
            return 0;
        }

        $value = (float) $limit;
        $unit = strtolower(substr($limit, -1));
        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }

    private function updateDailyStats(int $savedBytes, int $converted, int $failed, int $skipped): void
    {
        $today = date('Y-m-d');
        $exists = DB::getValue("SELECT 1 FROM `image_opt_statistics` WHERE `date` = ?", [$today]);

        if ($exists) {
            DB::execute(
                "UPDATE `image_opt_statistics` SET 
                    saved_bytes = saved_bytes + ?, 
                    converted_count = converted_count + ?, 
                    failed_count = failed_count + ?, 
                    skipped_count = skipped_count + ?
                WHERE `date` = ?",
                [$savedBytes, $converted, $failed, $skipped, $today]
            );
        } else {
            DB::insert('image_opt_statistics', [
                'date' => $today,
                'saved_bytes' => $savedBytes,
                'converted_count' => $converted,
                'failed_count' => $failed,
                'skipped_count' => $skipped
            ]);
        }
    }
}
