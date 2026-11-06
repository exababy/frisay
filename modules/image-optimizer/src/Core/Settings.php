<?php

namespace ImageOptimizer\Core;

use DB;

class Settings
{
    private static ?array $cached = null;

    public static function getAll(): array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $rows = DB::execute("SELECT `setting_key`, `setting_value` FROM `image_opt_settings`") ?: [];
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        self::$cached = $settings;
        return $settings;
    }

    public static function get(string $key, $default = '')
    {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, $value): bool
    {
        if (is_array($value)) {
            $value = json_encode($value);
        } else {
            $value = (string) $value;
        }

        $exists = DB::getValue("SELECT 1 FROM `image_opt_settings` WHERE `setting_key` = ?", [$key]);
        if ($exists) {
            $res = DB::update('image_opt_settings', ['setting_value' => $value], '`setting_key` = :key_where', ['key_where' => $key]);
        } else {
            $res = DB::insert('image_opt_settings', ['setting_key' => $key, 'setting_value' => $value]);
        }

        self::$cached = null; // Clear cache
        return $res !== false;
    }

    public static function saveAll(array $data): void
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }
}
