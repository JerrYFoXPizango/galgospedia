<?php

declare(strict_types=1);

namespace Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;

class TarjetaGenerator
{
    // Brand colors
    private const RED   = [191,  10,  48];   // Spanish red
    private const DARK  = [110,   5,  20];   // Darker footer
    private const GOLD  = [212, 175,  55];   // Gold
    private const WHITE = [255, 255, 255];
    private const CREAM = [255, 235, 190];

    private string $fontBold;
    private string $fontReg;

    public function __construct()
    {
        $this->fontBold = PUB_PATH . '/fonts/Montserrat-Bold.ttf';
        $this->fontReg  = PUB_PATH . '/fonts/Montserrat-Regular.ttf';
    }

    public function generate(array $dog, string $layout, string $tipo): string
    {
        [$w, $h] = match($layout) {
            'story'    => [1080, 1920],
            'cuadrado' => [1080, 1080],
            default    => [1200,  630],
        };

        $img = imagecreatetruecolor($w, $h);

        match($layout) {
            'horizontal' => $this->buildHorizontal($img, $dog, $w, $h),
            'cuadrado'   => $this->buildSquare($img, $dog, $w, $h),
            default      => $this->buildStory($img, $dog, $w, $h),
        };

        return $this->output($img, $tipo);
    }

    // ── Layouts ──────────────────────────────────────────────────────────────

    private function buildHorizontal(\GdImage $img, array $dog, int $w, int $h): void
    {
        // Background: solid red left, dark red right
        $red  = imagecolorallocate($img, ...self::RED);
        $dark = imagecolorallocate($img, ...self::DARK);
        imagefilledrectangle($img, 0, 0, $w, $h, $red);
        imagefilledrectangle($img, (int)($w * 0.45), 0, $w, $h, $dark);

        // Gold borders top/bottom
        $gold = imagecolorallocate($img, ...self::GOLD);
        $b    = 14;
        imagefilledrectangle($img, 0, 0, $w, $b, $gold);
        imagefilledrectangle($img, 0, $h - $b, $w, $h, $gold);

        // ── Left: Photo ──
        $photoW = (int)($w * 0.43) - 30;
        $photoH = $h - $b * 2 - 20;
        $photoX = 20;
        $photoY = $b + 10;
        $this->drawPhoto($img, $dog, $photoX, $photoY, $photoW, $photoH);

        // ── Right: Info panel ──
        $rx  = (int)($w * 0.45) + 40;
        $rw  = $w - $rx - 30;          // usable right width
        $ry  = 24;

        // Logo — centered in right panel
        $logoH = 80;
        $this->drawLogoScaled($img, $rx, $ry, $rw, $logoH);
        $ry += $logoH + 20;

        // Gold thin separator
        imagefilledrectangle($img, $rx, $ry, $rx + $rw, $ry + 2, $gold);
        $ry += 14;

        $white = imagecolorallocate($img, ...self::WHITE);
        $goldC = imagecolorallocate($img, ...self::GOLD);
        $cream = imagecolorallocate($img, ...self::CREAM);

        // Name — wrap to fit right panel
        $nameSize  = $this->fitSize($dog['name'], $this->fontBold, 70, $rw, 38);
        $nameLines = $this->wrap($dog['name'], $this->fontBold, $nameSize, $rw);
        foreach ($nameLines as $line) {
            $this->textLeft($img, $line, $this->fontBold, $nameSize, $white, $rx, $ry);
            $ry += (int)($nameSize * 1.18);
        }
        $ry += 8;

        // Breed · Gender
        $breed  = $this->breedLabel($dog['breed_variant'] ?? '');
        $gender = $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '');
        $line1  = implode('  ·  ', array_filter([$breed, $gender]));
        $this->textLeft($img, $line1, $this->fontReg, 26, $goldC, $rx, $ry);
        $ry += 36;

        // Country / Club
        $line2 = implode('  ·  ', array_filter([$dog['country'] ?? null, $dog['club'] ?? null]));
        if ($line2) {
            $this->textLeft($img, $line2, $this->fontReg, 24, $cream, $rx, $ry);
            $ry += 34;
        }

        // Champion
        if (!empty($dog['champion'])) {
            $ry += 6;
            imagefilledrectangle($img, $rx, $ry, $rx + $rw, $ry + 2, $gold);
            $ry += 12;
            $cLines = $this->wrap($dog['champion'], $this->fontBold, 24, $rw - 30);
            foreach (array_slice($cLines, 0, 3) as $i => $cl) {
                $this->textLeft($img, ($i === 0 ? '🏆  ' : '      ') . $cl, $this->fontBold, 24, $goldC, $rx, $ry);
                $ry += 34;
            }
        }

