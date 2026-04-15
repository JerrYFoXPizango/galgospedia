<?php
declare(strict_types=1);
namespace Controllers;

use Models\Tournament;
use Helpers\{Csrf, Flash};
use Services\{AuthService, ImageProcessor};

class TournamentController extends BaseController
{
    private Tournament $tournaments;

    public function __construct()
    {
        $this->tournaments = new Tournament();
    }

    public function index(): void
    {
        $page       = max(1, (int) $this->query('page', 1));
        $discipline = $this->query('disciplina', '');
        $q          = $this->query('q', '');
        $upcoming   = $this->query('futuros', '1');

        $result = $this->tournaments->listing($page, 15, [
            'discipline'    => $discipline,
            'q'             => $q,
            'upcoming_only' => $upcoming === '1',
        ]);

        $this->render('tournaments/index', [
            'tournaments' => $result['data'],
            'total'       => $result['total'],
            'page'        => $page,
            'perPage'      => $result['perPage'],
            'discipline'  => $discipline,
            'q'           => $q,
            'upcoming'    => $upcoming,
        ]);
    }

    public function show(array $p = []): void
    {
        $tournament = $this->tournaments->findBySlug($p['slug']);
        if (!$tournament) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $canEdit = AuthService::isLoggedIn() &&
                   ((int)$tournament['created_by'] === $this->currentUserId() || AuthService::isAdmin());

        $hasCoords = !empty($tournament['location_lat']) && !empty($tournament['location_lng']);
        $extraHead = $hasCoords
            ? '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">'
            : '';
        $extraScripts = $hasCoords
            ? '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
               <script src="/js/tournament-map.js"></script>'
            : '';

        $this->render('tournaments/show', compact('tournament', 'canEdit', 'extraHead', 'extraScripts', 'hasCoords'));
    }

    public function create(): void
    {
        AuthService::guard();
        $extraHead    = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">';
        $extraScripts = '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                         <script src="/js/tournament-map.js"></script>';
        $this->render('tournaments/create', [
            'tournament'   => null,
            'errors'       => [],
            'extraHead'    => $extraHead,
            'extraScripts' => $extraScripts,
        ]);
    }

