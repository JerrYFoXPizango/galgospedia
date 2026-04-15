<?php

declare(strict_types=1);

namespace Services;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Config\Config;

/**
 * Cloudflare R2 storage — thin wrapper around the S3-compatible API.
 *
 * Public objects  (dog photos, avatars, club logos):
 *   putPublic()  → upload, returns full public URL
 *   Bucket prefix: dogs/, avatars/, club-logos/
 *
 * Private objects (wallet docs, club docs):
 *   putPrivate() → upload, returns r2:{key} string for DB storage
 *   presignedUrl() → time-limited access URL (default 1 h)
 *   Bucket prefix: private/wallet/, private/club-docs/
 *
 * delete() handles both prefixes.
 */
class R2Storage
{
    private static ?S3Client $client = null;

    // ── Client ───────────────────────────────────────────────────

    private static function client(): S3Client
    {
        if (self::$client === null) {
            self::$client = new S3Client([
                'version'                 => 'latest',
                'region'                  => 'auto',
                'endpoint'                => Config::r2Endpoint(),
                'use_path_style_endpoint' => true,
                'credentials'             => [
                    'key'    => Config::r2AccessKey(),
                    'secret' => Config::r2SecretKey(),
                ],
            ]);
        }
        return self::$client;
    }

    // ── Public objects ────────────────────────────────────────────

    /**
     * Upload to public prefix. Returns full public URL (store in DB as-is).
     *
     * @param  string $key       e.g. "dogs/abc123_full.webp"
     * @param  string $localPath Absolute path to source file
     * @param  string $mime      MIME type
     * @return string            Full public URL
     */
    public static function putPublic(string $key, string $localPath, string $mime): string
    {
        self::client()->putObject([
            'Bucket'      => Config::r2Bucket(),
            'Key'         => $key,
            'SourceFile'  => $localPath,
            'ContentType' => $mime,
        ]);
        return 'r2:' . $key;
    }

    // ── Private objects ───────────────────────────────────────────

    /**
     * Upload to private prefix. Returns "r2:{key}" for DB storage.
     *
     * @param  string $key       e.g. "private/wallet/42/abc123.pdf"
     * @param  string $localPath Absolute path to source file (tmp or permanent)
     * @param  string $mime      MIME type
     * @return string            "r2:{key}" — store this in file_path column
     */
    public static function putPrivate(string $key, string $localPath, string $mime): string
    {
        self::client()->putObject([
            'Bucket'      => Config::r2Bucket(),
            'Key'         => $key,
            'SourceFile'  => $localPath,
            'ContentType' => $mime,
        ]);
        return 'r2:' . $key;
    }

    /**
     * Generate a presigned URL for a private object.
     *
     * @param  string $key        R2 object key (without "r2:" prefix)
     * @param  int    $ttlSeconds TTL in seconds (default 1 hour)
     * @return string             Time-limited HTTPS URL
     */
    public static function presignedUrl(string $key, int $ttlSeconds = 3600): string
    {
        $cmd = self::client()->getCommand('GetObject', [
            'Bucket' => Config::r2Bucket(),
            'Key'    => $key,
        ]);
        $request = self::client()->createPresignedRequest($cmd, '+' . $ttlSeconds . ' seconds');
        return (string) $request->getUri();
    }

    // ── Delete ────────────────────────────────────────────────────

    /**
     * Delete an object. Accepts either a raw key or a "r2:{key}" string.
     * Silently ignores missing objects.
     */
    public static function delete(string $keyOrPrefixed): void
    {
        $key = str_starts_with($keyOrPrefixed, 'r2:')
            ? substr($keyOrPrefixed, 3)
            : $keyOrPrefixed;

        try {
            self::client()->deleteObject([
                'Bucket' => Config::r2Bucket(),
                'Key'    => $key,
            ]);
        } catch (S3Exception $e) {
            error_log('R2Storage::delete failed for key=' . $key . ': ' . $e->getMessage());
        }
    }

    // ── Feature flag ─────────────────────────────────────────────

    public static function enabled(): bool
    {
        return Config::r2Enabled();
    }
}
