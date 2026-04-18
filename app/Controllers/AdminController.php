<?php
declare(strict_types=1);
namespace Controllers;

use Models\{User, Dog, Stallion, Broodmare, Club, Tournament};
use Helpers\{Csrf, Flash};
use Config\Database;
use Services\LicenseAlertService;

class AdminController extends BaseController
{
    public function dashboard(array $p = []): void
    {
        $db    = Database::pdo();
        $stats = [
            'dogs'          => (int) $db->query("SELECT COUNT(*) FROM dogs")->fetchColumn(),
            'users'         => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'stallions'     => (int) $db->query("SELECT COUNT(*) FROM stallions WHERE is_active=1")->fetchColumn(),
            'broodmares'    => (int) $db->query("SELECT COUNT(*) FROM broodmares WHERE is_active=1")->fetchColumn(),
            'clubs_pending' => (int) $db->query("SELECT COUNT(*) FROM clubs WHERE status='pending'")->fetchColumn(),
            'clubs_active'  => (int) $db->query("SELECT COUNT(*) FROM clubs WHERE status='active'")->fetchColumn(),
            'tournaments'   => (int) $db->query("SELECT COUNT(*) FROM tournaments WHERE status='published'")->fetchColumn(),
        ];
        $this->render('admin/dashboard', compact('stats'));
    }

    public function dogs(array $p = []): void
    {
        $page   = (int) $this->query('page', 1);
        $result = (new Dog())->directory($page, 30);
        $this->render('admin/dogs', [
            'dogs'  => $result['data'],
            'total' => $result['total'],
            'page'  => $page,
        ]);
    }

    public function users(array $p = []): void
    {
        $page   = (int) $this->query('page', 1);
        $result = (new User())->paginate($page, 30);
        $this->render('admin/users', [
            'users' => $result['data'],
            'total' => $result['total'],
            'page'  => $page,
        ]);
    }

    public function toggleStallion(array $p = []): void
    {
        Csrf::verify();
        $dog = (new Dog())->findById((int) $p['id']);
        if (!$dog) { Flash::set('error', 'Perro no encontrado.'); $this->redirect('/admin'); }

        $stallion = new Stallion();
        if ($stallion->forDog($dog['id'])) {
            $stallion->toggle($dog['id']);
        } else {
            $stallion->register($dog['id']);
        }
        Flash::set('success', 'Semental actualizado.');
        $this->redirect('/admin/galgos');
    }

    public function toggleBroodmare(array $p = []): void
    {
        Csrf::verify();
        $dog = (new Dog())->findById((int) $p['id']);
        if (!$dog) { Flash::set('error', 'Perro no encontrado.'); $this->redirect('/admin'); }

        $broodmare = new Broodmare();
        if ($broodmare->forDog($dog['id'])) {
            $broodmare->toggle($dog['id']);
        } else {
            $broodmare->register($dog['id']);
        }
        Flash::set('success', 'Reproductora actualizada.');
        $this->redirect('/admin/galgos');
    }

    public function changeRole(array $p = []): void
    {
        Csrf::verify();
        $role = $this->input('role', 'user');
        if (!in_array($role, ['user', 'moderator', 'president', 'admin'])) {
            Flash::set('error', 'Rol inválido.');
            $this->redirect('/admin/usuarios');
        }
        (new User())->setRole((int) $p['id'], $role);
        Flash::set('success', 'Rol actualizado.');
        $this->redirect('/admin/usuarios');
    }

    // ── Clubs ────────────────────────────────────────────────

    public function clubs(array $p = []): void
    {
        $page   = max(1, (int) $this->query('page', 1));
        $result = (new Club())->listAll($page, 30);
        $this->render('admin/clubs', [
            'clubs' => $result['data'],
            'total' => $result['total'],
            'page'  => $page,
        ]);
    }

    public function approveClub(array $p = []): void
    {
        Csrf::verify();
        $clubId  = (int) $p['id'];
        $club    = (new Club())->findById($clubId);
        if (!$club) {
            Flash::set('error', 'Club no encontrado.');
            $this->redirect('/admin/clubs');
        }

        $clubModel = new Club();
        $userModel = new User();
        $adminId   = $this->currentUserId();

        // Set president_user_id = created_by if not already set
        $presidentId = $club['president_user_id'] ?? $club['created_by'];
        $clubModel->approve($clubId, $adminId);
        if ($presidentId) {
            $clubModel->setPresident($clubId, (int) $presidentId);
            $userModel->setRole((int) $presidentId, 'president');
        }

        Flash::set('success', 'Club aprobado y presidente asignado.');
        $this->redirect('/admin/clubs');
    }

    public function suspendClub(array $p = []): void
    {
        Csrf::verify();
        (new Club())->suspend((int) $p['id']);
        Flash::set('success', 'Club suspendido.');
        $this->redirect('/admin/clubs');
    }

    // ── Alertas de licencia ──────────────────────────────────

