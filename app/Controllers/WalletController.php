<?php
declare(strict_types=1);
namespace Controllers;

use Models\{UserWalletDoc, Dog};
use Helpers\{Csrf, Flash};
use Services\{AuthService, R2Storage};

class WalletController extends BaseController
{
    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];
    private const MAX_FILE_BYTES  = 10 * 1024 * 1024;  // 10 MB por archivo
    private const MAX_TOTAL_BYTES = 50 * 1024 * 1024;  // 50 MB por usuario

    private const DOC_TYPES = [
        'cartilla_veterinaria' => 'Cartilla veterinaria',
        'microchip'            => 'Certificado microchip',
        'cesion'               => 'Certificado de cesión',
        'licencia_federativa'  => 'Licencia federativa',
        'permiso_caza'         => 'Permiso de caza',
        'vacuna'               => 'Certificado de vacunación',
        'otro'                 => 'Otro',
    ];

    /** GET /mi-billetera */
    public function index(): void
    {
        AuthService::guard();
        $userId = $this->currentUserId();
        $model  = new UserWalletDoc();
        $docs   = $model->listByUser($userId);
        $used   = $model->totalSizeByUser($userId);

        // Agrupar por tipo
        $byType = [];
        foreach (self::DOC_TYPES as $key => $label) {
            $byType[$key] = ['label' => $label, 'docs' => []];
        }
        foreach ($docs as $doc) {
            $t = $doc['doc_type'] ?? 'otro';
            if (!isset($byType[$t])) { $t = 'otro'; }
            $byType[$t]['docs'][] = $doc;
        }
        // Eliminar tipos vacíos para no mostrar secciones en blanco
        $byType = array_filter($byType, fn($g) => count($g['docs']) > 0);

        $this->render('wallet/index', [
            'byType'   => $byType,
            'total'    => count($docs),
            'usedBytes'=> $used,
            'maxBytes' => self::MAX_TOTAL_BYTES,
        ]);
    }

    /** GET /mi-billetera/subir */
    public function upload(): void
    {
        AuthService::guard();
        $userId = $this->currentUserId();
        $dogs   = $this->getUserDogs($userId);

        $this->render('wallet/upload', [
            'errors'   => [],
            'docTypes' => self::DOC_TYPES,
            'dogs'     => $dogs,
            'old'      => [],
        ]);
    }

    /** POST /mi-billetera */
    public function store(): void
    {
        AuthService::guard();
        Csrf::verify();

        $userId   = $this->currentUserId();
        $model    = new UserWalletDoc();
        $dogs     = $this->getUserDogs($userId);
        $errors   = [];

        $title   = trim($this->input('title', ''));
        $type    = $this->input('doc_type', 'otro');
        $dogId   = (int) $this->input('dog_id', 0);
        $expires = $this->input('expires_at', '');
        $notes   = $this->input('notes', '');

        if (strlen($title) < 2) {
            $errors[] = 'El título es obligatorio (mínimo 2 caracteres).';
        }
        if (!array_key_exists($type, self::DOC_TYPES)) {
            $errors[] = 'Tipo de documento no válido.';
        }
        if ($dogId > 0 && !$this->userOwnsDog($userId, $dogId, $dogs)) {
            $errors[] = 'El galgo seleccionado no es tuyo.';
            $dogId = 0;
        }

        // Validar archivo
        $fileOk = !empty($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK;
        if (!$fileOk) {
            $errors[] = 'Debes seleccionar un archivo.';
        }

        if ($fileOk && !$errors) {
            $file  = $_FILES['document'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']) ?: '';

            if ($file['size'] > self::MAX_FILE_BYTES) {
                $errors[] = 'El archivo no puede superar los 10 MB.';
            } elseif (!in_array($mime, self::ALLOWED_MIME, true)) {
                $errors[] = 'Tipo de archivo no permitido. Se aceptan: JPEG, PNG, WEBP y PDF.';
            } elseif ($model->totalSizeByUser($userId) + $file['size'] > self::MAX_TOTAL_BYTES) {
                $errors[] = 'Has superado el límite de 50 MB de almacenamiento.';
            }
        }

        if ($errors) {
            $this->render('wallet/upload', [
                'errors'   => $errors,
                'docTypes' => self::DOC_TYPES,
                'dogs'     => $dogs,
                'old'      => ['title' => $title, 'doc_type' => $type, 'dog_id' => $dogId, 'expires_at' => $expires, 'notes' => $notes],
            ]);
            return;
        }

        $file    = $_FILES['document'];
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $stored  = sha1(uniqid('wal_', true)) . '_' . time() . '.' . $ext;

        if (R2Storage::enabled()) {
            $r2Key    = 'private/wallet/' . $userId . '/' . $stored;
            $filePath = R2Storage::putPrivate($r2Key, $file['tmp_name'], $mime);
        } else {
            $dir = BASE_PATH . '/storage/wallet/' . $userId;
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $stored)) {
                Flash::set('error', 'Error al guardar el archivo. Inténtalo de nuevo.');
                $this->redirect('/mi-billetera/subir');
                return;
            }
            $filePath = 'wallet/' . $userId . '/' . $stored;
        }

        $model->add([
            'user_id'       => $userId,
            'dog_id'        => $dogId ?: null,
            'doc_type'      => $type,
            'title'         => $title,
            'file_path'     => $filePath,
            'original_name' => $file['name'],
            'mime_type'     => $mime,
            'file_size'     => (int) $file['size'],
            'expires_at'    => $expires ?: null,
            'notes'         => $notes ?: null,
        ]);

        Flash::set('success', 'Documento guardado en tu billetera.');
        $this->redirect('/mi-billetera');
    }

    /** GET /mi-billetera/{id}/ver  — abre inline (imagen/PDF) */
    public function view(array $p = []): void
    {
        AuthService::guard();
        $userId = $this->currentUserId();
        $doc    = (new UserWalletDoc())->findById((int) $p['id']);

        if (!$doc || (int) $doc['user_id'] !== $userId) {
            http_response_code(403);
            echo 'Acceso denegado.';
            exit;
        }

        // R2 object → redirect to presigned URL (1 h TTL)
        if (str_starts_with($doc['file_path'], 'r2:')) {
            $key = substr($doc['file_path'], 3);
            header('Location: ' . R2Storage::presignedUrl($key));
            exit;
        }

        $absPath = BASE_PATH . '/storage/' . $doc['file_path'];
        if (!file_exists($absPath)) {
            http_response_code(404);
            echo 'Archivo no encontrado.';
            exit;
        }

        $mime     = $doc['mime_type'] ?: 'application/octet-stream';
        $filename = $doc['original_name'] ?: basename($doc['file_path']);

        // Imágenes y PDF → inline (se abren en el navegador)
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($absPath));
        header('Cache-Control: private, no-cache');
        readfile($absPath);
        exit;
    }

    /** POST /mi-billetera/{id}/eliminar */
    public function destroy(array $p = []): void
    {
        AuthService::guard();
        Csrf::verify();

        $userId = $this->currentUserId();
        $model  = new UserWalletDoc();
        $doc    = $model->findById((int) $p['id']);

        if (!$doc || (int) $doc['user_id'] !== $userId) {
            Flash::set('error', 'Documento no encontrado.');
            $this->redirect('/mi-billetera');
            return;
        }

        if (str_starts_with($doc['file_path'], 'r2:')) {
            R2Storage::delete($doc['file_path']);
        } else {
            $absPath = BASE_PATH . '/storage/' . $doc['file_path'];
            if (file_exists($absPath)) {
                unlink($absPath);
            }
        }

        $model->remove((int) $p['id']);
        Flash::set('success', 'Documento eliminado.');
        $this->redirect('/mi-billetera');
    }

    // ── Helpers privados ─────────────────────────────────────

    /** Galgos del usuario (owner_user_id o created_by) */
    private function getUserDogs(int $userId): array
    {
        $db   = \Config\Database::pdo();
        $stmt = $db->prepare(
            "SELECT id, name FROM dogs
             WHERE owner_user_id = ? OR created_by = ?
             ORDER BY name ASC"
        );
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }

    private function userOwnsDog(int $userId, int $dogId, array $dogs): bool
    {
        foreach ($dogs as $d) {
            if ((int) $d['id'] === $dogId) { return true; }
        }
        return false;
    }
}
