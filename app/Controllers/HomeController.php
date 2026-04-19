<?php
declare(strict_types=1);
namespace Controllers;

use Models\{Stallion, Broodmare, Dog, Sponsor};

class HomeController extends BaseController
{
    public function index(array $p = []): void
    {
        $stallionModel  = new Stallion();
        $broodmareModel = new Broodmare();
        $dogModel       = new Dog();

        $stallions       = $stallionModel->allActive(6);
        $broodmares      = $broodmareModel->allActive(6);
        $recentDogs      = $dogModel->getRecent(12);
        $totalDogs       = $dogModel->countPublic();
        $totalStallions  = $stallionModel->countActive();
        $totalBroodmares = $broodmareModel->countActive();
        $sponsors        = Sponsor::allActive();

        $this->render('home/index', compact(
            'stallions', 'broodmares', 'recentDogs',
            'totalDogs', 'totalStallions', 'totalBroodmares',
            'sponsors'
        ));
    }
}