    public function alertas(): void
    {
        $service = new LicenseAlertService();
        $this->render('admin/alertas', [
            'pending' => $service->getPending(),
            'history' => $service->getHistory(60),
        ]);
    }

    // ── Torneos ──────────────────────────────────────────────

    public function tournaments(array $p = []): void
    {
        $page       = max(1, (int) $this->query('page', 1));
        $discipline = $this->query('disciplina', '');
        $status     = $this->query('estado', '');
        $result     = (new Tournament())->adminListing($page, 30, compact('discipline', 'status'));
        $this->render('admin/tournaments', [
            'tournaments' => $result['data'],
            'total'       => $result['total'],
            'page'        => $page,
            'discipline'  => $discipline,
            'status'      => $status,
        ]);
    }

    public function updateTournamentStatus(array $p = []): void
    {
        Csrf::verify();
        $newStatus = $this->input('status', '');
        if (!\in_array($newStatus, ['published', 'draft', 'cancelled'])) {
            Flash::set('error', 'Estado no válido.');
            $this->redirect('/admin/torneos');
        }
        (new Tournament())->updateStatus((int) $p['id'], $newStatus);
        Flash::set('success', 'Estado actualizado.');
        $this->redirect('/admin/torneos');
    }

    public function enviarAlertas(): void
    {
        Csrf::verify();
        $dryRun  = (bool) $this->input('dry_run', false);
        $service = new LicenseAlertService();
        $stats   = $service->run($dryRun);

        $msg = $dryRun
            ? "Simulación completada: {$stats['processed']} alertas pendientes en {$stats['clubs']} clubs."
            : "Alertas enviadas: {$stats['processed']} socios en {$stats['clubs']} clubs."
                . ($stats['errors'] > 0 ? " ({$stats['errors']} errores de envío)" : '');

        Flash::set($stats['errors'] > 0 ? 'error' : 'success', $msg);
        $this->redirect('/admin/alertas');
    }

    // ── Importador CSV ───────────────────────────────────────

    public function showImport(array $p = []): void
    {
        $this->render('admin/import', ['preview' => null, 'tmpFile' => null]);
    }

    public function previewImport(array $p = []): void
    {
        Csrf::verify();

        $file = $_FILES['csv'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Flash::set('error', 'Error al subir el archivo CSV.');
            $this->redirect('/admin/importar');
        }

        // Validate MIME / extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            Flash::set('error', 'Sólo se aceptan archivos .csv');
            $this->redirect('/admin/importar');
        }

        // Move to temp dir
        $tmpFile = sys_get_temp_dir() . '/galgos_import_' . bin2hex(random_bytes(8)) . '.csv';
        move_uploaded_file($file['tmp_name'], $tmpFile);

        // Parse CSV — detect BOM and delimiter
        $rows    = $this->parseCsv($tmpFile);
        $total   = count($rows);
        $preview = array_slice($rows, 0, 8);

        // Count duplicates for info
        $dryStats = (new Dog())->bulkImport($rows, $this->currentUserId(), true);

        $_SESSION['import_tmp'] = $tmpFile;

        $this->render('admin/import', compact('preview', 'total', 'dryStats', 'tmpFile'));
    }

    public function runImport(array $p = []): void
    {
        Csrf::verify();
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $tmpFile = $_SESSION['import_tmp'] ?? null;
        if (!$tmpFile || !file_exists($tmpFile)) {
            Flash::set('error', 'Archivo temporal no encontrado. Vuelve a subir el CSV.');
            $this->redirect('/admin/importar');
        }

        try {
            $rows  = $this->parseCsv($tmpFile);
            $stats = (new Dog())->bulkImport($rows, $this->currentUserId(), false);

            @unlink($tmpFile);
            unset($_SESSION['import_tmp']);

            Flash::set('success',
                "Importación completada: {$stats['inserted']} galgos nuevos, "
                . "{$stats['linked']} parentescos enlazados, "
                . "{$stats['skipped']} omitidos (ya existían o sin nombre)."
            );
        } catch (\Throwable $e) {
            // Log full error and show friendly message with detail for admin
            $logFile = APP_PATH . '/../storage/logs/import_error.log';
            @file_put_contents(
                $logFile,
                '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n",
                FILE_APPEND
            );
            // Keep tmp file so admin can retry
            Flash::set('error',
                'Error durante la importación: ' . htmlspecialchars($e->getMessage()) . '. '
                . 'La base de datos quedó sin cambios (rollback automático). '
                . 'Puedes volver a intentarlo.'
            );
        }

        $this->redirect('/admin/importar');
    }

    /** Parse a UTF-8 (with or without BOM) CSV file into assoc rows */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return [];

        // Strip BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        // Sniff delimiter on first line
        $first = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") fread($handle, 3);
        $delimiter = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';

        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) { fclose($handle); return []; }
        $headers = array_map('trim', $headers);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($handle);
        return $rows;
    }
}