    public function store(): void
    {
        AuthService::guard();
        Csrf::verify();

        [$errors, $data] = $this->validateInput();

        if ($errors) {
            $extraHead    = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">';
            $extraScripts = '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                             <script src="/js/tournament-map.js"></script>';
            $this->render('tournaments/create', [
                'tournament'   => $_POST,
                'errors'       => $errors,
                'extraHead'    => $extraHead,
                'extraScripts' => $extraScripts,
            ]);
            return;
        }

        // Poster upload
        if (!empty($_FILES['poster']['tmp_name']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            try {
                $data['poster'] = (new ImageProcessor())->processTournamentPoster($_FILES['poster']);
            } catch (\RuntimeException $e) {
                Flash::set('error', 'Error al subir el cartel: ' . $e->getMessage());
            }
        }

        $id  = $this->tournaments->create($data, $this->currentUserId());
        $row = $this->tournaments->findById($id);
        Flash::set('success', 'Torneo publicado correctamente.');
        $this->redirect('/torneos/' . $row['slug']);
    }

    public function edit(array $p = []): void
    {
        AuthService::guard();
        $tournament = $this->tournaments->findBySlug($p['slug']);
        if (!$tournament) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->canEdit($tournament);

        $extraHead    = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">';
        $extraScripts = '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                         <script src="/js/tournament-map.js"></script>';
        $this->render('tournaments/edit', [
            'tournament'   => $tournament,
            'errors'       => [],
            'extraHead'    => $extraHead,
            'extraScripts' => $extraScripts,
        ]);
    }

    public function update(array $p = []): void
    {
        AuthService::guard();
        Csrf::verify();

        $tournament = $this->tournaments->findBySlug($p['slug']);
        if (!$tournament) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->canEdit($tournament);

        [$errors, $data] = $this->validateInput();

        if ($errors) {
            $extraHead    = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">';
            $extraScripts = '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                             <script src="/js/tournament-map.js"></script>';
            $this->render('tournaments/edit', [
                'tournament'   => array_merge($tournament, $_POST),
                'errors'       => $errors,
                'extraHead'    => $extraHead,
                'extraScripts' => $extraScripts,
            ]);
            return;
        }

        if (!AuthService::isAdmin()) {
            unset($data['status']);
        }

        // Poster upload
        if (!empty($_FILES['poster']['tmp_name']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            try {
                $processor = new ImageProcessor();
                // Delete old poster if exists
                if (!empty($tournament['poster'])) {
                    $processor->deleteSingle($tournament['poster']);
                }
                $data['poster'] = $processor->processTournamentPoster($_FILES['poster']);
            } catch (\RuntimeException $e) {
                Flash::set('error', 'Error al subir el cartel: ' . $e->getMessage());
            }
        }

        // Remove poster if requested
        if ($this->input('remove_poster') === '1' && !empty($tournament['poster'])) {
            (new ImageProcessor())->deleteSingle($tournament['poster']);
            $data['poster'] = null;
        }

        $this->tournaments->updateInfo((int)$tournament['id'], $data);
        Flash::set('success', 'Torneo actualizado.');
        $this->redirect('/torneos/' . $tournament['slug']);
    }

    public function destroy(array $p = []): void
    {
        AuthService::guard();
        Csrf::verify();

        $tournament = $this->tournaments->findBySlug($p['slug']);
        if (!$tournament) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->canEdit($tournament);
        // Delete poster if exists
        if (!empty($tournament['poster'])) {
            (new ImageProcessor())->deleteSingle($tournament['poster']);
        }
        $this->tournaments->remove((int)$tournament['id']);
        Flash::set('success', 'Torneo eliminado.');
        $this->redirect('/torneos');
    }

    // ── Private helpers ──────────────────────────────────────

    private function canEdit(array $tournament): void
    {
        if ((int)$tournament['created_by'] !== $this->currentUserId() && !AuthService::isAdmin()) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            exit;
        }
    }

    private function validateInput(): array
    {
        $errors = [];

        $title      = trim($this->input('title', ''));
        $discipline = $this->input('discipline', '');
        $startsAt   = $this->input('starts_at', '');
        $endsAt     = $this->input('ends_at', '');

        if (\strlen($title) < 2) {
            $errors['title'] = 'El título debe tener al menos 2 caracteres.';
        }
        if (!\in_array($discipline, ['campo', 'liebre_mecanica', 'campeonato', 'morfologico', 'talleres', 'varios'])) {
            $errors['discipline'] = 'Disciplina no válida.';
        }
        if (!$startsAt) {
            $errors['starts_at'] = 'La fecha de inicio es obligatoria.';
        }
        if ($endsAt && $startsAt && $endsAt < $startsAt) {
            $errors['ends_at'] = 'La fecha de fin debe ser posterior al inicio.';
        }

        $latRaw = $this->input('location_lat', '');
        $lngRaw = $this->input('location_lng', '');
        $lat    = $latRaw !== '' ? (float) $latRaw : null;
        $lng    = $lngRaw !== '' ? (float) $lngRaw : null;
        if ($lat !== null && ($lat < -90 || $lat > 90))   $lat = null;
        if ($lng !== null && ($lng < -180 || $lng > 180)) $lng = null;

        $maxP = $this->input('max_participants', '');
        $maxP = $maxP !== '' ? (int) $maxP : null;

        $status = $this->input('status', 'published');
        if (!\in_array($status, ['published', 'draft', 'cancelled'])) {
            $status = 'published';
        }

        $data = [
            'title'                 => $title,
            'discipline'            => $discipline,
            'category'              => $this->input('category', ''),
            'starts_at'             => $startsAt ? (new \DateTime($startsAt))->format('Y-m-d H:i:s') : null,
            'ends_at'               => $endsAt   ? (new \DateTime($endsAt))->format('Y-m-d H:i:s')   : null,
            'location_name'         => $this->input('location_name', ''),
            'location_address'      => $this->input('location_address', ''),
            'location_lat'          => $lat,
            'location_lng'          => $lng,
            'meeting_point'         => $this->input('meeting_point', ''),
            'meeting_time'          => $this->input('meeting_time', ''),
            'map_url'               => $this->input('map_url', ''),
            'notes'                 => $this->input('notes', ''),
            'description'           => $this->input('description', ''),
            'organizer_name'        => $this->input('organizer_name', ''),
            'contact_info'          => $this->input('contact_info', ''),
            'registration_required' => $this->input('registration_required') ? 1 : 0,
            'registration_url'      => $this->input('registration_url', ''),
            'max_participants'      => $maxP,
            'status'                => $status,
        ];

        return [$errors, $data];
    }
}
