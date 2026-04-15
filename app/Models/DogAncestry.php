<?php

declare(strict_types=1);

namespace Models;

use Config\Database;
use Helpers\Asset;
use PDO;

class DogAncestry
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    /** Rebuild ancestry rows for a dog by calling the stored procedure */
    public function rebuild(int $dogId): void
    {
        $this->db->exec("CALL sp_rebuild_ancestry($dogId)");
    }

    /** Rebuild ancestry for a dog AND propagate to all its descendants (BFS, no SP recursion) */
    public function rebuildWithDescendants(int $dogId): void
    {
        $this->rebuild($dogId);

        // BFS: find all dogs that descend from $dogId and rebuild each
        $visited = [$dogId => true];
        $queue   = [$dogId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $stmt = $this->db->prepare(
                "SELECT id FROM dogs WHERE father_id = ? OR mother_id = ?"
            );
            $stmt->execute([$current, $current]);
            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($children as $childId) {
                $childId = (int) $childId;
                if (!isset($visited[$childId])) {
                    $visited[$childId] = true;
                    $this->rebuild($childId);
                    $queue[] = $childId;
                }
            }
        }
    }

    /**
     * Get all ancestors of a dog up to $maxDepth generations.
     * Returns flat list ordered by depth.
     */
    public function getAncestors(int $dogId, int $maxDepth = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.name, d.gender, d.photo_thumb,
                    d.registration_number, da.depth, da.path_line
             FROM dog_ancestry da
             JOIN dogs d ON d.id = da.ancestor_id
             WHERE da.descendant_id = ?
               AND da.depth > 0
               AND da.depth <= ?
             ORDER BY da.depth ASC, d.name ASC"
        );
        $stmt->execute([$dogId, $maxDepth]);
        return $stmt->fetchAll();
    }

    /**
     * Get all descendants of a dog.
     */
    public function getDescendants(int $dogId, int $maxDepth = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.name, d.gender, d.photo_thumb,
                    d.registration_number, da.depth, da.path_line
             FROM dog_ancestry da
             JOIN dogs d ON d.id = da.descendant_id
             WHERE da.ancestor_id = ?
               AND da.depth > 0
               AND da.depth <= ?
             ORDER BY da.depth ASC, d.name ASC"
        );
        $stmt->execute([$dogId, $maxDepth]);
        return $stmt->fetchAll();
    }

    /**
     * Build a nested tree structure for D3.js.
     * Returns array suitable for json_encode.
     * "children" in our tree = parents of the dog (tree grows upward).
     */
    public function buildTreeNode(int $dogId, int $maxDepth = 8, int $currentDepth = 0): ?array
    {
        if ($currentDepth >= $maxDepth) {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.name, d.gender, d.photo_thumb, d.photo_webp,
                    d.registration_number, d.date_of_birth, d.color,
                    d.club, d.country, d.champion,
                    d.father_id, d.mother_id,
                    s.id AS is_stallion, b.id AS is_broodmare
             FROM dogs d
             LEFT JOIN stallions  s ON s.dog_id = d.id AND s.is_active = 1
             LEFT JOIN broodmares b ON b.dog_id = d.id AND b.is_active = 1
             WHERE d.id = ? LIMIT 1"
        );
        $stmt->execute([$dogId]);
        $dog = $stmt->fetch();

        if (!$dog) {
            return null;
        }

        $node = [
            'id'         => $dog['id'],
            'slug'       => $dog['slug'],
            'name'       => $dog['name'],
            'gender'     => $dog['gender'],
            'photo'      => Asset::url($dog['photo_webp'] ?: $dog['photo_thumb'] ?: ''),
            'reg'        => $dog['registration_number'],
            'birth'      => $dog['date_of_birth'],
            'club'       => $dog['club'] ?? null,
            'country'    => $dog['country'] ?? null,
            'champion'   => $dog['champion'] ?? null,
            'isStallion' => (bool) $dog['is_stallion'],
            'isBreeder'  => (bool) $dog['is_broodmare'],
            'depth'      => $currentDepth,
            'children'   => [],
        ];

        // Father branch (paternal)
        if ($dog['father_id']) {
            $fatherNode = $this->buildTreeNode($dog['father_id'], $maxDepth, $currentDepth + 1);
            if ($fatherNode) {
                $fatherNode['side'] = 'paternal';
                $node['children'][] = $fatherNode;
            }
        }

        // Mother branch (maternal)
        if ($dog['mother_id']) {
            $motherNode = $this->buildTreeNode($dog['mother_id'], $maxDepth, $currentDepth + 1);
            if ($motherNode) {
                $motherNode['side'] = 'maternal';
                $node['children'][] = $motherNode;
            }
        }

        return $node;
    }

    /**
     * Find dogs that could be auto-connected as parent of $dogId.
     * Searches by name similarity and registration number.
     */
    public function findAutoConnectCandidates(int $dogId): array
    {
        // Get the dog's current parent references if any were entered as text
        $stmt = $this->db->prepare("SELECT * FROM dogs WHERE id = ?");
        $stmt->execute([$dogId]);
        $dog = $stmt->fetch();

        if (!$dog) {
            return [];
        }

        $candidates = [];

        // Find existing dogs that have this dog as a child
        $childStmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.name, d.gender, d.registration_number, d.photo_thumb,
                    'child' AS relationship
             FROM dogs d
             WHERE (d.father_id = ? OR d.mother_id = ?)
               AND d.id != ?"
        );
        $childStmt->execute([$dogId, $dogId, $dogId]);
        $candidates = array_merge($candidates, $childStmt->fetchAll());

        return $candidates;
    }

    /**
     * Check if two dogs are related (share a common ancestor within N generations).
     */
    public function areRelated(int $dogA, int $dogB, int $withinDepth = 6): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM dog_ancestry da1
             JOIN dog_ancestry da2 ON da1.ancestor_id = da2.ancestor_id
             WHERE da1.descendant_id = ?
               AND da2.descendant_id = ?
               AND da1.depth <= ?
               AND da2.depth <= ?
               AND da1.depth > 0
               AND da2.depth > 0"
        );
        $stmt->execute([$dogA, $dogB, $withinDepth, $withinDepth]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Calculate inbreeding coefficient (COI) using Wright's path coefficient method.
     * Returns a float 0.0–1.0 (0% to 100%).
     */
    public function calculateCOI(int $dogId, int $generations = 5): float
    {
        // Collect all ancestors with their path counts
        $stmt = $this->db->prepare(
            "SELECT ancestor_id, depth, path_line
             FROM dog_ancestry
             WHERE descendant_id = ? AND depth > 0 AND depth <= ?"
        );
        $stmt->execute([$dogId, $generations]);
        $ancestors = $stmt->fetchAll();

        // Group by ancestor_id
        $ancestorPaths = [];
        foreach ($ancestors as $row) {
            $ancestorPaths[$row['ancestor_id']][] = $row['depth'];
        }

        $coi = 0.0;
        foreach ($ancestorPaths as $paths) {
            $pathCount = \count($paths);
            if ($pathCount < 2) {
                continue; // ancestor appears only once → no inbreeding contribution
            }
            // For each pair of paths through this common ancestor
            for ($i = 0; $i < $pathCount - 1; $i++) {
                for ($j = $i + 1; $j < $pathCount; $j++) {
                    $n = $paths[$i] + $paths[$j];
                    $coi += pow(0.5, $n + 1);
                }
            }
        }

        return round($coi, 6);
    }
}
