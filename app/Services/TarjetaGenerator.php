<?php

declare(strict_types=1);

namespace Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;

class TarjetaGenerator
{
    private const RED   = [204,   0,   0];
    private const DARK  = [140,   0,   0];
    private const GOLD  = [201, 162,  39];
    private const WHITE = [255, 255, 255];
    private const CREAM = [255, 235, 180];

    private string $fontBold;
    private string $fontRegular;

    public function __construct()
    {
        $this->fontBold    = PUB_PATH . '/fonts/Montserrat-Bold.ttf';
        $this->fontRegular = PUB_PATH . '/fonts/Montserrat-Regular.ttf';
    }

    public function generate(array $dog, string $layout, string $tipo): string
    {
        [$w, $h] = match($layout) {
            'story'    => [1080, 1920],
            'cuadrado' => [1080, 1080],
            default    => [1200,  630],
        };

        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);

        $this->drawBackground($img, $w, $h);

        match($layout) {
            'horizontal' => $this->layoutHorizontal($img, $dog, $w, $h),
            default      => $this->layoutVertical($img, $dog, $w, $h, $layout),
        };

        return $this->output($img, $tipo);
    }

    // ── Layouts ──────────────────────────────────────────────

    private function layoutHorizontal(\GdImage $img, array $dog, int $w, int $h): void
    {
        $pad     = 30;
        $divider = (int)($w * 0.44);   // photo ends here
        $photoW  = $divider - $pad * 2;
        $photoH  = $h - $pad * 2;
        $photoX  = $pad;
        $photoY  = $pad;

        $this->drawPhoto($img, $dog, $photoX, $photoY, $photoW, $photoH);

        // Right column
        $rx     = $divider + 40;
        $rw     = $w - $rx - $pad;     // usable width for text
        $qrSize = 130;
        $qrX    = $w - $qrSize - $pad;
        $qrY    = $h - $qrSize - $pad;

        // Logo top-right
        $this->drawLogo($img, $w - 90 - $pad, $pad, 90);

        // Name — auto-size down if long
        $nameSize = $this->fitFontSize($dog['name'], $this->fontBold, 80, $rw, 48);
        $ry = 40;
        $nameLines = $this->wrapText($dog['name'], $this->fontBold, $nameSize, $rw);
        $white = imagecolorallocate($img, ...self::WHITE);
        $gold  = imagecolorallocate($img, ...self::GOLD);
        $cream = imagecolorallocate($img, ...self::CREAM);
        foreach ($nameLines as $line) {
            $this->text($img, $line, $this->fontBold, $nameSize, $white, $rx, $ry);
            $ry += (int)($nameSize * 1.15);
        }
        $ry += 12;

        // Breed · Gender
        $breed  = $this->breedLabel($dog['breed_variant'] ?? '');
        $gender = $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '');
        $parts  = array_filter([$breed, $gender, $dog['country'] ?? null]);
        $sub    = implode('  ·  ', $parts);
        $this->text($img, $sub, $this->fontRegular, 30, $gold, $rx, $ry);
        $ry += 44;

        // Club
        if (!empty($dog['club'])) {
            $this->text($img, $dog['club'], $this->fontRegular, 26, $cream, $rx, $ry);
            $ry += 38;
        }

        // Gold separator
        imagefilledrectangle($img, $rx, $ry + 6, $rx + (int)($rw * 0.85), $ry + 9, $gold);
        $ry += 30;

        // Champion
        if (!empty($dog['champion'])) {
            $champLines = $this->wrapText($dog['champion'], $this->fontRegular, 26, $rw - 10);
            foreach (array_slice($champLines, 0, 4) as $i => $line) {
                $prefix = $i === 0 ? '🏆  ' : '      ';
                $this->text($img, $prefix . $line, $this->fontBold, 26, $gold, $rx, $ry);
                $ry += 36;
            }
        }

        // QR code
        $qrImg = $this->buildQr($dog['slug'] ?? '', $qrSize);
        if ($qrImg) {
            imagecopy($img, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize);
            imagedestroy($qrImg);
        }

        // URL below QR
        $urlY = $qrY + $qrSize + 6;
        if ($urlY + 28 < $h) {
            $this->textCentered($img, 'galgospedia.com', $this->fontRegular, 20, $gold, $qrX, $qrX + $qrSize, $urlY);
        }
    }

    private function layoutVertical(\GdImage $img, array $dog, int $w, int $h, string $layout): void
    {
        $isStory = $layout === 'story';
        $pad     = 60;

        // Logo
        $logoSize = 80;
        $this->drawLogo($img, (int)(($w - $logoSize) / 2), 30, $logoSize);

        // Photo
        $photoSize = $isStory ? 720 : 600;
        $photoX    = (int)(($w - $photoSize) / 2);
        $photoY    = 140;
        $this->drawPhoto($img, $dog, $photoX, $photoY, $photoSize, $photoSize);

        $white = imagecolorallocate($img, ...self::WHITE);
        $gold  = imagecolorallocate($img, ...self::GOLD);
        $cream = imagecolorallocate($img, ...self::CREAM);

        $ry = $photoY + $photoSize + 40;

        // Name
        $nameSize  = $this->fitFontSize($dog['name'], $this->fontBold, 76, $w - $pad * 2, 44);
        $nameLines = $this->wrapText($dog['name'], $this->fontBold, $nameSize, $w - $pad * 2);
        foreach ($nameLines as $line) {
            $this->textCentered($img, $line, $this->fontBold, $nameSize, $white, $pad, $w - $pad, $ry);
            $ry += (int)($nameSize * 1.2);
        }
        $ry += 10;

        // Breed · Gender · Country
        $breed  = $this->breedLabel($dog['breed_variant'] ?? '');
        $gender = $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '');
        $parts  = array_filter([$breed, $gender, $dog['country'] ?? null]);
        $this->textCentered($img, implode('  ·  ', $parts), $this->fontRegular, 34, $gold, $pad, $w - $pad, $ry);
        $ry += 50;

        if (!empty($dog['club'])) {
            $this->textCentered($img, $dog['club'], $this->fontRegular, 28, $cream, $pad, $w - $pad, $ry);
            $ry += 44;
        }

        // Separator
        $sepW = (int)($w * 0.6);
        imagefilledrectangle($img, (int)(($w - $sepW) / 2), $ry + 4, (int)(($w + $sepW) / 2), $ry + 7, $gold);
        $ry += 30;

        // Champion
        if (!empty($dog['champion'])) {
            $lines = $this->wrapText($dog['champion'], $this->fontBold, 30, $w - $pad * 3);
            foreach (array_slice($lines, 0, 3) as $i => $line) {
                $this->textCentered($img, ($i === 0 ? '🏆  ' : '') . $line, $this->fontBold, 30, $gold, $pad, $w - $pad, $ry);
                $ry += 44;
            }
            $ry += 10;
        }

        // QR + URL at bottom
        $qrSize = 160;
        $qrX    = (int)(($w - $qrSize) / 2);
        $qrY    = $h - $qrSize - 60;
        $qrImg  = $this->buildQr($dog['slug'] ?? '', $qrSize);
        if ($qrImg) {
            imagecopy($img, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize);
            imagedestroy($qrImg);
        }
        $this->textCentered($img, 'galgospedia.com', $this->fontRegular, 24, $gold, $pad, $w - $pad, $qrY + $qrSize + 10);
    }

    // ── Helpers ──────────────────────────────────────────────

    private function drawBackground(\GdImage $img, int $w, int $h): void
    {
        $red  = imagecolorallocate($img, ...self::RED);
        $dark = imagecolorallocate($img, ...self::DARK);
        $gold = imagecolorallocate($img, ...self::GOLD);

        imagefilledrectangle($img, 0, 0, $w, $h, $red);
        // Darker bottom quarter
        imagefilledrectangle($img, 0, (int)($h * 0.72), $w, $h, $dark);
        // Gold border lines
        $b = 12;
        imagefilledrectangle($img, 0, 0, $w, $b, $gold);
        imagefilledrectangle($img, 0, $h - $b, $w, $h, $gold);
        imagefilledrectangle($img, 0, 0, $b, $h, $gold);
        imagefilledrectangle($img, $w - $b, 0, $w, $h, $gold);
    }

    private function drawLogo(\GdImage $img, int $x, int $y, int $size): void
    {
        $logoPath = PUB_PATH . '/logo/logo512-512.png';
        if (!file_exists($logoPath)) return;
        $logo = @imagecreatefrompng($logoPath);
        if (!$logo) return;
        $lw = imagesx($logo);
        $lh = imagesy($logo);
        imagecopyresampled($img, $logo, $x, $y, 0, 0, $size, $size, $lw, $lh);
        imagedestroy($logo);
    }

    private function drawPhoto(\GdImage $img, array $dog, int $x, int $y, int $w, int $h): void
    {
        $source = null;
        $path   = $dog['photo_webp'] ?? $dog['photo_thumb'] ?? '';

        if ($path && str_starts_with($path, 'r2:')) {
            $url  = rtrim(\Config\Config::r2PublicUrl(), '/') . '/' . substr($path, 3);
            $data = @file_get_contents($url);
            if ($data) $source = @imagecreatefromstring($data);
        } elseif ($path) {
            $full = PUB_PATH . '/' . ltrim($path, '/');
            if (file_exists($full)) $source = @imagecreatefromstring(file_get_contents($full));
        }

        if ($source) {
            $pw   = imagesx($source);
            $ph   = imagesy($source);
            // object-contain: fit inside box keeping ratio
            $ratio = min($w / $pw, $h / $ph);
            $nw    = (int)($pw * $ratio);
            $nh    = (int)($ph * $ratio);
            $ox    = $x + (int)(($w - $nw) / 2);
            $oy    = $y + (int)(($h - $nh) / 2);
            imagecopyresampled($img, $source, $ox, $oy, 0, 0, $nw, $nh, $pw, $ph);
            imagedestroy($source);
        } else {
            // Placeholder
            $dark = imagecolorallocate($img, 140, 0, 0);
            imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $dark);
            $this->drawLogo($img, $x + (int)(($w - 120) / 2), $y + (int)(($h - 120) / 2), 120);
        }

        // Gold border around photo
        $gold = imagecolorallocate($img, ...self::GOLD);
        $t    = 3;
        imagerectangle($img, $x - $t, $y - $t, $x + $w + $t, $y + $h + $t, $gold);
        imagerectangle($img, $x - $t + 1, $y - $t + 1, $x + $w + $t - 1, $y + $h + $t - 1, $gold);
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
                backgroundColor: new Color(204, 0, 0),
            );
            $result = (new PngWriter())->write($qr);
            return @imagecreatefromstring($result->getString()) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function text(\GdImage $img, string $t, string $font, int $size, int $color, int $x, int $y): void
    {
        imagettftext($img, $size, 0, $x, $y + $size, $color, $font, $t);
    }

    private function textCentered(\GdImage $img, string $t, string $font, int $size, int $color, int $x1, int $x2, int $y): void
    {
        $box = imagettfbbox($size, 0, $font, $t);
        $tw  = abs($box[4] - $box[0]);
        $cx  = $x1 + (int)(($x2 - $x1 - $tw) / 2);
        imagettftext($img, $size, 0, max($x1, $cx), $y + $size, $color, $font, $t);
    }

    private function fitFontSize(string $text, string $font, int $maxSize, int $maxW, int $minSize): int
    {
        $size = $maxSize;
        while ($size > $minSize) {
            $box = imagettfbbox($size, 0, $font, $text);
            if (abs($box[4] - $box[0]) <= $maxW) break;
            $size -= 4;
        }
        return $size;
    }

    private function wrapText(string $text, string $font, int $size, int $maxW): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $line  = '';
        foreach ($words as $word) {
            $test = $line ? "$line $word" : $word;
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
        [$pw, $ph] = $orient === 'L' ? [287, 190] : [190, 277];
        $pdf->Image($tmp, (int)((($orient === 'L' ? 297 : 210) - $pw) / 2), (int)((($orient === 'L' ? 210 : 297) - $ph) / 2), $pw, $ph, 'JPG');
        $out = $pdf->Output('S');
        @unlink($tmp);
        return $out;
    }
}
