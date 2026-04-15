<?php

declare(strict_types=1);

namespace Services;

use Config\Config;

class ImageProcessor
{
    private string $uploadDir;
    private int    $maxFileSize;

    // Full image: max dimensions (proportional, no crop)
    private const FULL_MAX_W   = 1200;
    private const FULL_MAX_H   = 900;
    private const FULL_QUALITY = 82;

    // Thumbnail: proportional resize for listing cards
    private const THUMB_MAX_W   = 600;
    private const THUMB_MAX_H   = 450;
    private const THUMB_QUALITY = 72;

    private const ALLOWED_MIME = [
        'image/jpeg', 'image/jpg', 'image/png',
        'image/webp', 'image/gif',
    ];

    public function __construct()
    {
        $this->uploadDir   = Config::uploadDir();
        $this->maxFileSize = Config::uploadMaxSize();

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Process an uploaded dog photo.
     * Produces:
     *   _full.webp  — proportionally resized, max 1200×900
     *   _thumb.webp — square center-crop for cards (400×400)
     *
     * @param array $file $_FILES['photo']
     * @return array{webp: string, thumb: string}  Relative paths from public/
     */
    public function process(array $file): array
    {
        $this->validate($file);

        $tmpPath  = $file['tmp_name'];
        $mime     = $this->detectMime($tmpPath);
        $src      = $this->createGdImage($tmpPath, $mime);
        $baseName = sha1(uniqid('galgo_', true)) . '_' . time();

        if (R2Storage::enabled()) {
            // Save to system temp, upload to R2, delete temp files
            $tmpDir    = sys_get_temp_dir();
            $fullFile  = $tmpDir . '/' . $baseName . '_full.webp';
            $thumbFile = $tmpDir . '/' . $baseName . '_thumb.webp';

            $this->saveResized($src, $fullFile,  self::FULL_MAX_W,  self::FULL_MAX_H,  self::FULL_QUALITY);
            $this->saveResized($src, $thumbFile, self::THUMB_MAX_W, self::THUMB_MAX_H, self::THUMB_QUALITY);
            imagedestroy($src);

            $fullR2  = R2Storage::putPublic('dogs/' . $baseName . '_full.webp',  $fullFile,  'image/webp');
            $thumbR2 = R2Storage::putPublic('dogs/' . $baseName . '_thumb.webp', $thumbFile, 'image/webp');

            @unlink($fullFile);
            @unlink($thumbFile);

            return ['webp' => $fullR2, 'thumb' => $thumbR2];
        }

        // ── Local storage (default) ──────────────────────────────
        $uploadKey = Config::get('UPLOAD_DIR', 'uploads/dogs');
        $fullFile  = $this->uploadDir . '/' . $baseName . '_full.webp';
        $fullRel   = $uploadKey . '/' . $baseName . '_full.webp';
        $this->saveResized($src, $fullFile, self::FULL_MAX_W, self::FULL_MAX_H, self::FULL_QUALITY);

        $thumbFile = $this->uploadDir . '/' . $baseName . '_thumb.webp';
        $thumbRel  = $uploadKey . '/' . $baseName . '_thumb.webp';
        $this->saveResized($src, $thumbFile, self::THUMB_MAX_W, self::THUMB_MAX_H, self::THUMB_QUALITY);

        imagedestroy($src);

        return ['webp' => $fullRel, 'thumb' => $thumbRel];
    }

    /**
     * Process a club logo upload — proportional resize to max 300×120, no crop.
     * Returns relative path from public/.
     */
    public function processClubLogo(array $file): string
    {
        $this->validate($file);

        $src      = $this->createGdImage($file['tmp_name'], $this->detectMime($file['tmp_name']));
        $baseName = sha1(uniqid('club_', true)) . '_' . time();

        $srcW  = imagesx($src);
        $srcH  = imagesy($src);
        $ratio = min(300 / $srcW, 120 / $srcH, 1.0);
        $dstW  = (int) round($srcW * $ratio);
        $dstH  = (int) round($srcH * $ratio);
        $dst   = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        if (R2Storage::enabled()) {
            $tmpFile = sys_get_temp_dir() . '/' . $baseName . '.webp';
            imagewebp($dst, $tmpFile, 90);
            imagedestroy($dst);
            imagedestroy($src);
            $r2Key = R2Storage::putPublic('club-logos/' . $baseName . '.webp', $tmpFile, 'image/webp');
            @unlink($tmpFile);
            return $r2Key;
        }

        $logoDir  = PUB_PATH . '/uploads/club-logos';
        if (!is_dir($logoDir)) {
            mkdir($logoDir, 0755, true);
        }
        $filePath = $logoDir . '/' . $baseName . '.webp';
        imagewebp($dst, $filePath, 90);
        imagedestroy($dst);
        imagedestroy($src);

        return 'uploads/club-logos/' . $baseName . '.webp';
    }

    /**
     * Process a user avatar upload — square center-crop at 200×200.
     */
    public function processAvatar(array $file): string
    {
        $this->validate($file);

        $src      = $this->createGdImage($file['tmp_name'], $this->detectMime($file['tmp_name']));
        $baseName = sha1(uniqid('avatar_', true)) . '_' . time();

        if (R2Storage::enabled()) {
            $tmpFile = sys_get_temp_dir() . '/' . $baseName . '.webp';
            $this->saveCroppedSquare($src, $tmpFile, 200, 80);
            imagedestroy($src);
            $r2Key = R2Storage::putPublic('avatars/' . $baseName . '.webp', $tmpFile, 'image/webp');
            @unlink($tmpFile);
            return $r2Key;
        }

        $avatarDir = dirname($this->uploadDir) . '/avatars';
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }
        $filePath = $avatarDir . '/' . $baseName . '.webp';
        $this->saveCroppedSquare($src, $filePath, 200, 80);
        imagedestroy($src);

        return 'uploads/avatars/' . $baseName . '.webp';
    }

