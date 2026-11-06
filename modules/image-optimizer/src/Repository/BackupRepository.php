<?php

namespace ImageOptimizer\Repository;

use DB;

class BackupRepository
{
    public static function add(int $imageId, string $backupPath): bool
    {
        $exists = DB::getValue("SELECT 1 FROM `image_opt_backups` WHERE `image_id` = ?", [$imageId]);
        if ($exists) {
            return DB::update('image_opt_backups', ['backup_path' => $backupPath], 'image_id = :img_id', ['img_id' => $imageId]) !== false;
        } else {
            return DB::insert('image_opt_backups', [
                'image_id' => $imageId,
                'backup_path' => $backupPath,
                'created_at' => date('Y-m-d H:i:s')
            ]) !== false;
        }
    }

    public static function findByImageId(int $imageId): ?array
    {
        $row = DB::getRowSafe('image_opt_backups', 'image_id = ?', [$imageId]);
        return $row ?: null;
    }

    public static function remove(int $imageId): bool
    {
        return DB::execute("DELETE FROM `image_opt_backups` WHERE `image_id` = ?", [$imageId]) !== false;
    }

    public static function getAll(): array
    {
        return DB::execute("SELECT * FROM `image_opt_backups`") ?: [];
    }

    public static function clearAll(): bool
    {
        return DB::execute("TRUNCATE TABLE `image_opt_backups`") !== false;
    }
}
