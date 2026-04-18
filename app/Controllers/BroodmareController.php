<?php
declare(strict_types=1);
namespace Controllers;

use Models\Broodmare;

class BroodmareController extends BaseController
{
    public function index(array $p = []): void
    {
        $q = trim($this->query('q', ''));
        $broodmares = (new Broodmare())->allActive(50, $q);
        $this->render('broodmares/index', compact('broodmares', 'q'));
    }
}
