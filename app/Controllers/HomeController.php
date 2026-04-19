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

        $withPhoto = fn($d) => !empty($d['photo_webp']);
        $stallions  = array_slice(array_values(array_filter($stallionModel->allActive(40), $withPhoto)), 0, 12);
        $broodmares = array_slice(array_values(array_filter($broodmareModel->allActive(40), $withPhoto)), 0, 12);
        $recentDogs = $dogModel->getRecent(16);
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
