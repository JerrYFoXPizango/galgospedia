<?php

declare(strict_types=1);

namespace Models;

class ClubEvent extends BaseModel
{
    protected string $table = 'club_events';

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    /** Próximos eventos (starts_at >= ahora) */
    public function upcoming(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM club_events
             WHERE club_id = ? AND starts_at >= NOW()
             ORDER BY starts_at ASC"
        );
        $stmt->execute([$clubId]);
        return $stmt->fetchAll();
    }

    /** Eventos pasados (starts_at < ahora) */
    public function past(int $clubId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM club_events
             WHERE club_id = ? AND starts_at < NOW()
             ORDER BY starts_at DESC
             LIMIT $limit"
        );
        $stmt->execute([$clubId]);
        return $stmt->fetchAll();
    }

    public function add(array $data): int
    {
        return $this->insert([
            'club_id'     => $data['club_id'],
            'title'       => $data['title'],
            'type'        => $data['type'],
            'description' => $data['description'] ?? null,
            'location'    => $data['location'] ?? null,
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'] ?? null,
            'created_by'  => $data['created_by'],
        ]);
    }

    public function updateEvent(int $id, array $data): bool
    {
        return $this->update($id, [
            'title'       => $data['title'],
            'type'        => $data['type'],
            'description' => $data['description'] ?? null,
            'location'    => $data['location'] ?? null,
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'] ?? null,
        ]);
    }

    public function remove(int $id): bool
    {
        return $this->delete($id);
    }
}
