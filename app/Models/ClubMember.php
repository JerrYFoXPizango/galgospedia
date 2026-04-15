<?php

declare(strict_types=1);

namespace Models;

class ClubMember extends BaseModel
{
    protected string $table = 'club_members';

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    /** Full member list for a club, ordered by status then name */
    public function listByClub(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT cm.*,
                    u.username,
                    CASE cm.status
                        WHEN 'active'    THEN 1
                        WHEN 'pending'   THEN 2
                        WHEN 'suspended' THEN 3
                        ELSE 4
                    END AS status_order
             FROM club_members cm
             LEFT JOIN users u ON u.id = cm.user_id
             WHERE cm.club_id = ?
             ORDER BY status_order ASC, cm.name ASC"
        );
        $stmt->execute([$clubId]);
        return $stmt->fetchAll();
    }

    /** Aggregate stats for a club */
    public function statsByClub(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*)                                                                        AS total,
                SUM(status = 'active')                                                          AS active,
                SUM(status = 'pending')                                                         AS pending,
                SUM(status = 'suspended')                                                       AS suspended,
                SUM(status = 'active' AND license_expires_at IS NOT NULL
                    AND license_expires_at < CURDATE())                                         AS expired,
                SUM(status = 'active' AND license_expires_at IS NOT NULL
                    AND license_expires_at >= CURDATE()
                    AND license_expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))             AS expiring_soon
             FROM club_members
             WHERE club_id = ?"
        );
        $stmt->execute([$clubId]);
        $row = $stmt->fetch();
        return array_map('intval', $row);
    }

    /** Members with expired or soon-expiring licenses (semáforo) */
    public function licenseAlerts(int $clubId): array
    {
        $stmt = $this->db->prepare(
            "SELECT cm.*,
                    CASE
                        WHEN license_expires_at < CURDATE()                                    THEN 'expired'
                        WHEN license_expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)        THEN 'expiring_soon'
                    END AS alert_type,
                    DATEDIFF(license_expires_at, CURDATE()) AS days_left
             FROM club_members cm
             WHERE cm.club_id = ?
               AND cm.status  = 'active'
               AND cm.license_expires_at IS NOT NULL
               AND cm.license_expires_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             ORDER BY cm.license_expires_at ASC"
        );
        $stmt->execute([$clubId]);
        return $stmt->fetchAll();
    }

    /** Add a member manually (president adds socio not yet registered) */
    public function add(array $data, int $addedBy): int
    {
        return $this->insert([
            'club_id'            => $data['club_id'],
            'user_id'            => $data['user_id'] ?? null,
            'name'               => $data['name'],
            'email'              => $data['email'] ?? null,
            'phone'              => $data['phone'] ?? null,
            'license_number'     => $data['license_number'] ?? null,
            'license_type'       => $data['license_type'] ?? null,
            'license_expires_at' => $data['license_expires_at'] ?? null,
            'status'             => $data['status'] ?? 'active',
            'is_delegate'        => (int) ($data['is_delegate'] ?? 0),
            'notes'              => $data['notes'] ?? null,
            'added_by'           => $addedBy,
        ]);
    }

    public function updateMember(int $id, array $data): bool
    {
        $allowed = [
            'name','email','phone','license_number','license_type',
            'license_expires_at','status','is_delegate','notes',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->update($id, $filtered);
    }

    public function approve(int $id): bool
    {
        return $this->update($id, ['status' => 'active']);
    }

    public function suspend(int $id): bool
    {
        return $this->update($id, ['status' => 'suspended']);
    }

    public function remove(int $id): bool
    {
        return $this->delete($id);
    }
}
