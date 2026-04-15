<?php

declare(strict_types=1);

namespace Models;

class ClubDocument extends BaseModel
{
    protected string $table = 'club_documents';

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    public function listByClub(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT cd.*, u.username AS uploader_username
             FROM club_documents cd
             LEFT JOIN users u ON u.id = cd.uploaded_by
             WHERE cd.club_id = ?
             ORDER BY cd.created_at DESC"
        );
        $stmt->execute([$clubId]);
        return $stmt->fetchAll();
    }

    public function add(array $data): int
    {
        return $this->insert([
            'club_id'       => $data['club_id'],
            'title'         => $data['title'],
            'category'      => $data['category'],
            'file_path'     => $data['file_path'],
            'original_name' => $data['original_name'],
            'mime_type'     => $data['mime_type'],
            'file_size'     => $data['file_size'],
            'expires_at'    => $data['expires_at'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'uploaded_by'   => $data['uploaded_by'],
        ]);
    }

    public function remove(int $id): bool
    {
        return $this->delete($id);
    }
}
