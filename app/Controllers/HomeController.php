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

        $prioritize = function(array $list): array {
            usort($list, function($a, $b) {
                $fo = ($a['featured_order'] ?? 99) <=> ($b['featured_order'] ?? 99);
                if ($fo !== 0) return $fo;
                $ph = (!empty($b['photo_webp']) ? 1 : 0) <=> (!empty($a['photo_webp']) ? 1 : 0);
                if ($ph !== 0) return $ph;
                return strcmp($a['name'], $b['name']);
            });
            return $list;
        };

        $stallions  = $prioritize($stallionModel->allActive(20));
        $broodmares = $prioritize($broodmareModel->allActive(20));
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
