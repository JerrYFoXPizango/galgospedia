<?php

declare(strict_types=1);

namespace Controllers;

use Models\Club;
use Models\ClubMember;
use Models\ClubDocument;
use Models\ClubEvent;
use Services\{AuthService, R2Storage};
use Helpers\{Flash, Paginator};

class OficinaController extends BaseController
{
    private Club         $clubModel;
    private ClubMember   $memberModel;
    private ClubDocument $docModel;
    private ClubEvent    $eventModel;

    private const DOC_ALLOWED_MIME = [
        'application/pdf',
        'image/jpeg', 'image/jpg', 'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    private const DOC_MAX_BYTES = 20 * 1024 * 1024; // 20 MB

    public function __construct()
    {
        $this->clubModel   = new Club();
        $this->memberModel = new ClubMember();
        $this->docModel    = new ClubDocument();
        $this->eventModel  = new ClubEvent();
    }

    /** GET /oficina — public listing of active clubs */
    public function index(): void
    {
        $page    = max(1, (int) ($this->query('page', 1)));
        $perPage = 24;
        $result  = $this->clubModel->listActive($page, $perPage);

        $this->render('oficina/index', [
            'clubs'     => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'perPage'   => $result['perPage'],
            'paginator' => new Paginator($result['total'], $result['page'], $result['perPage']),
        ]);
    }

    /** GET /oficina/mi-club — president dashboard */
    public function miClub(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);

        if (!$club) {
            $this->redirect('/oficina/solicitar-club');
            return;
        }

        $clubId = (int) $club['id'];

        $this->render('oficina/mi-club', [
            'club'       => $club,
            'members'    => $this->memberModel->listByClub($clubId),
            'stats'      => $this->memberModel->statsByClub($clubId),
            'alerts'     => $this->memberModel->licenseAlerts($clubId),
        ]);
    }

    /** GET /oficina/mi-club/socios/nuevo */
    public function nuevoSocio(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $this->render('oficina/socio-form', ['club' => $club, 'member' => null, 'errors' => []]);
    }

    /** POST /oficina/mi-club/socios */
    public function storeSocio(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $name = trim($this->input('name', ''));
        if (\strlen($name) < 2) {
            $this->render('oficina/socio-form', [
                'club'   => $club,
                'member' => null,
                'errors' => ['El nombre es obligatorio (mínimo 2 caracteres).'],
            ]);
            return;
        }

        $expiresRaw = $this->input('license_expires_at', '');

        $this->memberModel->add([
            'club_id'            => $club['id'],
            'name'               => $name,
            'email'              => $this->input('email'),
            'phone'              => $this->input('phone'),
            'license_number'     => $this->input('license_number'),
            'license_type'       => $this->input('license_type'),
            'license_expires_at' => $expiresRaw ?: null,
            'status'             => 'active',
            'is_delegate'        => $this->input('is_delegate') ? 1 : 0,
            'notes'              => $this->input('notes'),
        ], $userId);

        Flash::set('success', 'Socio añadido correctamente.');
        $this->redirect('/oficina/mi-club');
    }

    /** GET /oficina/mi-club/socios/{id}/editar */
    public function editarSocio(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $member = $this->memberModel->findById((int) $p['id']);
        if (!$member || (int) $member['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Socio no encontrado.');
            $this->redirect('/oficina/mi-club');
            return;
        }

        $this->render('oficina/socio-form', ['club' => $club, 'member' => $member, 'errors' => []]);
    }

