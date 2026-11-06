<?php

namespace ImageOptimizer\Repository;

use DB;

class ImageRepository
{
    public static function save(array $data): int|bool
    {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($id > 0) {
            unset($data['id']);
            $res = DB::update('image_opt_images', $data, 'id = :id_where', ['id_where' => $id]);
            return $res !== false ? $id : false;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return DB::insert('image_opt_images', $data);
        }
    }

    public static function findById(int $id): ?array
    {
        $row = DB::getRowSafe('image_opt_images', 'id = ?', [$id]);
        return $row ?: null;
    }

    public static function findByPath(string $filePath): ?array
    {
        $row = DB::getRowSafe('image_opt_images', 'file_path = ?', [$filePath]);
        return $row ?: null;
    }

    public static function findByHash(string $hash): ?array
    {
        $row = DB::getRowSafe('image_opt_images', 'hash = ?', [$hash]);
        return $row ?: null;
    }

    public static function getAll(int $limit = 50, int $offset = 0, string $search = '', string $status = '', string $sort = 'id DESC'): array
    {
        $params = [];
        $where = [];

        if ($search !== '') {
            $where[] = "file_path LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = "status = :status";
            $params['status'] = $status;
        }

        $whereClause = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT * FROM `image_opt_images` {$whereClause} ORDER BY {$sort} LIMIT {$offset}, {$limit}";
        return DB::execute($sql, $params) ?: [];
    }

    public static function countAll(string $search = '', string $status = ''): int
    {
        $params = [];
        $where = [];

        if ($search !== '') {
            $where[] = "file_path LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = "status = :status";
            $params['status'] = $status;
        }

        $whereClause = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) FROM `image_opt_images` {$whereClause}";
        return (int) DB::getValue($sql, $params);
    }

    public static function getStats(): array
    {
        $total = (int) DB::getValue("SELECT COUNT(*) FROM `image_opt_images`") ?: 0;
        $converted = (int) DB::getValue("SELECT COUNT(*) FROM `image_opt_images` WHERE status = 'converted'") ?: 0;
        $failed = (int) DB::getValue("SELECT COUNT(*) FROM `image_opt_images` WHERE status = 'failed'") ?: 0;
        $skipped = (int) DB::getValue("SELECT COUNT(*) FROM `image_opt_images` WHERE status = 'skipped'") ?: 0;
        $pending = (int) DB::getValue("SELECT COUNT(*) FROM `image_opt_images` WHERE status = 'pending'") ?: 0;

        $diskSaved = (int) DB::getValue("SELECT SUM(original_size - optimized_size) FROM `image_opt_images` WHERE status = 'converted' AND optimized_size IS NOT NULL") ?: 0;
        $totalOriginal = (int) DB::getValue("SELECT SUM(original_size) FROM `image_opt_images`") ?: 0;
        $totalOptimized = (int) DB::getValue("SELECT SUM(CASE WHEN status = 'converted' THEN optimized_size ELSE original_size END) FROM `image_opt_images`") ?: 0;

        $averageCompression = 0;
        if ($totalOriginal > 0) {
            $averageCompression = round((($totalOriginal - $totalOptimized) / $totalOriginal) * 100, 1);
        }

        $largest = DB::execute("SELECT file_path, original_size FROM `image_opt_images` ORDER BY original_size DESC LIMIT 1") ?: [];
        $smallest = DB::execute("SELECT file_path, original_size FROM `image_opt_images` WHERE original_size > 0 ORDER BY original_size ASC LIMIT 1") ?: [];

        $todaySavings = (int) DB::getValue("SELECT SUM(original_size - optimized_size) FROM `image_opt_images` WHERE status = 'converted' AND DATE(updated_at) = CURDATE()") ?: 0;

        return [
            'total' => $total,
            'converted' => $converted,
            'failed' => $failed,
            'skipped' => $skipped,
            'pending' => $pending,
            'disk_saved' => $diskSaved,
            'today_savings' => $todaySavings,
            'average_compression' => $averageCompression,
            'largest' => $largest !== [] ? $largest[0] : null,
            'smallest' => $smallest !== [] ? $smallest[0] : null,
        ];
    }
}
