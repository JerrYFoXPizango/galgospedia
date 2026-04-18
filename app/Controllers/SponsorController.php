<?php

namespace Controllers;

use Models\Sponsor;
use Helpers\Flash;
use Helpers\Csrf;

class SponsorController extends BaseController
{
    private const UPLOAD_DIR  = PUB_PATH . '/img/sponsors/';
    private const PUBLIC_BASE = '/img/sponsors/';
    private const MAX_SIZE    = 2 * 1024 * 1024; // 2 MB
    private const ALLOWED     = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];

    // GET /admin/patrocinadores
    public function index(): void
    {
        $sponsors = Sponsor::all();
        $this->render('admin/sponsors/index', compact('sponsors'));
    }

    // GET /admin/patrocinadores/nuevo
    public function create(): void
    {
        $errors = [];
        $old    = [];
        $this->render('admin/sponsors/form', compact('errors', 'old'));
    }

    // POST /admin/patrocinadores/nuevo
    public function store(): void
    {
        Csrf::verify();
        [$errors, $logoPath] = $this->handleUpload(null);

        $name  = trim($_POST['name'] ?? '');
        $url   = trim($_POST['website_url'] ?? '') ?: null;
        $order = (int)($_POST['sort_order'] ?? 0);

        if (empty($name)) $errors[] = 'El nombre es obligatorio.';
        if (!$logoPath && empty($errors))  $errors[] = 'El logo es obligatorio.';

        if (empty($errors)) {
            Sponsor::create($name, $logoPath, $url, $order);
            Flash::set('success', 'Patrocinador añadido.');
            header('Location: /admin/patrocinadores');
            exit;
        }

        $old = $_POST;
        $this->render('admin/sponsors/form', compact('errors', 'old'));
    }

    // GET /admin/patrocinadores/{id}/editar
    public function edit(array $p = []): void
    {
        $id      = (int)($p['id'] ?? 0);
        $sponsor = Sponsor::find($id);
        if (!$sponsor) { http_response_code(404); die('No encontrado.'); }
        $errors = [];
        $old    = $sponsor;
        $this->render('admin/sponsors/form', compact('errors', 'old', 'sponsor'));
    }

    // POST /admin/patrocinadores/{id}/actualizar
    public function update(array $p = []): void
    {
        $id      = (int)($p['id'] ?? 0);
        Csrf::verify();
        $sponsor = Sponsor::find($id);
        if (!$sponsor) { http_response_code(404); die('No encontrado.'); }

        [$errors, $newLogoPath] = $this->handleUpload($sponsor['logo_path']);

        $name     = trim($_POST['name'] ?? '');
        $url      = trim($_POST['website_url'] ?? '') ?: null;
        $order    = (int)($_POST['sort_order'] ?? 0);
        $active   = isset($_POST['active']) ? 1 : 0;
        $logoPath = $newLogoPath ?: $sponsor['logo_path'];

        if (empty($name)) $errors[] = 'El nombre es obligatorio.';

        if (empty($errors)) {
            // Borrar logo antiguo si se subió uno nuevo
            if ($newLogoPath && $newLogoPath !== $sponsor['logo_path']) {
                $this->deleteFile($sponsor['logo_path']);
            }
            Sponsor::update($id, $name, $logoPath, $url, $order, $active);
            Flash::set('success', 'Patrocinador actualizado.');
            header('Location: /admin/patrocinadores');
            exit;
        }

        $old = array_merge($sponsor, $_POST);
        $this->render('admin/sponsors/form', compact('errors', 'old', 'sponsor'));
    }

    // POST /admin/patrocinadores/{id}/eliminar
    public function destroy(array $p = []): void
    {
        $id       = (int)($p['id'] ?? 0);
        Csrf::verify();
        $logoPath = Sponsor::delete($id);
        if ($logoPath) $this->deleteFile($logoPath);
        Flash::set('success', 'Patrocinador eliminado.');
        header('Location: /admin/patrocinadores');
        exit;
    }

    // POST /admin/patrocinadores/{id}/toggle
    public function toggle(array $p = []): void
    {
        $id = (int)($p['id'] ?? 0);
        Csrf::verify();
        Sponsor::toggleActive($id);
        header('Location: /admin/patrocinadores');
        exit;
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /** Procesa el upload del logo. Devuelve [errors[], publicPath|null] */
    private function handleUpload(?string $_current): array
    {
        $errors = [];
        if (empty($_FILES['logo']['name'])) return [$errors, null];

        $file = $_FILES['logo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir el archivo.';
            return [$errors, null];
        }
        if ($file['size'] > self::MAX_SIZE) {
            $errors[] = 'El logo no puede superar 2 MB.';
            return [$errors, null];
        }
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED, true)) {
            $errors[] = 'Formato no permitido. Usa PNG, JPG, WebP o SVG.';
            return [$errors, null];
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'png';
        $filename = 'sponsor_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dest     = self::UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors[] = 'No se pudo guardar el archivo.';
            return [$errors, null];
        }

        return [$errors, self::PUBLIC_BASE . $filename];
    }

    private function deleteFile(string $publicPath): void
    {
        if (str_contains($publicPath, 'placeholder')) return; // nunca borrar el placeholder
        $abs = PUBLIC_PATH . $publicPath;
        if (file_exists($abs)) @unlink($abs);
    }
}