    /** POST /oficina/mi-club/socios/{id}/actualizar */
    public function actualizarSocio(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $member = $this->memberModel->findById((int) $p['id']);
        if (!$member || (int) $member['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Socio no encontrado.');
            $this->redirect('/oficina/mi-club');
            return;
        }

        $name = trim($this->input('name', ''));
        if (\strlen($name) < 2) {
            $this->render('oficina/socio-form', [
                'club'   => $club,
                'member' => $member,
                'errors' => ['El nombre es obligatorio (mínimo 2 caracteres).'],
            ]);
            return;
        }

        $expiresRaw = $this->input('license_expires_at', '');

        $this->memberModel->updateMember((int) $p['id'], [
            'name'               => $name,
            'email'              => $this->input('email'),
            'phone'              => $this->input('phone'),
            'license_number'     => $this->input('license_number'),
            'license_type'       => $this->input('license_type'),
            'license_expires_at' => $expiresRaw ?: null,
            'is_delegate'        => $this->input('is_delegate') ? 1 : 0,
            'notes'              => $this->input('notes'),
        ]);

        Flash::set('success', 'Socio actualizado correctamente.');
        $this->redirect('/oficina/mi-club');
    }

    /** POST /oficina/mi-club/socios/{id}/eliminar */
    public function eliminarSocio(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $member = $this->memberModel->findById((int) $p['id']);
        if (!$member || (int) $member['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Socio no encontrado.');
            $this->redirect('/oficina/mi-club');
            return;
        }

        $this->memberModel->remove((int) $p['id']);
        Flash::set('success', 'Socio eliminado.');
        $this->redirect('/oficina/mi-club');
    }

    /** POST /oficina/mi-club/socios/{id}/aprobar */
    public function aprobarSocio(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $member = $this->memberModel->findById((int) $p['id']);
        if (!$member || (int) $member['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Socio no encontrado.');
            $this->redirect('/oficina/mi-club');
            return;
        }

        $this->memberModel->approve((int) $p['id']);
        Flash::set('success', 'Socio aprobado.');
        $this->redirect('/oficina/mi-club');
    }

    /** POST /oficina/mi-club/socios/{id}/suspender */
    public function suspenderSocio(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $member = $this->memberModel->findById((int) $p['id']);
        if (!$member || (int) $member['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Socio no encontrado.');
            $this->redirect('/oficina/mi-club');
            return;
        }

        $this->memberModel->suspend((int) $p['id']);
        Flash::set('success', 'Socio suspendido.');
        $this->redirect('/oficina/mi-club');
    }

    // ── Calendario de eventos ───────────────────────────────────

    /** GET /oficina/mi-club/eventos */
    public function calendario(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $clubId = (int) $club['id'];
        $this->render('oficina/calendario', [
            'club'     => $club,
            'upcoming' => $this->eventModel->upcoming($clubId),
            'past'     => $this->eventModel->past($clubId),
        ]);
    }

    /** GET /oficina/mi-club/eventos/nuevo */
    public function nuevoEvento(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $this->render('oficina/evento-form', ['club' => $club, 'event' => null, 'errors' => []]);
    }

    /** POST /oficina/mi-club/eventos/nuevo */
    public function guardarEvento(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        [$errors, $data] = $this->validateEventInput();

        if ($errors) {
            $this->render('oficina/evento-form', ['club' => $club, 'event' => null, 'errors' => $errors]);
            return;
        }

        $data['club_id']    = $club['id'];
        $data['created_by'] = $userId;
        $this->eventModel->add($data);

        Flash::set('success', 'Evento creado correctamente.');
        $this->redirect('/oficina/mi-club/eventos');
    }

    /** GET /oficina/mi-club/eventos/{id}/editar */
    public function editarEvento(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $event = $this->eventModel->findById((int) $p['id']);
        if (!$event || (int) $event['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Evento no encontrado.');
            $this->redirect('/oficina/mi-club/eventos');
            return;
        }

        $this->render('oficina/evento-form', ['club' => $club, 'event' => $event, 'errors' => []]);
    }

