<?php
declare(strict_types=1);
namespace Controllers;

use Models\{VetRecord, Dog};
use Helpers\{Csrf, Flash};
use Services\AuthService;

class VetController extends BaseController
{
    private VetRecord $records;
    private Dog $dogs;

    public function __construct()
    {
        $this->records = new VetRecord();
        $this->dogs    = new Dog();
    }

    // ── Hub: todos mis galgos con semáforo ────────────────────

    public function index(): void
    {
        AuthService::guard();
        $userId  = $this->currentUserId();
        $summary = $this->records->summaryByDog($userId);

        $this->render('apps/veterinario/index', [
            'summary' => $summary,
        ]);
    }

    // ── Historial de un galgo ─────────────────────────────────

    public function show(array $p = []): void
    {
        AuthService::guard();
        $userId = $this->currentUserId();
        $dog    = $this->dogs->findBySlug($p['slug'] ?? '');

        if (!$dog || (int)$dog['owner_user_id'] !== $userId) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        $records        = $this->records->forDog((int)$dog['id'], $userId);
        $activeInjuries = $this->records->activeInjuries((int)$dog['id'], $userId);
        $upcoming       = $this->records->upcomingDue((int)$dog['id'], $userId);
        $overdue        = $this->records->overdue((int)$dog['id'], $userId);

        $this->render('apps/veterinario/show', [
            'dog'           => $dog,
            'records'       => $records,
            'activeInjuries'=> $activeInjuries,
            'upcoming'      => $upcoming,
            'overdue'       => $overdue,
        ]);
    }

    // ── Formulario nuevo registro ─────────────────────────────

    public function create(array $p = []): void
    {
        AuthService::guard();
        $userId = $this->currentUserId();
        $dog    = $this->dogs->findBySlug($p['slug'] ?? '');

        if (!$dog || (int)$dog['owner_user_id'] !== $userId) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        $this->render('apps/veterinario/form', [
            'dog'    => $dog,
            'record' => null,
            'csrf'   => Csrf::generate(),
        ]);
    }

    // ── Guardar nuevo registro ────────────────────────────────

    public function store(array $p = []): void
    {
        AuthService::guard();
        if (!Csrf::verify($this->input('_csrf'))) {
            Flash::set('error', 'Token de seguridad inválido.');
            $this->redirect('/apps/veterinario/' . ($p['slug'] ?? ''));
            return;
        }

        $userId = $this->currentUserId();
        $dog    = $this->dogs->findBySlug($p['slug'] ?? '');

        if (!$dog || (int)$dog['owner_user_id'] !== $userId) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        $errors = $this->validateInput();
        if ($errors) {
            $this->render('apps/veterinario/form', [
                'dog'    => $dog,
                'record' => null,
                'errors' => $errors,
                'old'    => $_POST,
                'csrf'   => Csrf::generate(),
            ]);
            return;
        }

        $this->records->addRecord($_POST, (int)$dog['id'], $userId);
        Flash::set('success', 'Registro añadido correctamente.');
        $this->redirect('/apps/veterinario/' . $dog['slug']);
    }

    // ── Formulario editar ─────────────────────────────────────

    public function edit(array $p = []): void
    {
        AuthService::guard();
        $userId = $this->currentUserId();
        $record = $this->records->findOwned((int)($p['id'] ?? 0), $userId);

        if (!$record) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $dog = $this->dogs->find((int)$record['dog_id']);

        $this->render('apps/veterinario/form', [
            'dog'    => $dog,
            'record' => $record,
            'csrf'   => Csrf::generate(),
        ]);
    }

    // ── Actualizar registro ───────────────────────────────────

    public function update(array $p = []): void
    {
        AuthService::guard();
        if (!Csrf::verify($this->input('_csrf'))) {
            Flash::set('error', 'Token de seguridad inválido.');
            $this->redirect('/apps/veterinario');
            return;
        }

        $userId = $this->currentUserId();
        $record = $this->records->findOwned((int)($p['id'] ?? 0), $userId);

        if (!$record) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $errors = $this->validateInput();
        if ($errors) {
            $dog = $this->dogs->find((int)$record['dog_id']);
            $this->render('apps/veterinario/form', [
                'dog'    => $dog,
                'record' => $record,
                'errors' => $errors,
                'old'    => $_POST,
                'csrf'   => Csrf::generate(),
            ]);
            return;
        }

        $this->records->updateRecord((int)$record['id'], $_POST);
        Flash::set('success', 'Registro actualizado.');
        $dog = $this->dogs->find((int)$record['dog_id']);
        $this->redirect('/apps/veterinario/' . $dog['slug']);
    }

    // ── Eliminar registro ─────────────────────────────────────

    public function destroy(array $p = []): void
    {
        AuthService::guard();
        if (!Csrf::verify($this->input('_csrf'))) {
            Flash::set('error', 'Token de seguridad inválido.');
            $this->redirect('/apps/veterinario');
            return;
        }

        $userId = $this->currentUserId();
        $record = $this->records->findOwned((int)($p['id'] ?? 0), $userId);

        if (!$record) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $dog = $this->dogs->find((int)$record['dog_id']);
        $this->records->removeRecord((int)$record['id']);
        Flash::set('success', 'Registro eliminado.');
        $this->redirect('/apps/veterinario/' . ($dog['slug'] ?? ''));
    }

    // ── Validación ────────────────────────────────────────────

    private function validateInput(): array
    {
        $errors = [];
        $type  = $this->input('type');
        $title = trim($this->input('title') ?? '');
        $date  = $this->input('date');

        $validTypes = ['vaccine','deworming','injury','visit','weight'];
        if (!in_array($type, $validTypes)) {
            $errors['type'] = 'Tipo no válido.';
        }
        if (strlen($title) < 2) {
            $errors['title'] = 'El título es obligatorio (mín. 2 caracteres).';
        }
        if (!$date || !strtotime($date)) {
            $errors['date'] = 'La fecha es obligatoria.';
        }
        return $errors;
    }
}
