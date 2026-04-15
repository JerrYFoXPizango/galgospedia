<?php
declare(strict_types=1);
namespace Services;

use Models\DogAncestry;

class TreeBuilder
{
    private DogAncestry $ancestry;

    public function __construct()
    {
        $this->ancestry = new DogAncestry();
    }

    /** Build the full nested tree JSON for D3.js */
    public function build(int $dogId, int $maxDepth = 8): ?array
    {
        return $this->ancestry->buildTreeNode($dogId, $maxDepth);
    }

    /** Flat ancestor list for sidebar/table view */
    public function ancestors(int $dogId, int $maxDepth = 8): array
    {
        return $this->ancestry->getAncestors($dogId, $maxDepth);
    }

    /** Flat descendant list */
    public function descendants(int $dogId, int $maxDepth = 8): array
    {
        return $this->ancestry->getDescendants($dogId, $maxDepth);
    }
}
