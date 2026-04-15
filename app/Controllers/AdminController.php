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
}
