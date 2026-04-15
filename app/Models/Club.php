<?php

declare(strict_types=1);

namespace Models;

class Club extends BaseModel
{
    protected string $table = 'clubs';

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.username AS president_username
             FROM clubs c
             LEFT JOIN users u ON u.id = c.president_user_id
             WHERE c.slug = ? LIMIT 1"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** All active clubs as a flat list for dropdowns */
    public function allActive(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, province, autonomous_community
             FROM clubs WHERE status = 'active' ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }

    /** Active clubs for public listing */
    public function listActive(int $page = 1, int $perPage = 24): array
    {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT c.id, c.slug, c.name, c.type, c.province, c.autonomous_community,
                    c.country, c.logo_path, c.is_verified,
                    u.username AS president_username
             FROM clubs c
             LEFT JOIN users u ON u.id = c.president_user_id
             WHERE c.status = 'active'
             ORDER BY c.name ASC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);

        $countStmt = $this->db->query("SELECT COUNT(*) FROM clubs WHERE status = 'active'");

        return [
            'data'    => $stmt->fetchAll(),
            'total'   => (int) $countStmt->fetchColumn(),
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }

    /** All clubs for admin */
    public function listAll(int $page = 1, int $perPage = 30): array
    {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT c.*, u.username AS president_username,
                    cb.username AS created_by_username
             FROM clubs c
             LEFT JOIN users u  ON u.id  = c.president_user_id
             LEFT JOIN users cb ON cb.id = c.created_by
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);
        $countStmt = $this->db->query("SELECT COUNT(*) FROM clubs");

        return [
            'data'    => $stmt->fetchAll(),
            'total'   => (int) $countStmt->fetchColumn(),
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }

    /** Club owned/presided by a user */
    public function findByPresident(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM clubs WHERE president_user_id = ? LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data, int $createdBy): int
    {
        $slug = $this->makeUniqueSlug($data['name']);
        return $this->insert([
            'name'                 => $data['name'],
            'slug'                 => $slug,
            'type'                 => $data['type'] ?? 'club',
            'status'               => 'pending',
            'province'             => $data['province'] ?? null,
            'autonomous_community' => $data['autonomous_community'] ?? null,
            'country'              => $data['country'] ?? 'España',
            'contact_email'        => $data['contact_email'] ?? null,
            'contact_phone'        => $data['contact_phone'] ?? null,
            'website'              => $data['website'] ?? null,
            'description'          => $data['description'] ?? null,
            'created_by'           => $createdBy,
        ]);
    }

    public function approve(int $id, int $adminUserId): bool
    {
        return $this->update($id, [
            'status'      => 'active',
            'approved_by' => $adminUserId,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function suspend(int $id): bool
    {
        return $this->update($id, ['status' => 'suspended']);
    }

    public function setPresident(int $id, int $userId): bool
    {
        return $this->update($id, ['president_user_id' => $userId]);
    }

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
