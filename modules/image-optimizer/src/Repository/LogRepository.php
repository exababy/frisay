<?php

namespace ImageOptimizer\Repository;

use DB;

class LogRepository
{
    public static function log(?int $imageId, string $level, string $action, string $message): bool
    {
        return DB::insert('image_opt_logs', [
            'image_id' => $imageId,
            'level' => $level,
            'action' => $action,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]) !== false;
    }

    public static function getLogs(int $limit = 50, int $offset = 0, string $search = '', string $level = ''): array
    {
        $params = [];
        $where = [];

        if ($search !== '') {
            $where[] = "message LIKE :search OR action LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        if ($level !== '') {
            $where[] = "level = :level";
            $params['level'] = $level;
        }

        $whereClause = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT * FROM `image_opt_logs` {$whereClause} ORDER BY id DESC LIMIT {$offset}, {$limit}";
        return DB::execute($sql, $params) ?: [];
    }

    public static function countLogs(string $search = '', string $level = ''): int
    {
        $params = [];
        $where = [];

        if ($search !== '') {
            $where[] = "message LIKE :search OR action LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        if ($level !== '') {
            $where[] = "level = :level";
            $params['level'] = $level;
        }

        $whereClause = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) FROM `image_opt_logs` {$whereClause}";
        return (int) DB::getValue($sql, $params);
    }

    public static function clearLogs(): bool
    {
        return DB::execute("TRUNCATE TABLE `image_opt_logs`") !== false;
    }
}
