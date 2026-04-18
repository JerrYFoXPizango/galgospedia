<?php

declare(strict_types=1);

namespace Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;

class TarjetaGenerator
{
    private const RED    = [204,   0,   0];
    private const DARK   = [160,   0,   0];
    private const GOLD   = [201, 162,  39];
    private const WHITE  = [255, 255, 255];
    private const CREAM  = [255, 245, 220];

    private string $fontBold;
    private string $fontRegular;
    private string $pubPath;

    public function __construct()
    {
        $this->fontBold    = PUB_PATH . '/fonts/Montserrat-Bold.ttf';
        $this->fontRegular = PUB_PATH . '/fonts/Montserrat-Regular.ttf';
        $this->pubPath     = PUB_PATH;
    }

    /** Returns raw image bytes in the requested mime type */
    public function generate(array $dog, string $layout, string $tipo): string
    {
        [$w, $h] = match($layout) {
            'story'      => [1080, 1920],
            'cuadrado'   => [1080, 1080],
            default      => [1200,  630],   // horizontal
        };

        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);

        $this->drawBackground($img, $w, $h);
        $this->drawLogo($img, $w, $layout);

        if ($layout === 'horizontal') {
            $this->drawHorizontalLayout($img, $dog, $w, $h);
        } else {
            $this->drawVerticalLayout($img, $dog, $w, $h, $layout);
        }

        $this->drawFooter($img, $dog, $w, $h, $layout);