    /**
     * Process a tournament poster upload — proportional resize, max 1200×1600.
     * Returns the path/R2 key of the stored WebP.
     */
    public function processTournamentPoster(array $file): string
    {
        $this->validate($file);

        $src      = $this->createGdImage($file['tmp_name'], $this->detectMime($file['tmp_name']));
        $baseName = sha1(uniqid('poster_', true)) . '_' . time();

        if (R2Storage::enabled()) {
            $tmpFile = sys_get_temp_dir() . '/' . $baseName . '.webp';
            $this->saveResized($src, $tmpFile, 1200, 1600, 85);
            imagedestroy($src);
            $r2Key = R2Storage::putPublic('tournaments/' . $baseName . '.webp', $tmpFile, 'image/webp');
            @unlink($tmpFile);
            return $r2Key;
        }

        $dir = PUB_PATH . '/uploads/tournaments';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filePath = $dir . '/' . $baseName . '.webp';
        $this->saveResized($src, $filePath, 1200, 1600, 85);
        imagedestroy($src);

        return 'uploads/tournaments/' . $baseName . '.webp';
    }

    /** Delete a single image file (local or R2). */
    public function deleteSingle(string $path): void
    {
        if (!$path) return;
        if (str_starts_with($path, 'r2:')) {
            R2Storage::delete($path);
        } else {
            $abs = PUB_PATH . '/' . ltrim($path, '/');
            if (file_exists($abs)) unlink($abs);
        }
    }

    /** Delete image files associated with a dog (local or R2). */
    public function delete(string $webpRel, string $thumbRel): void
    {
        foreach ([$webpRel, $thumbRel] as $rel) {
            if (!$rel) {
                continue;
            }
            if (str_starts_with($rel, 'r2:')) {
                R2Storage::delete($rel);
            } else {
                $abs = PUB_PATH . '/' . ltrim($rel, '/');
                if (file_exists($abs)) {
                    unlink($abs);
                }
            }
        }
    }

    // ── Private helpers ──────────────────────────────────────────

    private function validate(array $file): void
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir el archivo. Código: ' . ($file['error'] ?? 'desconocido'));
        }
        if ($file['size'] > $this->maxFileSize) {
            $mb = round($this->maxFileSize / 1024 / 1024, 1);
            throw new \RuntimeException("El archivo es demasiado grande. Máximo permitido: {$mb} MB.");
        }
        $mime = $this->detectMime($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new \RuntimeException('Tipo de archivo no permitido. Solo se aceptan: JPEG, PNG, WebP, GIF.');
        }
    }

    private function detectMime(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($path) ?: '';
    }

    private function createGdImage(string $path, string $mime): \GdImage
    {
        $img = match ($mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png'               => imagecreatefrompng($path),
            'image/webp'              => imagecreatefromwebp($path),
            'image/gif'               => imagecreatefromgif($path),
            default                   => throw new \RuntimeException('Formato de imagen no soportado.'),
        };

        if (!$img) {
            throw new \RuntimeException('No se pudo procesar la imagen.');
        }

        // Auto-rotate based on EXIF orientation (JPEG only)
        if (in_array($mime, ['image/jpeg', 'image/jpg'], true) && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            if ($exif && isset($exif['Orientation'])) {
                $img = $this->autoRotate($img, (int) $exif['Orientation']);
            }
        }

        return $img;
    }

    private function autoRotate(\GdImage $img, int $orientation): \GdImage
    {
        return match ($orientation) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
    }

    /**
     * Proportional resize: shrink to fit within $maxW × $maxH. Never upscales.
     */
    private function saveResized(\GdImage $src, string $destPath, int $maxW, int $maxH, int $quality): void
    {
        $srcW  = imagesx($src);
        $srcH  = imagesy($src);
        $ratio = min($maxW / $srcW, $maxH / $srcH, 1.0);
        $dstW  = (int) round($srcW * $ratio);
        $dstH  = (int) round($srcH * $ratio);

        $dst = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagewebp($dst, $destPath, $quality);
        imagedestroy($dst);
    }

    /**
     * Center-crop to a square of $size × $size, save as WebP.
     */
    private function saveCroppedSquare(\GdImage $src, string $destPath, int $size, int $quality): void
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);

        $cropSize = min($srcW, $srcH);
        $srcX     = (int) (($srcW - $cropSize) / 2);
        $srcY     = (int) (($srcH - $cropSize) / 2);

        $dst = imagecreatetruecolor($size, $size);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $cropSize, $cropSize);
        imagewebp($dst, $destPath, $quality);
        imagedestroy($dst);
    }
}
