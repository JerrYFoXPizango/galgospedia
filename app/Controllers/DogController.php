<?php
declare(strict_types=1);
namespace Controllers;

use Models\{Dog, Stallion, Broodmare};
use Models\DogAncestry;
use Services\{ImageProcessor, AncestryService};
use Helpers\{Csrf, Flash};

class DogController extends BaseController
{
    private Dog             $dogs;
    private AncestryService $ancestry;

    public function __construct()
    {
        $this->dogs     = new Dog();
        $this->ancestry = new AncestryService();
    }

    public function index(array $p = []): void
    {
        $page    = (int) $this->query('page', 1);
        $filters = [
            'q'      => $this->query('q', ''),
            'gender' => $this->query('gender', ''),
        ];
        $result = $this->dogs->directory($page, 24, $filters);
        $this->render('dogs/index', [
            'dogs'    => $result['data'],
            'total'   => $result['total'],
            'page'    => $page,
            'perPage' => $result['perPage'],
            'filters' => $filters,
        ]);
    }

    public function show(array $p = []): void
    {
        $dog = $this->dogs->findBySlug($p['slug']);
        if (!$dog) { $this->notFound(); }

        $children   = $this->dogs->children($dog['id']);
        $ancestry   = new DogAncestry();
        $coi        = $ancestry->calculateCOI($dog['id']);
        $isStallion = (new Stallion())->forDog($dog['id']);
        $isBreeder  = (new Broodmare())->forDog($dog['id']);

        $this->render('dogs/show', compact('dog', 'children', 'coi', 'isStallion', 'isBreeder'));
    }

    public function create(array $p = []): void
    {
        $this->render('dogs/create');
    }

    public function store(array $p = []): void
    {
        Csrf::verify();
        $userId = $this->currentUserId();

        $data = [
            'name'                => $this->input('name', ''),
            'gender'              => $this->input('gender', 'unknown'),
            'date_of_birth'       => $this->input('date_of_birth') ?: null,
            'color'               => $this->input('color') ?: null,
            'club'                => $this->input('club') ?: null,
            'country'             => $this->input('country') ?: null,
            'champion'            => $this->input('champion') ?: null,
            'breed_variant'       => $this->input('breed_variant', 'spanish_greyhound'),
            'notes'               => $this->input('notes') ?: null,
            'is_public'           => $this->input('is_public', '1') === '1' ? 1 : 0,
            'owner_user_id'       => $userId,
        ];

        if (empty($data['name'])) {
            Flash::set('error', 'El nombre del galgo es obligatorio.');
            $this->redirect('/galgos/nuevo');
        }

        $dogId = $this->dogs->create($data, $userId);
        $this->ancestry->initDog($dogId);

        // Process photo if uploaded
        if (!empty($_FILES['photo']['name'])) {
            try {
                $processor = new ImageProcessor();
                $paths     = $processor->process($_FILES['photo']);
                $this->dogs->setPhoto($dogId, $paths['webp'], $paths['thumb']);
            } catch (\RuntimeException $e) {
                Flash::set('warning', 'Perro creado, pero la foto falló: ' . $e->getMessage());
            }
        }

        // Sync stallion / broodmare
        $stallion  = new Stallion();
        $broodmare = new Broodmare();
        $this->input('is_stallion')  ? $stallion->register($dogId)  : $stallion->remove($dogId);
        $this->input('is_broodmare') ? $broodmare->register($dogId) : $broodmare->remove($dogId);

        $dog = $this->dogs->findById($dogId);
        Flash::set('success', 'Galgo "' . htmlspecialchars($dog['name']) . '" creado correctamente.');
        $this->redirect('/galgos/' . $dog['slug']);
    }

    public function edit(array $p = []): void
    {
        $dog       = $this->dogs->findBySlug($p['slug']);
        if (!$dog) { $this->notFound(); }
        $this->canEdit($dog);
        $isStallion = (new Stallion())->forDog($dog['id']);
        $isBreeder  = (new Broodmare())->forDog($dog['id']);
        $this->render('dogs/edit', compact('dog', 'isStallion', 'isBreeder'));
    }

    public function update(array $p = []): void
    {
        Csrf::verify();
        $dog = $this->dogs->findBySlug($p['slug']);
        if (!$dog) { $this->notFound(); }
        $this->canEdit($dog);

        $data = [
            'name'                => $this->input('name', ''),
            'gender'              => $this->input('gender', 'unknown'),
            'date_of_birth'       => $this->input('date_of_birth') ?: null,
            'date_of_death'       => $this->input('date_of_death') ?: null,
            'color'               => $this->input('color') ?: null,
            'club'                => $this->input('club') ?: null,
            'country'             => $this->input('country') ?: null,
            'champion'            => $this->input('champion') ?: null,
            'breed_variant'       => $this->input('breed_variant', 'spanish_greyhound'),
            'notes'               => $this->input('notes') ?: null,
            'is_public'           => $this->input('is_public', '1') === '1' ? 1 : 0,
        ];

        $this->dogs->updateInfo($dog['id'], $data);

        if (!empty($_FILES['photo']['name'])) {
            try {
                $processor = new ImageProcessor();
                $paths     = $processor->process($_FILES['photo']);
                // Delete old images
                if ($dog['photo_webp']) {
                    $processor->delete($dog['photo_webp'], $dog['photo_thumb']);
                }
                $this->dogs->setPhoto($dog['id'], $paths['webp'], $paths['thumb']);
            } catch (\RuntimeException $e) {
                Flash::set('warning', 'Datos actualizados, pero la foto falló: ' . $e->getMessage());
            }
        }

        // Sync stallion / broodmare
        $stallion  = new Stallion();
        $broodmare = new Broodmare();
        $this->input('is_stallion')  ? $stallion->register($dog['id'])  : $stallion->remove($dog['id']);
        $this->input('is_broodmare') ? $broodmare->register($dog['id']) : $broodmare->remove($dog['id']);

        Flash::set('success', 'Galgo actualizado correctamente.');
        $this->redirect('/galgos/' . $p['slug']);
    }

    public function destroy(array $p = []): void
    {
        Csrf::verify();
        $dog = $this->dogs->findBySlug($p['slug']);
        if (!$dog) { $this->notFound(); }
        $this->canEdit($dog);

        if ($dog['photo_webp']) {
            (new ImageProcessor())->delete($dog['photo_webp'], $dog['photo_thumb']);
        }
        $this->dogs->remove($dog['id']);
        Flash::set('success', 'Galgo eliminado.');
        $this->redirect('/galgos');
    }

    public function apiSearch(array $p = []): void
    {
        $q       = $this->query('q', '');
        $results = $this->dogs->search($q, 10);
        foreach ($results as &$r) {
            $r['photo_url'] = \Helpers\Asset::url($r['photo_thumb'] ?? '');
        }
        $this->json($results);
    }

    private function canEdit(array $dog): void
    {
        $userId = $this->currentUserId();
        if ($dog['created_by'] !== $userId && !\Services\AuthService::isAdmin()) {
            http_response_code(403);
            include APP_PATH . '/Views/errors/403.php';
            exit;
        }
    }

    private function notFound(): never
    {
        http_response_code(404);
        include APP_PATH . '/Views/errors/404.php';
        exit;
    }
}