        return $this->output($img, $tipo, $w, $h);
    }

    private function drawBackground(\GdImage $img, int $w, int $h): void
    {
        $red  = imagecolorallocate($img, ...self::RED);
        $dark = imagecolorallocate($img, ...self::DARK);
        imagefilledrectangle($img, 0, 0, $w, $h, $red);
        // Subtle gradient: darker bottom strip
        imagefilledrectangle($img, 0, (int)($h * 0.75), $w, $h, $dark);
        // Gold top line
        $gold = imagecolorallocate($img, ...self::GOLD);
        imagefilledrectangle($img, 0, 0, $w, 8, $gold);
        imagefilledrectangle($img, 0, $h - 8, $w, $h, $gold);
    }

    private function drawLogo(\GdImage $img, int $w, string $layout): void
    {
        $logoPath = $this->pubPath . '/logo/logo512-512.png';
        if (!file_exists($logoPath)) return;

        $size = $layout === 'horizontal' ? 60 : 80;
        $logo = imagecreatefrompng($logoPath);
        $lw   = imagesx($logo);
        $lh   = imagesy($logo);
        $x    = $layout === 'horizontal' ? $w - $size - 30 : (int)(($w - $size) / 2);
        $y    = $layout === 'horizontal' ? 20 : 30;
        imagecopyresampled($img, $logo, $x, $y, 0, 0, $size, $size, $lw, $lh);
        imagedestroy($logo);
    }

    private function drawVerticalLayout(\GdImage $img, array $dog, int $w, int $h, string $layout): void
    {
        $white = imagecolorallocate($img, ...self::WHITE);
        $gold  = imagecolorallocate($img, ...self::GOLD);
        $cream = imagecolorallocate($img, ...self::CREAM);

        // Photo area
        $photoSize = $layout === 'story' ? 700 : 600;
        $photoX    = (int)(($w - $photoSize) / 2);
        $photoY    = $layout === 'story' ? 140 : 120;
        $this->drawPhoto($img, $dog, $photoX, $photoY, $photoSize, $photoSize);

        // Name
        $nameY = $photoY + $photoSize + 50;
        $nameSize = $layout === 'story' ? 72 : 60;
        $this->centeredText($img, $dog['name'], $this->fontBold, $nameSize, $white, $w, $nameY);

        // Breed · Gender
        $breed  = $this->breedLabel($dog['breed_variant'] ?? '');
        $gender = $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '');
        $sub    = implode(' · ', array_filter([$breed, $gender, $dog['country'] ?? null]));
        $this->centeredText($img, $sub, $this->fontRegular, 38, $gold, $w, $nameY + $nameSize + 20);

        // Club
        if (!empty($dog['club'])) {
            $this->centeredText($img, $dog['club'], $this->fontRegular, 34, $cream, $w, $nameY + $nameSize + 75);
        }

        // Gold separator
        $sepY = $nameY + $nameSize + 130;
        $gold = imagecolorallocate($img, ...self::GOLD);
        imagefilledrectangle($img, 80, $sepY, $w - 80, $sepY + 3, $gold);

        // Champion
        if (!empty($dog['champion'])) {
            $champY = $sepY + 30;
            $lines  = $this->wrapText($dog['champion'], $this->fontBold, 34, $w - 160);
            foreach ($lines as $i => $line) {
                $this->centeredText($img, '🏆 ' . $line, $this->fontBold, 34, $gold, $w, $champY + $i * 50);
            }
        }
    }

    private function drawHorizontalLayout(\GdImage $img, array $dog, int $w, int $h): void
    {
        $white = imagecolorallocate($img, ...self::WHITE);
        $gold  = imagecolorallocate($img, ...self::GOLD);
        $cream = imagecolorallocate($img, ...self::CREAM);

        // Photo — left half
        $photoSize = 560;
        $photoX    = 30;
        $photoY    = (int)(($h - $photoSize) / 2);
        $this->drawPhoto($img, $dog, $photoX, $photoY, $photoSize, $photoSize);

        // Right column
        $rx = 640;
        $ry = 80;

        // Name
        $nameLines = $this->wrapText($dog['name'], $this->fontBold, 68, $w - $rx - 40);
        foreach ($nameLines as $i => $line) {
            $this->drawText($img, $line, $this->fontBold, 68, $white, $rx, $ry + $i * 80);
        }
        $ry += count($nameLines) * 80 + 20;

        // Breed · Gender · Country
        $breed  = $this->breedLabel($dog['breed_variant'] ?? '');
        $gender = $dog['gender'] === 'male' ? 'Macho' : ($dog['gender'] === 'female' ? 'Hembra' : '');
        $sub    = implode(' · ', array_filter([$breed, $gender, $dog['country'] ?? null]));
        $this->drawText($img, $sub, $this->fontRegular, 32, $gold, $rx, $ry);
        $ry += 50;

        if (!empty($dog['club'])) {
            $this->drawText($img, $dog['club'], $this->fontRegular, 28, $cream, $rx, $ry);
            $ry += 44;
        }

        // Separator
        imagefilledrectangle($img, $rx, $ry + 10, $w - 40, $ry + 13, $gold);
        $ry += 40;

        // Champion
        if (!empty($dog['champion'])) {
            $lines = $this->wrapText($dog['champion'], $this->fontBold, 28, $w - $rx - 40);
            foreach (array_slice($lines, 0, 3) as $i => $line) {
                $this->drawText($img, ($i === 0 ? '🏆 ' : '     ') . $line, $this->fontBold, 28, $gold, $rx, $ry + $i * 40);
            }
        }
    }

    private function drawFooter(\GdImage $img, array $dog, int $w, int $h, string $layout): void
    {
        $white = imagecolorallocate($img, ...self::WHITE);
        $gold  = imagecolorallocate($img, ...self::GOLD);

        $url    = 'https://galgospedia.com/galgos/' . ($dog['slug'] ?? '');
        $qrSize = $layout === 'horizontal' ? 110 : 140;

        // Generate QR
        $qrImg = $this->generateQrImage($url, $qrSize);

        if ($layout === 'horizontal') {
            $qrX = $w - $qrSize - 40;
            $qrY = $h - $qrSize - 20;
        } else {
            $qrX = (int)(($w - $qrSize) / 2) - 10;
            $qrY = $h - $qrSize - 40;
        }

        if ($qrImg) {
            imagecopy($img, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize);
            imagedestroy($qrImg);
        }

        // URL text
        $urlText  = 'galgospedia.com';
        $urlSize  = 28;
        $textX    = $layout === 'horizontal' ? $w - $qrSize - 40 : $qrX + $qrSize + 15;
        $textY    = $layout === 'horizontal' ? $h - 55 : $qrY + (int)($qrSize / 2);
        $this->drawText($img, $urlText, $this->fontRegular, $urlSize, $gold, $textX, $textY);
    }

    private function drawPhoto(\GdImage $img, array $dog, int $x, int $y, int $w, int $h): void
    {
        $photo = null;
        $photoPath = $dog['photo_webp'] ?? $dog['photo_thumb'] ?? '';

        if ($photoPath && str_starts_with($photoPath, 'r2:')) {
            // Download from R2
            $url = rtrim(\Config\Config::r2PublicUrl(), '/') . '/' . substr($photoPath, 3);
            $tmp = tempnam(sys_get_temp_dir(), 'galgo_card_');
            @file_put_contents($tmp, @file_get_contents($url));
            if (filesize($tmp) > 0) {
                $photo = @imagecreatefromstring(file_get_contents($tmp));
            }
            @unlink($tmp);
        } elseif ($photoPath && file_exists(PUB_PATH . '/' . ltrim($photoPath, '/'))) {
            $photo = @imagecreatefromstring(file_get_contents(PUB_PATH . '/' . ltrim($photoPath, '/')));
        }

        if ($photo) {
            $pw = imagesx($photo);
            $ph = imagesy($photo);
            // Cover crop: center
            $ratio  = max($w / $pw, $h / $ph);
            $nw     = (int)($pw * $ratio);
            $nh     = (int)($ph * $ratio);
            $sx     = (int)(($nw - $w) / 2);
            $sy     = (int)(($nh - $h) / 2);
            $tmp2   = imagecreatetruecolor($w, $h);
            imagecopyresampled($tmp2, $photo, 0, 0, (int)($sx / $ratio), (int)($sy / $ratio), $w, $h, (int)($w / $ratio), (int)($h / $ratio));
            imagecopy($img, $tmp2, $x, $y, 0, 0, $w, $h);
            imagedestroy($tmp2);
            imagedestroy($photo);
        } else {
            // Placeholder
            $dark = imagecolorallocate($img, 120, 0, 0);
            $gold = imagecolorallocate($img, ...self::GOLD);
            imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $dark);
            // Logo placeholder
            $logoPath = $this->pubPath . '/logo/logo512-512.png';
            if (file_exists($logoPath)) {
                $size = (int)min($w, $h) / 2;
                $logo = imagecreatefrompng($logoPath);
                $lw   = imagesx($logo);
                $lh   = imagesy($logo);
                $lx   = $x + (int)(($w - $size) / 2);
                $ly   = $y + (int)(($h - $size) / 2);
                imagecopyresampled($img, $logo, $lx, $ly, 0, 0, $size, $size, $lw, $lh);
                imagedestroy($logo);
            }
        }

        // Border
        $gold = imagecolorallocate($img, ...self::GOLD);
        imagerectangle($img, $x, $y, $x + $w, $y + $h, $gold);
        imagerectangle($img, $x + 1, $y + 1, $x + $w - 1, $y + $h - 1, $gold);
    }

    private function generateQrImage(string $url, int $size): ?\GdImage
    {
        try {
            $qrCode = new QrCode(
                data: $url,
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: $size,
                margin: 4,
                foregroundColor: new Color(255, 255, 255),
                backgroundColor: new Color(204, 0, 0),
            );
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            return imagecreatefromstring($result->getString()) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function centeredText(\GdImage $img, string $text, string $font, int $size, int $color, int $w, int $y): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $tw  = abs($box[4] - $box[0]);
        $x   = (int)(($w - $tw) / 2);
        imagettftext($img, $size, 0, $x, $y + $size, $color, $font, $text);
    }

    private function drawText(\GdImage $img, string $text, string $font, int $size, int $color, int $x, int $y): void
    {
        imagettftext($img, $size, 0, $x, $y + $size, $color, $font, $text);
    }

    private function wrapText(string $text, string $font, int $size, int $maxW): array
    {
        $words  = explode(' ', $text);
        $lines  = [];
        $line   = '';
        foreach ($words as $word) {
            $test = $line ? "$line $word" : $word;
            $box  = imagettfbbox($size, 0, $font, $test);
            $tw   = abs($box[4] - $box[0]);
            if ($tw > $maxW && $line !== '') {
                $lines[] = $line;
                $line    = $word;
            } else {
                $line = $test;
            }
        }
        if ($line) $lines[] = $line;
        return $lines;
    }

    private function breedLabel(string $variant): string
    {
        return match($variant) {
            'english_greyhound' => 'Galgo Inglés',
            'hybrid'            => 'Galgo Híbrido',
            default             => 'Galgo Español',
        };
    }

    private function output(\GdImage $img, string $tipo, int $w, int $h): string
    {
        ob_start();
        match($tipo) {
            'jpg'  => imagejpeg($img, null, 92),
            'webp' => imagewebp($img, null, 90),
            default => imagepng($img),
        };
        $bytes = ob_get_clean();
        imagedestroy($img);
        return $bytes;
    }

    public function generatePdf(array $dog, string $layout): string
    {
        $imgBytes = $this->generate($dog, $layout, 'jpg');
        $tmpImg   = tempnam(sys_get_temp_dir(), 'galgo_pdf_') . '.jpg';
        file_put_contents($tmpImg, $imgBytes);

        $pdf = new \FPDF('L', 'mm', 'A4'); // landscape for horizontal
        $pdf->AddPage();
        $pdf->Image($tmpImg, 10, 10, 277, 0, 'JPG');
        $pdfStr = $pdf->Output('S');
        @unlink($tmpImg);
        return $pdfStr;
    }
}
