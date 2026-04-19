<?php

declare(strict_types=1);

namespace Models;

class Dog extends BaseModel
{
    protected string $table = 'dogs';

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT d.*,
                    f.name AS father_name, f.slug AS father_slug,
                    m.name AS mother_name, m.slug AS mother_slug,
                    u.username AS owner_username
             FROM dogs d
             LEFT JOIN dogs f ON f.id = d.father_id
             LEFT JOIN dogs m ON m.id = d.mother_id
             LEFT JOIN users u ON u.id = d.owner_user_id
             WHERE d.slug = ? LIMIT 1"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data, int $createdBy): int
    {
        $slug = $this->makeUniqueSlug($data['name']);
        return $this->insert([
            'slug'                => $slug,
            'name'                => $data['name'],
            'gender'              => $data['gender'] ?? 'unknown',
            'date_of_birth'       => $data['date_of_birth'] ?? null,
            'date_of_death'       => $data['date_of_death'] ?? null,
            'color'               => $data['color'] ?? null,
            'club'                => $data['club'] ?? null,
            'country'             => $data['country'] ?? null,
            'champion'            => $data['champion'] ?? null,
            'breed_variant'       => $data['breed_variant'] ?? 'spanish_greyhound',
            'father_id'           => $data['father_id'] ?? null,
            'mother_id'           => $data['mother_id'] ?? null,
            'owner_user_id'       => $data['owner_user_id'] ?? null,
            'owner_name'          => $data['owner_name'] ?? null,
            'registration_number' => $data['registration_number'] ?? null,
            'notes'               => $data['notes'] ?? null,
            'is_public'           => $data['is_public'] ?? 1,
            'created_by'          => $createdBy,
        ]);
    }

    public function updateInfo(int $id, array $data): bool
    {
        $allowed = [
            'name','gender','date_of_birth','date_of_death','color',
            'club','country','champion','breed_variant','notes','is_public','owner_name',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->update($id, $filtered);
    }

    public function setPhoto(int $id, string $webp, string $thumb): bool
    {
        return $this->update($id, [
            'photo_webp'   => $webp,
            'photo_thumb'  => $thumb,
        ]);
    }

    public function setFather(int $id, ?int $fatherId): bool
    {
        return $this->update($id, ['father_id' => $fatherId]);
    }

    public function setMother(int $id, ?int $motherId): bool
    {
        return $this->update($id, ['mother_id' => $motherId]);
    }

    public function remove(int $id): bool
    {
        return $this->delete($id);
    }

    /** Search dogs by name, registration number, country, club or champion */
    public function search(string $query, int $limit = 10): array
    {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            "SELECT id, slug, name, gender, photo_thumb, registration_number, club, country, champion
             FROM dogs
             WHERE is_public = 1
               AND (name LIKE ? OR registration_number LIKE ? OR country LIKE ? OR club LIKE ? OR champion LIKE ?)
             ORDER BY name ASC LIMIT ?"
        );
        $stmt->execute([$like, $like, $like, $like, $like, $limit]);
        return $stmt->fetchAll();
    }

    /** Paginated public directory */
    public function getRecent(int $limit = 12): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.name, d.gender, d.photo_thumb, d.created_at
             FROM dogs d WHERE d.is_public = 1
             ORDER BY d.created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function directory(int $page = 1, int $perPage = 24, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ['d.is_public = 1'];
        $params = [];

        if (!empty($filters['gender'])) {
            $where[]  = 'd.gender = ?';
            $params[] = $filters['gender'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(d.name LIKE ? OR d.registration_number LIKE ? OR d.country LIKE ? OR d.club LIKE ? OR d.champion LIKE ?)';
            $like     = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.name, d.gender, d.photo_webp, d.photo_thumb,
                    d.registration_number, d.date_of_birth, d.date_of_death, d.club, d.country,
                    s.id AS stallion_id, b.id AS broodmare_id
             FROM dogs d
             LEFT JOIN stallions s ON s.dog_id = d.id AND s.is_active = 1
             LEFT JOIN broodmares b ON b.dog_id = d.id AND b.is_active = 1
             WHERE $whereStr
             ORDER BY d.name ASC LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, $perPage, $offset]);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM dogs d WHERE $whereStr");
        $countStmt->execute($params);

        return [
            'data'    => $stmt->fetchAll(),
            'total'   => (int) $countStmt->fetchColumn(),
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }

    /** Get direct children (dogs that list this dog as father or mother) */
    public function children(int $dogId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, slug, name, gender, photo_thumb
             FROM dogs
             WHERE father_id = ? OR mother_id = ?
             ORDER BY name ASC"
        );
        $stmt->execute([$dogId, $dogId]);
        return $stmt->fetchAll();
    }

