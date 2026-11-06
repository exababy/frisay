<?php

namespace ImageOptimizer\Repository;

use DB;

class QueueRepository
{
    public static function add(int $imageId, int $priority = 0): bool
    {
        $exists = DB::getValue("SELECT 1 FROM `image_opt_queue` WHERE `image_id` = ?", [$imageId]);
        if ($exists) {
            return true;
        }
        return DB::insert('image_opt_queue', [
            'image_id' => $imageId,
            'priority' => $priority,
            'locked' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]) !== false;
    }

    public static function popBatch(int $limit): array
    {
        // Get unlocked queue items ordered by priority desc and id asc
        return DB::execute("SELECT * FROM `image_opt_queue` WHERE locked = 0 ORDER BY priority DESC, id ASC LIMIT {$limit}") ?: [];
    }

    public static function lockJobs(array $ids): bool
    {
        if ($ids === []) return true;
        $in = implode(',', array_map('intval', $ids));
        return DB::execute("UPDATE `image_opt_queue` SET locked = 1 WHERE id IN ({$in})") !== false;
    }

    public static function releaseJobs(array $ids): bool
    {
        if ($ids === []) return true;
        $in = implode(',', array_map('intval', $ids));
        return DB::execute("UPDATE `image_opt_queue` SET locked = 0 WHERE id IN ({$in})") !== false;
    }

    public static function deleteJobs(array $ids): bool
    {
        if ($ids === []) return true;
        $in = implode(',', array_map('intval', $ids));
        return DB::execute("DELETE FROM `image_opt_queue` WHERE id IN ({$in})") !== false;
    }

    public static function deleteByImageId(int $imageId): bool
    {
        return DB::execute("DELETE FROM `image_opt_queue` WHERE `image_id` = ?", [$imageId]) !== false;
    }

    public static function clear(): bool
    {
        return DB::execute("TRUNCATE TABLE `image_opt_queue`") !== false;
    }

    public static function countPending(): int
    {
        return (int) DB::getValue("SELECT COUNT(*) FROM `image_opt_queue` WHERE locked = 0");
    }
}
