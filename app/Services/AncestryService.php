<?php
declare(strict_types=1);
namespace Services;

use Models\Dog;
use Models\DogAncestry;

class AncestryService
{
    private Dog         $dogModel;
    private DogAncestry $ancestry;

    public function __construct()
    {
        $this->dogModel = new Dog();
        $this->ancestry = new DogAncestry();
    }

    public function linkFather(int $dogId, ?int $fatherId): void
    {
        $this->dogModel->setFather($dogId, $fatherId);
        $this->ancestry->rebuildWithDescendants($dogId);
    }

    public function linkMother(int $dogId, ?int $motherId): void
    {
        $this->dogModel->setMother($dogId, $motherId);
        $this->ancestry->rebuildWithDescendants($dogId);
    }

    /** After creating a new dog, initialize its self-reference in closure table */
    public function initDog(int $dogId): void
    {
        $this->ancestry->rebuild($dogId);
    }

    /** Find dogs that could be connected to this dog as parent/child */
    public function suggestConnections(int $dogId): array
    {
        return $this->ancestry->findAutoConnectCandidates($dogId);
    }

    public function getCOI(int $dogId, int $generations = 5): float
    {
        return $this->ancestry->calculateCOI($dogId, $generations);
    }
}
