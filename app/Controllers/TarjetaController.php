<?php

declare(strict_types=1);

namespace Controllers;

use Models\Dog;
use Services\TarjetaGenerator;

class TarjetaController extends BaseController
{
    public function generate(array $p = []): void
    {
        $dog = (new Dog())->findBySlug($p['slug']);
        if (!$dog) {
            http_response_code(404);
            exit;
        }

        $layout = in_array($_GET['layout'] ?? '', ['story', 'cuadrado', 'horizontal'])
            ? $_GET['layout']
            : 'horizontal';

        $tipo = in_array($_GET['tipo'] ?? '', ['png', 'jpg', 'webp', 'pdf'])
            ? $_GET['tipo']
            : 'png';

        $gen      = new TarjetaGenerator();
        $filename = 'galgospedia-' . ($dog['slug'] ?? 'galgo') . '-' . $layout;

        if ($tipo === 'pdf') {
            $bytes = $gen->generatePdf($dog, $layout);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
            header('Content-Length: ' . strlen($bytes));
            echo $bytes;
            exit;
        }

        $bytes = $gen->generate($dog, $layout, $tipo);
        $mime  = match($tipo) {
            'jpg'  => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
        $ext = $tipo === 'jpg' ? 'jpg' : ($tipo === 'webp' ? 'webp' : 'png');

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
        header('Content-Length: ' . strlen($bytes));
        echo $bytes;
        exit;
    }
}