    /** POST /oficina/mi-club/eventos/{id}/actualizar */
    public function actualizarEvento(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $event = $this->eventModel->findById((int) $p['id']);
        if (!$event || (int) $event['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Evento no encontrado.');
            $this->redirect('/oficina/mi-club/eventos');
            return;
        }

        [$errors, $data] = $this->validateEventInput();

        if ($errors) {
            $this->render('oficina/evento-form', ['club' => $club, 'event' => $event, 'errors' => $errors]);
            return;
        }

        $this->eventModel->updateEvent((int) $p['id'], $data);
        Flash::set('success', 'Evento actualizado correctamente.');
        $this->redirect('/oficina/mi-club/eventos');
    }

    /** POST /oficina/mi-club/eventos/{id}/eliminar */
    public function eliminarEvento(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $event = $this->eventModel->findById((int) $p['id']);
        if (!$event || (int) $event['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Evento no encontrado.');
            $this->redirect('/oficina/mi-club/eventos');
            return;
        }

        $this->eventModel->remove((int) $p['id']);
        Flash::set('success', 'Evento eliminado.');
        $this->redirect('/oficina/mi-club/eventos');
    }

    /** Shared input validation for create + update */
    private function validateEventInput(): array
    {
        $errors = [];
        $title  = trim($this->input('title', ''));
        if (\strlen($title) < 2) {
            $errors[] = 'El título es obligatorio (mínimo 2 caracteres).';
        }

        $startsAt = $this->input('starts_at', '');
        if (!$startsAt) {
            $errors[] = 'La fecha de inicio es obligatoria.';
        }

        $endsAt = $this->input('ends_at', '') ?: null;
        if ($endsAt && $startsAt && $endsAt < $startsAt) {
            $errors[] = 'La fecha de fin no puede ser anterior a la de inicio.';
        }

        $allowed = ['tirada', 'carrera', 'veda', 'reunion', 'otro'];
        $type    = $this->input('type', 'otro');
        if (!\in_array($type, $allowed, true)) {
            $type = 'otro';
        }

        $data = [
            'title'       => $title,
            'type'        => $type,
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
            'location'    => $this->input('location') ?: null,
            'description' => $this->input('description') ?: null,
        ];

        return [$errors, $data];
    }

    // ── Bóveda de documentos ────────────────────────────────────

    /** GET /oficina/mi-club/documentos */
    public function boveda(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $this->render('oficina/boveda', [
            'club' => $club,
            'docs' => $this->docModel->listByClub((int) $club['id']),
        ]);
    }

    /** GET /oficina/mi-club/documentos/subir */
    public function subirDocumento(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $this->render('oficina/documento-form', ['club' => $club, 'errors' => []]);
    }

    /** POST /oficina/mi-club/documentos/subir */
    public function guardarDocumento(): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $errors = [];
        $title  = trim($this->input('title', ''));
        if (\strlen($title) < 2) {
            $errors[] = 'El título es obligatorio (mínimo 2 caracteres).';
        }

        $fileOk = !empty($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK;
        if (!$fileOk) {
            $errors[] = 'Debes seleccionar un archivo.';
        }

        if ($fileOk && !$errors) {
            $file  = $_FILES['document'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']) ?: '';

            if ($file['size'] > self::DOC_MAX_BYTES) {
                $errors[] = 'El archivo no puede superar los 20 MB.';
            } elseif (!\in_array($mime, self::DOC_ALLOWED_MIME, true)) {
                $errors[] = 'Tipo de archivo no permitido. Se aceptan: PDF, JPEG, PNG, DOC, DOCX.';
            }
        }

        if ($errors) {
            $this->render('oficina/documento-form', ['club' => $club, 'errors' => $errors]);
            return;
        }

        $file    = $_FILES['document'];
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $stored  = sha1(uniqid('doc_', true)) . '_' . time() . '.' . $ext;

        if (R2Storage::enabled()) {
            $r2Key    = 'private/club-docs/' . (int) $club['id'] . '/' . $stored;
            $filePath = R2Storage::putPrivate($r2Key, $file['tmp_name'], $mime);
        } else {
            $dir = BASE_PATH . '/storage/club-docs/' . (int) $club['id'];
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $stored)) {
                Flash::set('error', 'Error al guardar el archivo. Inténtalo de nuevo.');
                $this->redirect('/oficina/mi-club/documentos/subir');
                return;
            }
            $filePath = 'club-docs/' . (int) $club['id'] . '/' . $stored;
        }

