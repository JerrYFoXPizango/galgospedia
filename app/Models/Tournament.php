<?php
declare(strict_types=1);
namespace Models;

class Tournament extends BaseModel
{
    protected string $table = 'tournaments';

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.username AS creator_username
             FROM tournaments t
             LEFT JOIN users u ON u.id = t.created_by
             WHERE t.slug = ? LIMIT 1"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listing(int $page, int $perPage, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ["t.status = 'published'"];
        $params = [];

        if (!empty($filters['discipline'])) {
            $where[]  = 't.discipline = ?';
            $params[] = $filters['discipline'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(t.title LIKE ? OR t.location_name LIKE ? OR t.location_address LIKE ?)';
            $like     = '%' . $filters['q'] . '%';
            $params   = array_merge($params, [$like, $like, $like]);
        }
        if (!empty($filters['upcoming_only'])) {
            $where[] = 't.starts_at >= NOW()';
        }

        $whereStr = implode(' AND ', $where);
        $stmt     = $this->db->prepare(
            "SELECT t.*, u.username AS creator_username
             FROM tournaments t
             LEFT JOIN users u ON u.id = t.created_by
             WHERE $whereStr
             ORDER BY t.starts_at ASC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, $perPage, $offset]);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tournaments t WHERE $whereStr"
        );
        $countStmt->execute($params);

        return [
            'data'    => $stmt->fetchAll(),
            'total'   => (int) $countStmt->fetchColumn(),
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }

    public function adminListing(int $page, int $perPage, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['discipline'])) {
            $where[]  = 't.discipline = ?';
            $params[] = $filters['discipline'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 't.status = ?';
            $params[] = $filters['status'];
        }

        $whereStr = implode(' AND ', $where);
        $stmt     = $this->db->prepare(
            "SELECT t.*, u.username AS creator_username
             FROM tournaments t
             LEFT JOIN users u ON u.id = t.created_by
             WHERE $whereStr
             ORDER BY t.starts_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, $perPage, $offset]);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tournaments t WHERE $whereStr"
        );
        $countStmt->execute($params);

        return [
            'data'    => $stmt->fetchAll(),
            'total'   => (int) $countStmt->fetchColumn(),
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }

    public function create(array $data, int $createdBy): int
    {
        $slug = $this->makeUniqueSlug($data['title']);
        return $this->insert([
            'slug'                  => $slug,
            'title'                 => $data['title'],
            'discipline'            => $data['discipline'],
            'category'              => $data['category'] ?: null,
            'starts_at'             => $data['starts_at'],
            'ends_at'               => $data['ends_at'] ?: null,
            'location_name'         => $data['location_name'] ?: null,
            'location_address'      => $data['location_address'] ?: null,
            'location_lat'          => $data['location_lat'],
            'location_lng'          => $data['location_lng'],
            'meeting_point'         => $data['meeting_point'] ?: null,
            'meeting_time'          => $data['meeting_time'] ?: null,
            'map_url'               => $data['map_url'] ?: null,
            'notes'                 => $data['notes'] ?: null,
            'description'           => $data['description'] ?: null,
            'organizer_name'        => $data['organizer_name'] ?: null,
            'contact_info'          => $data['contact_info'] ?: null,
            'registration_required' => (int) ($data['registration_required'] ?? 0),
            'registration_url'      => $data['registration_url'] ?: null,
            'max_participants'      => $data['max_participants'],
            'poster'                => $data['poster'] ?? null,
            'status'                => $data['status'] ?? 'published',
            'created_by'            => $createdBy,
        ]);
    }

    public function updateInfo(int $id, array $data): bool
    {
        $allowed = [
            'title','discipline','category','starts_at','ends_at',
            'location_name','location_address','location_lat','location_lng',
            'meeting_point','meeting_time','map_url','notes','description',
            'organizer_name','contact_info','registration_required','registration_url',
            'max_participants','poster','status',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->update($id, $filtered);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    public function remove(int $id): bool
    {
        return $this->delete($id);
    }

    public function countPublished(): int
    {
        return $this->count("status = 'published'");
    }

    private function makeUniqueSlug(string $title): string
    {
        $base = \Helpers\Slugify::make($title);
        $slug = $base;
        $i    = 1;
        while ($this->findBy('slug', $slug)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
