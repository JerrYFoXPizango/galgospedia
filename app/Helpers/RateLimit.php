<?php

declare(strict_types=1);

namespace Helpers;

/**
 * Simple file-based rate limiter (per IP / per key).
 * Stored in sys_get_temp_dir() — no DB or APCu dependency.
 */
class RateLimit
{
    private static string $dir = '';

    private static function dir(): string
    {
        if (self::$dir === '') {
            self::$dir = sys_get_temp_dir() . '/galgospedia_rl/';
            if (!is_dir(self::$dir)) {
                mkdir(self::$dir, 0700, true);
            }
        }
        return self::$dir;
    }

    private static function load(string $key): array
    {
        $file = self::dir() . md5($key) . '.json';
        if (!file_exists($file)) {
            return ['attempts' => 0, 'reset_at' => 0];
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : ['attempts' => 0, 'reset_at' => 0];
    }

    private static function save(string $key, array $data): void
    {
        $file = self::dir() . md5($key) . '.json';
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /** Returns true if the key has exceeded $maxAttempts within $decaySeconds. */
    public static function tooMany(string $key, int $maxAttempts = 5, int $decaySeconds = 900): bool
    {
        $data = self::load($key);
        if (time() > $data['reset_at']) {
            return false; // window expired
        }
        return $data['attempts'] >= $maxAttempts;
    }

    /** Record one attempt. Starts (or resets) the time window on first hit. */
    public static function hit(string $key, int $decaySeconds = 900): void
    {
        $data = self::load($key);
        if (time() > $data['reset_at']) {
            $data = ['attempts' => 0, 'reset_at' => time() + $decaySeconds];
        }
        $data['attempts']++;
        self::save($key, $data);
    }

    /** Clear attempts on successful authentication. */
    public static function clear(string $key): void
    {
        $file = self::dir() . md5($key) . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