        $expiresRaw = $this->input('expires_at', '');

        $this->docModel->add([
            'club_id'       => $club['id'],
            'title'         => $title,
            'category'      => $this->input('category', 'otro'),
            'file_path'     => $filePath,
            'original_name' => $file['name'],
            'mime_type'     => $mime,
            'file_size'     => (int) $file['size'],
            'expires_at'    => $expiresRaw ?: null,
            'notes'         => $this->input('notes'),
            'uploaded_by'   => $userId,
        ]);

        Flash::set('success', 'Documento subido correctamente.');
        $this->redirect('/oficina/mi-club/documentos');
    }

    /** GET /oficina/mi-club/documentos/{id}/descargar */
    public function descargarDocumento(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $doc = $this->docModel->findById((int) $p['id']);
        if (!$doc || (int) $doc['club_id'] !== (int) $club['id']) {
            http_response_code(404);
            echo 'Documento no encontrado.';
            return;
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
            echo 'Archivo no encontrado en el servidor.';
            return;
        }

        $mime     = $doc['mime_type'] ?: 'application/octet-stream';
        $filename = $doc['original_name'] ?: basename($doc['file_path']);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($absPath));
        header('Cache-Control: private, no-cache');
        readfile($absPath);
        exit;
    }

    /** POST /oficina/mi-club/documentos/{id}/eliminar */
    public function eliminarDocumento(array $p = []): void
    {
        $userId = $this->currentUserId();
        $club   = $this->clubModel->findByPresident($userId);
        if (!$club) { $this->redirect('/oficina'); return; }

        $doc = $this->docModel->findById((int) $p['id']);
        if (!$doc || (int) $doc['club_id'] !== (int) $club['id']) {
            Flash::set('error', 'Documento no encontrado.');
            $this->redirect('/oficina/mi-club/documentos');
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

        $this->docModel->remove((int) $p['id']);
        Flash::set('success', 'Documento eliminado.');
        $this->redirect('/oficina/mi-club/documentos');
    }

    /** GET /oficina/solicitar-club — form to request a new club */
    public function showSolicitarClub(): void
    {
        $this->render('oficina/solicitar-club');
    }

    /** POST /oficina/solicitar-club — submit club request */
    public function solicitarClub(): void
    {
        $userId = $this->currentUserId();
        $errors = [];

        $name = $this->input('name');
        $type = $this->input('type', 'club');

        if (!$name || strlen($name) < 3) {
            $errors[] = 'El nombre del club debe tener al menos 3 caracteres.';
        }

        $allowed = ['club', 'coto', 'federacion', 'otro'];
        if (!in_array($type, $allowed, true)) {
            $type = 'club';
        }

        // Check if user already has a club pending or active
        $existing = $this->clubModel->findByPresident($userId);
        if ($existing) {
            $errors[] = 'Ya tienes un club registrado o pendiente de aprobación.';
        }

        if ($errors) {
            $this->render('oficina/solicitar-club', ['errors' => $errors, 'old' => $_POST]);
            return;
        }

        $this->clubModel->create([
            'name'                 => $name,
            'type'                 => $type,
            'province'             => $this->input('province'),
            'autonomous_community' => $this->input('autonomous_community'),
            'country'              => $this->input('country', 'España'),
            'contact_email'        => $this->input('contact_email'),
            'contact_phone'        => $this->input('contact_phone'),
            'website'              => $this->input('website'),
            'description'          => $this->input('description'),
        ], $userId);

        $_SESSION['flash_success'] = 'Solicitud enviada. El administrador revisará tu club en breve.';
        $this->redirect('/oficina');
    }
}
