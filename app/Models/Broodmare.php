<?php

declare(strict_types=1);

namespace Models;

class Broodmare extends BaseModel
{
    protected string $table = 'broodmares';

    public function allActive(int $limit = 50, string $q = ''): array
    {
        $where = "b.is_active = 1 AND d.is_public = 1";
        $params = [];
        if ($q !== '') {
            $where .= " AND (d.name LIKE ? OR d.registration_number LIKE ? OR d.club LIKE ? OR d.country LIKE ?)";
            $like = '%' . $q . '%';
            $params = [$like, $like, $like, $like];
        }
        $params[] = $limit;
        $stmt = $this->db->prepare(
            "SELECT b.*, d.slug, d.name, d.photo_thumb, d.photo_webp,
                    d.registration_number, d.date_of_birth, d.color, d.club, d.country,
                    u.username AS owner_username
             FROM broodmares b
             JOIN dogs d ON d.id = b.dog_id
             LEFT JOIN users u ON u.id = d.owner_user_id
             WHERE $where
             ORDER BY b.featured_order ASC, d.name ASC
             LIMIT ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM broodmares b JOIN dogs d ON d.id = b.dog_id WHERE b.is_active = 1 AND d.is_public = 1");
        return (int) $stmt->fetchColumn();
    }

    public function forDog(int $dogId): ?array
    {
        return $this->findBy('dog_id', $dogId);
    }

    public function register(int $dogId, array $data = []): int
    {
        $existing = $this->forDog($dogId);
        if ($existing) {
            return $existing['id'];
        }
        return $this->insert([
            'dog_id'         => $dogId,
            'description'    => $data['description'] ?? null,
            'achievements'   => $data['achievements'] ?? null,
            'featured_order' => $data['featured_order'] ?? 0,
            'is_active'      => 1,
        ]);
    }

    public function toggle(int $dogId): bool
    {
        $existing = $this->forDog($dogId);
        if ($existing) {
            $newActive = $existing['is_active'] ? 0 : 1;
            return $this->update($existing['id'], ['is_active' => $newActive]);
        }
        return false;
    }

    public function deactivateForDog(int $dogId): bool
    {
        $stmt = $this->db->prepare("UPDATE broodmares SET is_active = 0 WHERE dog_id = ?");
        return $stmt->execute([$dogId]);
    }

    public function remove(int $dogId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM broodmares WHERE dog_id = ?");
        return $stmt->execute([$dogId]);
    }
}
