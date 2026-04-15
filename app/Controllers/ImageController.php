<?php
declare(strict_types=1);
namespace Controllers;

use Services\ImageProcessor;

class ImageController extends BaseController
{
    public function upload(array $p = []): void
    {
        if (empty($_FILES['photo'])) {
            $this->json(['error' => 'No se recibió ningún archivo.'], 400);
        }
        try {
            $processor = new ImageProcessor();
            $paths     = $processor->process($_FILES['photo']);
            $this->json(['success' => true, 'webp' => $paths['webp'], 'thumb' => $paths['thumb']]);
        } catch (\RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }
}
