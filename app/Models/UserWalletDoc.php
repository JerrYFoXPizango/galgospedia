<?php
declare(strict_types=1);
namespace Models;

class UserWalletDoc extends BaseModel
{
    protected string $table = 'user_wallet_docs';

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    /** Todos los documentos de un usuario, con nombre del galgo si aplica */
    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT w.*, d.name AS dog_name
             FROM user_wallet_docs w
             LEFT JOIN dogs d ON d.id = w.dog_id
             WHERE w.user_id = ?
             ORDER BY w.doc_type, w.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Bytes totales ocupados por el usuario */
    public function totalSizeByUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(file_size), 0) FROM user_wallet_docs WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function add(array $data): int
    {
        return $this->insert([
            'user_id'       => $data['user_id'],
            'dog_id'        => $data['dog_id'] ?: null,
            'doc_type'      => $data['doc_type'],
            'title'         => $data['title'],
            'file_path'     => $data['file_path'],
            'original_name' => $data['original_name'],
            'mime_type'     => $data['mime_type'],
            'file_size'     => $data['file_size'],
            'expires_at'    => $data['expires_at'] ?: null,
            'notes'         => $data['notes'] ?? null,
        ]);
    }

    public function remove(int $id): bool
    {
        return $this->delete($id);
    }
}