    public function countPublic(): int
    {
        return $this->count('is_public = 1');
    }

    /**
     * Bulk-import dogs from a parsed CSV (array of assoc rows).
     * Skips rows whose name already exists (case-insensitive).
     * Two-pass: inserts all dogs first, then resolves sire/dam by name.
     *
     * @param  array[] $rows        Rows from the CSV (keys: name,gender,color,country,
     *                              year_of_birth,sire_name,dam_name,champion,variety)
     * @param  int     $importedBy  User ID that will own the import
     * @param  bool    $dryRun      If true, count only — no DB writes
     * @return array{inserted:int, skipped:int, linked:int}
     */
    public function bulkImport(array $rows, int $importedBy, bool $dryRun = false): array
    {
        // Build existing-name index (lowercase → true) to detect duplicates fast
        $existing = [];
        foreach ($this->db->query("SELECT LOWER(name) AS n FROM dogs")->fetchAll(\PDO::FETCH_COLUMN) as $n) {
            $existing[$n] = true;
        }

        $inserted       = 0;
        $skipped        = 0;
        $errors         = [];
        $pendingParents = []; // dog_id → ['sire' => name, 'dam' => name]

        if (!$dryRun) $this->db->beginTransaction();

        try {
            foreach ($rows as $i => $row) {
                $name = trim($row['name'] ?? '');
                if ($name === '') { $skipped++; continue; }

                if (isset($existing[strtolower($name)])) { $skipped++; continue; }

                $dob = !empty($row['year_of_birth']) ? ((int)$row['year_of_birth']) . '-01-01' : null;

                if (!$dryRun) {
                    $id = $this->create([
                        'name'                => $name,
                        'gender'              => $row['gender']              ?? 'unknown',
                        'color'               => $row['color']               ?: null,
                        'country'             => $row['country']             ?: null,
                        'date_of_birth'       => $dob,
                        'champion'            => $row['champion']            ?: null,
                        'registration_number' => $row['registration_number'] ?: null,
                        'owner_name'          => $row['owner_name']          ?: null,
                    ], $importedBy);

                    $existing[strtolower($name)] = true;

                    $sire = trim($row['sire_name'] ?? '');
                    $dam  = trim($row['dam_name']  ?? '');
                    if ($sire || $dam) {
                        $pendingParents[$id] = ['sire' => $sire, 'dam' => $dam];
                    }
                }

                $inserted++;
            }

            // Second pass — resolve parent links
            $linked = 0;
            if (!$dryRun) {
                $lookupStmt = $this->db->prepare("SELECT id FROM dogs WHERE name = ? LIMIT 1");
                foreach ($pendingParents as $dogId => $parents) {
                    $updates = [];
                    foreach (['sire' => 'father_id', 'dam' => 'mother_id'] as $key => $col) {
                        if ($parents[$key] !== '') {
                            $lookupStmt->execute([$parents[$key]]);
                            $found = $lookupStmt->fetchColumn();
                            if ($found) $updates[$col] = (int) $found;
                        }
                    }
                    if ($updates) { $this->update($dogId, $updates); $linked++; }
                }

                // Third pass — register males as stallions, females as broodmares
                $this->db->exec("INSERT INTO stallions (dog_id, is_active, created_at)
                    SELECT d.id, 1, NOW() FROM dogs d
                    LEFT JOIN stallions s ON s.dog_id = d.id
                    WHERE d.gender = 'male' AND s.id IS NULL");

                $this->db->exec("INSERT INTO broodmares (dog_id, is_active, created_at)
                    SELECT d.id, 1, NOW() FROM dogs d
                    LEFT JOIN broodmares b ON b.dog_id = d.id
                    WHERE d.gender = 'female' AND b.id IS NULL");

                $this->db->commit();
            }
        } catch (\Throwable $e) {
            if (!$dryRun && $this->db->inTransaction()) $this->db->rollBack();
            throw $e; // re-throw so controller can log + show error
        }

        return compact('inserted', 'skipped', 'linked', 'errors');
    }

    /** Generate a URL-safe slug unique in the dogs table */
    private function makeUniqueSlug(string $name): string
    {
        $base = \Helpers\Slugify::make($name);
        $slug = $base;
        $i    = 1;
        while ($this->findBy('slug', $slug)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
