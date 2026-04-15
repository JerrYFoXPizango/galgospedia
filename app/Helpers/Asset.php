<?php

declare(strict_types=1);

namespace Helpers;

use Config\Config;

/**
 * Asset URL helper.
 *
 * Handles three storage modes transparently:
 *   r2:{key}      → R2 public bucket URL  (dog photos, avatars, logos)
 *   https://…     → already a full URL, returned as-is
 *   relative/path → local /public, prepend leading slash
 */
class Asset
{
    /**
     * Returns an HTML-safe URL for use in src= / href= attributes.
     */
    public static function url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        // R2 key stored with r2: prefix → public CDN URL
        if (str_starts_with($path, 'r2:')) {
            $key = substr($path, 3);
            return htmlspecialchars(
                rtrim(Config::r2PublicUrl(), '/') . '/' . $key,
                ENT_QUOTES,
                'UTF-8'
            );
        }

        // Full URL already (e.g. migrated legacy R2 URL)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
        }

        // Local relative path → prepend /
        return '/' . htmlspecialchars(ltrim($path, '/'), ENT_QUOTES, 'UTF-8');
    }
}