        // ── QR + URL at bottom right ──
        $qrSize = 110;
        $qrX    = $w - $qrSize - 30;
        $qrY    = $h - $qrSize - $b - 12;
        $qrImg  = $this->buildQr($dog['slug'] ?? '', $qrSize);
        if ($qrImg) {
            imagecopy($img, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize);
            imagedestroy($qrImg);
        }
        // URL to the left of QR
        $urlSize = 22;
        $urlX    = $rx;
        $urlY    = $h - $b - 14 - $urlSize;
        $this->textLeft($img, 'galgospedia.com', $this->fontBold, $urlSize, $goldC, $urlX, $urlY);
    }

    private function buildSquare(\GdImage $img, array $dog, int $w, int $h): void
    {
        $this->fillBackground($img, $w, $h);

        $b    = 14;
        $pad  = 40;
        $gold = imagecolorallocate($img, ...self::GOLD);

        // Logo top center
        $logoH = 100;
        $this->drawLogoScaled($img, $pad, $b + 16, $w - $pad * 2, $logoH);

        // Photo — square centered
        $photoSize = 560;
        $photoX    = (int)(($w - $photoSize) / 2);
        $photoY    = $b + $logoH + 30;
        $this->drawPhoto($img, $dog, $photoX, $photoY, $photoSize, $photoSize);

        $white = imagecolorallocate($img, ...self::WHITE);
        $goldC = imagecolorallocate($img, ...self::GOLD);
        $cream = imagecolorallocate($img, ...self::CREAM);

        $ry = $photoY + $photoSize + 28;

        // Name
        $nameSize  = $this->fitSize($dog['name'], $this->fontBold, 72, $w - $pad * 2, 40);
        $nameLines = $this->wrap($dog['name'], $this->fontBold, $nameSize, $w - $pad * 2);
        foreach ($nameLines as $line) {
            $this->textCenter($img, $line, $this->fontBold, $nameSize, $white, 0, $w, $ry);
            $ry += (int)($nameSize * 1.18);
        }
        $ry += 8;

        // Breed · Gender · Country
        $breed  = $this->breedLabel($dog['breed_variant'] ?? '');
        $gender = $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '');
        $sub    = implode('  ·  ', array_filter([$breed, $gender, $dog['country'] ?? null]));
        $this->textCenter($img, $sub, $this->fontReg, 28, $goldC, 0, $w, $ry);
        $ry += 42;

        // Gold separator
        $sepW = 500;
        imagefilledrectangle($img, (int)(($w - $sepW) / 2), $ry, (int)(($w + $sepW) / 2), $ry + 3, $gold);
        $ry += 18;

        // Champion
        if (!empty($dog['champion'])) {
            $cLines = $this->wrap($dog['champion'], $this->fontBold, 26, $w - $pad * 3);
            foreach (array_slice($cLines, 0, 2) as $i => $cl) {
                $this->textCenter($img, ($i === 0 ? '🏆  ' : '') . $cl, $this->fontBold, 26, $goldC, 0, $w, $ry);
                $ry += 38;
            }
        }

        // QR centered at bottom
        $qrSize = 130;
        $qrX    = (int)(($w - $qrSize) / 2);
        $qrY    = $h - $qrSize - $b - 30;
        $qrImg  = $this->buildQr($dog['slug'] ?? '', $qrSize);
        if ($qrImg) {
            imagecopy($img, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize);
            imagedestroy($qrImg);
        }
        $this->textCenter($img, 'galgospedia.com', $this->fontBold, 22, $goldC, 0, $w, $qrY + $qrSize + 8);
    }

    private function buildStory(\GdImage $img, array $dog, int $w, int $h): void
    {
        $this->fillBackground($img, $w, $h);

        $b    = 16;
        $pad  = 60;
        $gold = imagecolorallocate($img, ...self::GOLD);

        // Logo top center — bigger for story
        $logoH = 120;
        $this->drawLogoScaled($img, $pad, $b + 20, $w - $pad * 2, $logoH);

        // Photo large
        $photoSize = 780;
        $photoX    = (int)(($w - $photoSize) / 2);
        $photoY    = $b + $logoH + 36;
        $this->drawPhoto($img, $dog, $photoX, $photoY, $photoSize, $photoSize);

        $white = imagecolorallocate($img, ...self::WHITE);
        $goldC = imagecolorallocate($img, ...self::GOLD);
        $cream = imagecolorallocate($img, ...self::CREAM);

        $ry = $photoY + $photoSize + 40;

        // Name
        $nameSize  = $this->fitSize($dog['name'], $this->fontBold, 90, $w - $pad * 2, 50);
        $nameLines = $this->wrap($dog['name'], $this->fontBold, $nameSize, $w - $pad * 2);
        foreach ($nameLines as $line) {
            $this->textCenter($img, $line, $this->fontBold, $nameSize, $white, 0, $w, $ry);
            $ry += (int)($nameSize * 1.18);
        }
        $ry += 12;

        // Breed · Gender · Country
        $breed  = $this->breedLabel($dog['breed_variant'] ?? '');
        $gender = $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '');
        $sub    = implode('  ·  ', array_filter([$breed, $gender, $dog['country'] ?? null]));
        $this->textCenter($img, $sub, $this->fontReg, 36, $goldC, 0, $w, $ry);
        $ry += 54;

        if (!empty($dog['club'])) {
            $this->textCenter($img, $dog['club'], $this->fontReg, 30, $cream, 0, $w, $ry);
            $ry += 46;
        }

        // Gold separator
        $sepW = 600;
        imagefilledrectangle($img, (int)(($w - $sepW) / 2), $ry, (int)(($w + $sepW) / 2), $ry + 4, $gold);
        $ry += 24;

        // Champion
        if (!empty($dog['champion'])) {
            $cLines = $this->wrap($dog['champion'], $this->fontBold, 32, $w - $pad * 2);
            foreach (array_slice($cLines, 0, 3) as $i => $cl) {
                $this->textCenter($img, ($i === 0 ? '🏆  ' : '') . $cl, $this->fontBold, 32, $goldC, 0, $w, $ry);
                $ry += 46;
            }
        }

        // QR + URL bottom
        $qrSize = 180;
        $qrX    = (int)(($w - $qrSize) / 2);
        $qrY    = $h - $qrSize - $b - 50;
        $qrImg  = $this->buildQr($dog['slug'] ?? '', $qrSize);
        if ($qrImg) {
            imagecopy($img, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize);
            imagedestroy($qrImg);
        }
        $this->textCenter($img, 'galgospedia.com', $this->fontBold, 28, $goldC, 0, $w, $qrY + $qrSize + 12);
    }

    // ── Drawing helpers ───────────────────────────────────────────────────────

    private function fillBackground(\GdImage $img, int $w, int $h): void
    {
        $red  = imagecolorallocate($img, ...self::RED);
        $dark = imagecolorallocate($img, ...self::DARK);
        $gold = imagecolorallocate($img, ...self::GOLD);
        imagefilledrectangle($img, 0, 0, $w, $h, $red);
        // Dark footer area (bottom 28%)
        imagefilledrectangle($img, 0, (int)($h * 0.72), $w, $h, $dark);
        // Gold frame
        $b = 16;
        imagefilledrectangle($img, 0, 0, $w, $b, $gold);
        imagefilledrectangle($img, 0, $h - $b, $w, $h, $gold);
        imagefilledrectangle($img, 0, 0, $b, $h, $gold);
        imagefilledrectangle($img, $w - $b, 0, $w, $h, $gold);
    }

    /** Draw logo scaled to fit a bounding box, centered horizontally */
    private function drawLogoScaled(\GdImage $img, int $x, int $y, int $maxW, int $maxH): void
    {
        $path = PUB_PATH . '/logo/logo512-512.png';
        if (!file_exists($path)) return;
        $logo = @imagecreatefrompng($path);
        if (!$logo) return;
        $lw    = imagesx($logo);
        $lh    = imagesy($logo);
        $ratio = min($maxW / $lw, $maxH / $lh);
        $dw    = (int)($lw * $ratio);
        $dh    = (int)($lh * $ratio);
        $dx    = $x + (int)(($maxW - $dw) / 2);
        imagecopyresampled($img, $logo, $dx, $y, 0, 0, $dw, $dh, $lw, $lh);
        imagedestroy($logo);
    }

    private function drawPhoto(\GdImage $img, array $dog, int $x, int $y, int $w, int $h): void
    {
        $src  = null;
        $path = $dog['photo_webp'] ?? $dog['photo_thumb'] ?? '';

        if ($path && str_starts_with($path, 'r2:')) {
            $url  = rtrim(\Config\Config::r2PublicUrl(), '/') . '/' . substr($path, 3);
            $data = @file_get_contents($url);
            if ($data) $src = @imagecreatefromstring($data);
        } elseif ($path) {
            $full = PUB_PATH . '/' . ltrim($path, '/');
            if (file_exists($full)) $src = @imagecreatefromstring(file_get_contents($full));
        }

        if ($src) {
            $sw    = imagesx($src);
            $sh    = imagesy($src);
            $ratio = min($w / $sw, $h / $sh);   // object-contain
            $dw    = (int)($sw * $ratio);
            $dh    = (int)($sh * $ratio);
            $dx    = $x + (int)(($w - $dw) / 2);
            $dy    = $y + (int)(($h - $dh) / 2);
            imagecopyresampled($img, $src, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);
            imagedestroy($src);
        } else {
            $dark = imagecolorallocate($img, 130, 5, 20);
            imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $dark);
            $this->drawLogoScaled($img, $x, $y, $w, $h);
        }

        // Gold double border
        $gold = imagecolorallocate($img, ...self::GOLD);
        imagerectangle($img, $x - 4, $y - 4, $x + $w + 4, $y + $h + 4, $gold);
        imagerectangle($img, $x - 2, $y - 2, $x + $w + 2, $y + $h + 2, $gold);
    }

    private function buildQr(string $slug, int $size): ?\GdImage
    {
        try {
            $qr = new QrCode(
                data: 'https://galgospedia.com/galgos/' . $slug,
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: $size,
                margin: 6,
                foregroundColor: new Color(255, 255, 255),
                backgroundColor: new Color(191, 10, 48),
            );
            $png = (new PngWriter())->write($qr)->getString();
            return @imagecreatefromstring($png) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    // ── Text helpers ──────────────────────────────────────────────────────────

    private function textLeft(\GdImage $img, string $t, string $font, int $size, int $color, int $x, int $y): void
    {
        imagettftext($img, $size, 0, $x, $y + $size, $color, $font, $t);
    }

    private function textCenter(\GdImage $img, string $t, string $font, int $size, int $color, int $x1, int $x2, int $y): void
    {
        $box = imagettfbbox($size, 0, $font, $t);
        $tw  = abs($box[4] - $box[0]);
        $cx  = $x1 + (int)(($x2 - $x1 - $tw) / 2);
        imagettftext($img, $size, 0, max($x1 + 10, $cx), $y + $size, $color, $font, $t);
    }

    private function fitSize(string $text, string $font, int $max, int $maxW, int $min): int
    {
        $size = $max;
        while ($size > $min) {
            $box = imagettfbbox($size, 0, $font, $text);
            if (abs($box[4] - $box[0]) <= $maxW) break;
            $size -= 4;
        }
        return $size;
    }

    private function wrap(string $text, string $font, int $size, int $maxW): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $line  = '';
        foreach ($words as $word) {
            $test = $line !== '' ? "$line $word" : $word;
            $box  = imagettfbbox($size, 0, $font, $test);
            if (abs($box[4] - $box[0]) > $maxW && $line !== '') {
                $lines[] = $line;
                $line    = $word;
            } else {
                $line = $test;
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines ?: [''];
    }

    private function breedLabel(string $v): string
    {
        return match($v) {
            'english_greyhound' => 'Galgo Inglés',
            'hybrid'            => 'Galgo Híbrido',
            default             => 'Galgo Español',
        };
    }

    private function output(\GdImage $img, string $tipo): string
    {
        ob_start();
        match($tipo) {
            'jpg'  => imagejpeg($img, null, 93),
            'webp' => imagewebp($img, null, 90),
            default => imagepng($img),
        };
        $bytes = ob_get_clean();
        imagedestroy($img);
        return $bytes;
    }

    public function generatePdf(array $dog, string $layout): string
    {
        $bytes  = $this->generate($dog, $layout, 'jpg');
        $tmp    = tempnam(sys_get_temp_dir(), 'galgo_') . '.jpg';
        file_put_contents($tmp, $bytes);
        $orient = $layout === 'story' ? 'P' : 'L';
        $pdf    = new \FPDF($orient, 'mm', 'A4');
        $pdf->AddPage();
        [$pw, $ph] = $orient === 'L' ? [277, 175] : [180, 254];
        $mx = (int)((($orient === 'L' ? 297 : 210) - $pw) / 2);
        $my = (int)((($orient === 'L' ? 210 : 297) - $ph) / 2);
        $pdf->Image($tmp, $mx, $my, $pw, $ph, 'JPG');
        $out = $pdf->Output('S');
        @unlink($tmp);
        return $out;
    }
}
