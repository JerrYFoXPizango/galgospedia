<?php

namespace Controllers;

use Models\Training;
use Helpers\Flash;
use Helpers\Csrf;

class TrainingController extends BaseController
{
    private function getDog(string $slug, int $userId): array
    {
        $db   = \Config\Database::pdo();
        $stmt = $db->prepare('SELECT * FROM dogs WHERE slug = ? AND owner_user_id = ?');
        $stmt->execute([$slug, $userId]);
        $dog  = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$dog) {
            http_response_code(404);
            die('Galgo no encontrado.');
        }
        return $dog;
    }

    // GET /apps/entrenamiento
    public function index(): void
    {
        $userId  = \Services\AuthService::currentUserId();
        $summary = Training::summaryByDog($userId);

        // Añadir estado de overtraining a cada galgo del resumen
        foreach ($summary as &$dog) {
            $ot = Training::overtrain((int)$dog['id'], $userId);
            $dog['ot_status'] = $ot['status'];
            $dog['ot_label']  = $ot['label'];
            $dog['week_km']   = round($dog['week_m'] / 1000, 1);
            $cfg              = Training::getConfig((int)$dog['id']);
            $dog['max_weekly_km'] = $cfg['max_weekly_km'];
        }
        unset($dog);

        $this->render('apps/entrenamiento/index', compact('summary'));
    }

    // GET /apps/entrenamiento/{slug}
    public function show(array $p = []): void
    {
        $slug    = $p['slug'] ?? '';
        $userId  = \Services\AuthService::currentUserId();
        $dog     = $this->getDog($slug, $userId);
        $dogId   = (int)$dog['id'];

        $sessions   = Training::forDog($dogId, $userId);
        $overtrain  = Training::overtrain($dogId, $userId);
        $weeks      = Training::recentWeeks($dogId, $userId, 6);
        $monthStats = Training::monthStats($dogId, $userId);
        $config     = Training::getConfig($dogId);
        $lastComp   = Training::lastCompetitionDate($dogId, $userId);
        $lastDays   = Training::daysSinceLastSession($dogId, $userId);
        $favTerrain = Training::favoriteTerrain($dogId, $userId);

        $daysAfterComp = null;
        if ($lastComp) {
            $daysAfterComp = (int)(new \DateTime())->diff(new \DateTime($lastComp))->days;
        }

        $this->render('apps/entrenamiento/show', compact(
            'dog', 'sessions', 'overtrain', 'weeks',
            'monthStats', 'config', 'lastComp', 'lastDays',
            'daysAfterComp', 'favTerrain'
        ));
    }

    // GET /apps/entrenamiento/{slug}/nuevo
    public function create(string $slug): void
    {
        $userId = \Services\AuthService::currentUserId();
        $dog    = $this->getDog($slug, $userId);
        $errors = [];
        $old    = [];
        $record = null;
        $this->render('apps/entrenamiento/form', compact('dog', 'errors', 'old', 'record'));
    }

    // POST /apps/entrenamiento/{slug}/nuevo
    public function store(string $slug): void
    {
        $userId = \Services\AuthService::currentUserId();
        $dog    = $this->getDog($slug, $userId);
        Csrf::verify();

        $data   = $_POST;
        $errors = $this->validateInput($data);

        // Convertir distancia a metros según unidad seleccionada
        $data['distance_m'] = $this->toMeters($data);

        if (empty($errors)) {
            Training::addSession((int)$dog['id'], $userId, $data);
            Flash::set('success', 'Sesión registrada correctamente.');
            header('Location: /apps/entrenamiento/' . $dog['slug']);
            exit;
        }

        $old    = $data;
        $record = null;
        $this->render('apps/entrenamiento/form', compact('dog', 'errors', 'old', 'record'));
    }

    // GET /apps/entrenamiento/sesion/{id}/editar
    public function edit(array $p = []): void
    {
        $id     = (int)($p['id'] ?? 0);
        $userId = \Services\AuthService::currentUserId();
        $record = Training::findOwned($id, $userId);
        if (!$record) { http_response_code(404); die('Sesión no encontrada.'); }

        $dog    = $this->getDogById((int)$record['dog_id'], $userId);
        $errors = [];
        $old    = $record;
        $this->render('apps/entrenamiento/form', compact('dog', 'errors', 'old', 'record'));
    }

    // POST /apps/entrenamiento/sesion/{id}/actualizar
    public function update(array $p = []): void
    {
        $id     = (int)($p['id'] ?? 0);
        $userId = \Services\AuthService::currentUserId();
        $record = Training::findOwned($id, $userId);
        if (!$record) { http_response_code(404); die('Sesión no encontrada.'); }

        Csrf::verify();
        $data   = $_POST;
        $errors = $this->validateInput($data);
        $data['distance_m'] = $this->toMeters($data);

        if (empty($errors)) {
            Training::updateSession($id, $data);
            Flash::set('success', 'Sesión actualizada.');
            $dog = $this->getDogById((int)$record['dog_id'], $userId);
            header('Location: /apps/entrenamiento/' . $dog['slug']);
            exit;
        }

        $dog = $this->getDogById((int)$record['dog_id'], $userId);
        $old = $data;
        $this->render('apps/entrenamiento/form', compact('dog', 'errors', 'old', 'record'));
    }

    // POST /apps/entrenamiento/sesion/{id}/eliminar
    public function destroy(array $p = []): void
    {
        $id     = (int)($p['id'] ?? 0);
        $userId = \Services\AuthService::currentUserId();
        $record = Training::findOwned($id, $userId);
        if (!$record) { http_response_code(404); die('Sesión no encontrada.'); }

        Csrf::verify();
        $dog = $this->getDogById((int)$record['dog_id'], $userId);
        Training::removeSession($id);
        Flash::set('success', 'Sesión eliminada.');
        header('Location: /apps/entrenamiento/' . $dog['slug']);
        exit;
    }

    // GET /apps/entrenamiento/{slug}/configurar
    public function config(string $slug): void
    {
        $userId = \Services\AuthService::currentUserId();
        $dog    = $this->getDog($slug, $userId);
        $cfg    = Training::getConfig((int)$dog['id']);
        $errors = [];
        $this->render('apps/entrenamiento/config', compact('dog', 'cfg', 'errors'));
    }

    // POST /apps/entrenamiento/{slug}/configurar
    public function saveConfig(string $slug): void
    {
        $userId = \Services\AuthService::currentUserId();
        $dog    = $this->getDog($slug, $userId);
        Csrf::verify();

        $maxKm   = (float)($_POST['max_weekly_km'] ?? 30);
        $maxCons = (int)($_POST['max_consecutive_high'] ?? 3);
        $restDays = (int)($_POST['rest_days_after_competition'] ?? 2);

        $errors = [];
        if ($maxKm <= 0 || $maxKm > 200)  $errors[] = 'Km semanales máximos debe estar entre 1 y 200.';
        if ($maxCons < 1 || $maxCons > 14) $errors[] = 'Días consecutivos debe estar entre 1 y 14.';
        if ($restDays < 0 || $restDays > 30) $errors[] = 'Días de descanso debe estar entre 0 y 30.';

        if (empty($errors)) {
            Training::saveConfig((int)$dog['id'], $userId, $maxKm, $maxCons, $restDays);
            Flash::set('success', 'Configuración guardada.');
            header('Location: /apps/entrenamiento/' . $dog['slug']);
            exit;
        }

        $cfg = ['max_weekly_km' => $maxKm, 'max_consecutive_high' => $maxCons, 'rest_days_after_competition' => $restDays];
        $this->render('apps/entrenamiento/config', compact('dog', 'cfg', 'errors'));
    }

    // ─── Helpers privados ──────────────────────────────────────────────────────

    private function getDogById(int $dogId, int $userId): array
    {
        $db   = \Config\Database::pdo();
        $stmt = $db->prepare('SELECT * FROM dogs WHERE id = ? AND owner_user_id = ?');
        $stmt->execute([$dogId, $userId]);
        $dog  = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$dog) die('Galgo no encontrado.');
        return $dog;
    }

    private function validateInput(array $data): array
    {
        $errors = [];
        $validTypes = ['run_free','run_hare','walk','track','active_rest','competition'];
        if (empty($data['type']) || !in_array($data['type'], $validTypes, true)) {
            $errors[] = 'Selecciona un tipo de sesión válido.';
        }
        if (empty($data['date']) || !strtotime($data['date'])) {
            $errors[] = 'La fecha no es válida.';
        }
        $validIntensities = ['low','medium','high'];
        if (empty($data['intensity']) || !in_array($data['intensity'], $validIntensities, true)) {
            $errors[] = 'Selecciona una intensidad.';
        }
        return $errors;
    }

    /** Convierte la distancia del formulario a metros */
    private function toMeters(array $data): ?int
    {
        $val  = trim($data['distance_value'] ?? '');
        $unit = $data['distance_unit'] ?? 'm';
        if ($val === '' || $val === null) return null;
        $num = (float)$val;
        if ($num <= 0) return null;
        return $unit === 'km' ? (int)round($num * 1000) : (int)$num;
    }
}
