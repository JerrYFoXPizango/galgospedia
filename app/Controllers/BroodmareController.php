<?php
declare(strict_types=1);
namespace Controllers;

use Models\Broodmare;

class BroodmareController extends BaseController
{
    public function index(array $p = []): void
    {
        $broodmares = (new Broodmare())->allActive();
        $this->render('broodmares/index', compact('broodmares'));
    }
}
