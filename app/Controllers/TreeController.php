<?php
declare(strict_types=1);
namespace Controllers;

use Models\Dog;
use Services\{TreeBuilder, AncestryService};
use Helpers\{Csrf, Flash};

class TreeController extends BaseController
{
    private Dog             $dogs;
    private TreeBuilder     $treeBuilder;
    private AncestryService $ancestry;

    public function __construct()
    {
        $this->dogs        = new Dog();
        $this->treeBuilder = new TreeBuilder();
        $this->ancestry    = new AncestryService();
    }

    public function show(array $p = []): void
    {
        $dog = $this->dogs->findBySlug($p['slug']);
        if (!$dog) {
            http_response_code(404);
            include APP_PATH . '/Views/errors/404.php';
            exit;
        }
        $this->render('tree/show', compact('dog'));
    }

    public function apiTree(array $p = []): void
    {
        $dog = $this->dogs->findBySlug($p['slug']);
        if (!$dog) {
            $this->json(['error' => 'Perro no encontrado'], 404);
        }
        $maxDepth = min((int) $this->query('gen', 6), 12);
        $tree     = $this->treeBuilder->build($dog['id'], $maxDepth);
        $this->json($tree ?: ['error' => 'Sin datos']);
    }

    public function setFather(array $p = []): void
    {
        Csrf::verify();
        $dog = $this->dogs->findBySlug($p['slug']);
        if (!$dog) { http_response_code(404); exit; }

        $fatherId = (int) $this->input('father_id', 0) ?: null;
        $this->ancestry->linkFather($dog['id'], $fatherId);

        Flash::set('success', $fatherId ? 'Padre vinculado correctamente.' : 'Padre desvinculado.');
        $this->redirect('/galgos/' . $p['slug']);
    }

    public function setMother(array $p = []): void
    {
        Csrf::verify();
        $dog = $this->dogs->findBySlug($p['slug']);
        if (!$dog) { http_response_code(404); exit; }

        $motherId = (int) $this->input('mother_id', 0) ?: null;
        $this->ancestry->linkMother($dog['id'], $motherId);

        Flash::set('success', $motherId ? 'Madre vinculada correctamente.' : 'Madre desvinculada.');
        $this->redirect('/galgos/' . $p['slug']);
    }

    public function apiRelatives(array $p = []): void
    {
        $dogId = (int) ($p['id'] ?? 0);
        $suggestions = $this->ancestry->suggestConnections($dogId);
        $this->json($suggestions);
    }
}
