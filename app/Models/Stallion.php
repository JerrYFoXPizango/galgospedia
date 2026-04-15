<?php

declare(strict_types=1);

namespace Models;

class Stallion extends BaseModel
{
    protected string $table = 'stallions';

    public function allActive(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, d.slug, d.name, d.photo_thumb, d.photo_webp,
                    d.registration_number, d.date_of_birth, d.color, d.club, d.country,
                    u.username AS owner_username
             FROM stallions s
             JOIN dogs d ON d.id = s.dog_id
             LEFT JOIN users u ON u.id = d.owner_user_id
             WHERE s.is_active = 1 AND d.is_public = 1
             ORDER BY s.featured_order ASC, d.name ASC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM stallions s JOIN dogs d ON d.id = s.dog_id WHERE s.is_active = 1 AND d.is_public = 1");
        return (int) $stmt->fetchColumn();
    }

    public function forDog(int $dogId): ?array
    {
        return $this->findBy('dog_id', $dogId);
    }

    public function register(int $dogId, array $data = []): int
    {
        // Remove if already exists, then re-insert
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

    public function remove(int $dogId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM stallions WHERE dog_id = ?");
        return $stmt->execute([$dogId]);
    }
}
