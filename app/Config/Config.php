<?php

declare(strict_types=1);

namespace Config;

class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return ($value !== false) ? $value : $default;
    }

    public static function appUrl(): string
    {
        return rtrim(self::get('APP_URL', 'http://localhost'), '/');
    }

    public static function appName(): string
    {
        return self::get('APP_NAME', 'Galgospedia');
    }

    public static function isDebug(): bool
    {
        return self::get('APP_DEBUG', 'false') === 'true';
    }

    public static function uploadMaxSize(): int
    {
        return (int) self::get('UPLOAD_MAX_SIZE', 10485760);
    }

    public static function uploadDir(): string
    {
        // Absolute path to the uploads directory inside public/
        return PUB_PATH . '/' . self::get('UPLOAD_DIR', 'uploads/dogs');
    }

    public static function uploadUrl(): string
    {
        return self::appUrl() . '/' . self::get('UPLOAD_DIR', 'uploads/dogs');
    }

    // ── Cloudflare R2 ─────────────────────────────────────────────

    /** True when R2 credentials are present in the environment. */
    public static function r2Enabled(): bool
    {
        return self::get('R2_ACCESS_KEY', '') !== '';
    }

    public static function r2AccountId(): string
    {
        return self::get('R2_ACCOUNT_ID', '');
    }

    public static function r2AccessKey(): string
    {
        return self::get('R2_ACCESS_KEY', '');
    }

    public static function r2SecretKey(): string
    {
        return self::get('R2_SECRET_KEY', '');
    }

    public static function r2Bucket(): string
    {
        return self::get('R2_BUCKET', 'galgospedia');
    }

    /** Public CDN/domain URL for the bucket (no trailing slash). */
    public static function r2PublicUrl(): string
    {
        return rtrim(self::get('R2_PUBLIC_URL', ''), '/');
    }

    /** S3-compatible endpoint URL. */
    public static function r2Endpoint(): string
    {
        return 'https://' . self::r2AccountId() . '.r2.cloudflarestorage.com';
    }

    // ── Google Analytics ──────────────────────────────────────────

    /** Returns the GA4 Measurement ID (e.g. G-XXXXXXXXXX), or empty string if not set. */
    public static function gaId(): string
    {
        return self::get('GA_MEASUREMENT_ID', '');
    }
}
