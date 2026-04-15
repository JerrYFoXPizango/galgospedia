<?php
declare(strict_types=1);
namespace Models;

class VetRecord extends BaseModel
{
    protected string $table = 'vet_records';

    // ── Etiquetas y colores por tipo ──────────────────────────

    public static function typeLabel(string $t): string
    {
        return match($t) {
            'vaccine'   => 'Vacuna',
            'deworming' => 'Desparasitación',
            'injury'    => 'Lesión',
            'visit'     => 'Visita veterinaria',
            'weight'    => 'Peso / Condición',
            default     => $t,
        };
    }

    public static function typeBadgeClass(string $t): string
    {
        return match($t) {
            'vaccine'   => 'bg-blue-100 text-blue-700',
            'deworming' => 'bg-green-100 text-green-700',
            'injury'    => 'bg-red-100 text-red-700',
            'visit'     => 'bg-purple-100 text-purple-700',
            'weight'    => 'bg-yellow-100 text-yellow-700',
            default     => 'bg-gray-100 text-gray-600',
        };
    }

    public static function typeIcon(string $t): string
    {
        return match($t) {
            'vaccine'   => '💉',
            'deworming' => '🐛',
            'injury'    => '🩹',
            'visit'     => '🏥',
            'weight'    => '⚖️',
            default     => '📋',
        };
    }

    public static function severityLabel(string $s): string
    {
        return match($s) {
            'mild'     => 'Leve',
            'moderate' => 'Moderada',
            'severe'   => 'Grave',
            default    => $s,
        };
    }

    public static function severityClass(string $s): string
    {
        return match($s) {
            'mild'     => 'bg-yellow-100 text-yellow-700',
            'moderate' => 'bg-orange-100 text-orange-700',
            'severe'   => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    // ── Queries principales ───────────────────────────────────

    /** Todos los registros de un galgo (del dueño) */
    public function forDog(int $dogId, int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vet_records
             WHERE dog_id = ? AND user_id = ?
             ORDER BY date DESC, id DESC"
        );
        $stmt->execute([$dogId, $userId]);
        return $stmt->fetchAll();
    }

    /** Registros recientes de un galgo (widget perfil) */
    public function recentForDog(int $dogId, int $userId, int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vet_records
             WHERE dog_id = ? AND user_id = ?
             ORDER BY date DESC LIMIT ?"
        );
        $stmt->execute([$dogId, $userId, $limit]);
        return $stmt->fetchAll();
    }

    /** Lesiones activas (sin fecha de alta) */
    public function activeInjuries(int $dogId, int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vet_records
             WHERE dog_id = ? AND user_id = ? AND type = 'injury' AND resolved_at IS NULL
             ORDER BY date DESC"
        );
        $stmt->execute([$dogId, $userId]);
        return $stmt->fetchAll();
    }

    /** Próximas dosis pendientes (vacunas/desparasitaciones próximas a vencer) */
    public function upcomingDue(int $dogId, int $userId, int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vet_records
             WHERE dog_id = ? AND user_id = ?
               AND next_due_date IS NOT NULL
               AND next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY next_due_date ASC"
        );
        $stmt->execute([$dogId, $userId, $days]);
        return $stmt->fetchAll();
    }

    /** Vencidos (next_due_date pasada) */
    public function overdue(int $dogId, int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vet_records
             WHERE dog_id = ? AND user_id = ?
               AND next_due_date IS NOT NULL
               AND next_due_date < CURDATE()
             ORDER BY next_due_date ASC"
        );
        $stmt->execute([$dogId, $userId]);
        return $stmt->fetchAll();
    }

    /** Semáforo de salud: devuelve 'red' | 'yellow' | 'green' */
    public function healthStatus(int $dogId, int $userId): string
    {
        $injuries = $this->activeInjuries($dogId, $userId);
        if (!empty($injuries)) return 'red';

        $overdue = $this->overdue($dogId, $userId);
        if (!empty($overdue)) return 'yellow';

        return 'green';
    }

    /** Resumen para el hub: galgos con su estado de salud */
    public function summaryByDog(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.slug, d.name, d.gender, d.photo_thumb,
                    COUNT(v.id) AS total_records,
                    SUM(CASE WHEN v.type='injury' AND v.resolved_at IS NULL THEN 1 ELSE 0 END) AS active_injuries,
                    SUM(CASE WHEN v.next_due_date IS NOT NULL AND v.next_due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue,
                    SUM(CASE WHEN v.next_due_date IS NOT NULL AND v.next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS due_soon
             FROM dogs d
             LEFT JOIN vet_records v ON v.dog_id = d.id AND v.user_id = ?
             WHERE d.owner_user_id = ?
             GROUP BY d.id
             ORDER BY d.name ASC"
        );
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }

    public function findOwned(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vet_records WHERE id = ? AND user_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function addRecord(array $data, int $dogId, int $userId): int
    {
        return $this->insert([
            'dog_id'        => $dogId,
            'user_id'       => $userId,
            'type'          => $data['type'],
            'title'         => $data['title'],
            'date'          => $data['date'],
            'next_due_date' => $data['next_due_date'] ?: null,
            'body_part'     => $data['body_part'] ?: null,
            'severity'      => $data['severity'] ?: null,
            'treatment'     => $data['treatment'] ?: null,
            'notes'         => $data['notes'] ?: null,
            'resolved_at'   => $data['resolved_at'] ?: null,
            'weight_kg'     => is_numeric($data['weight_kg'] ?? '') ? (float)$data['weight_kg'] : null,
        ]);
    }

    public function updateRecord(int $id, array $data): bool
    {
        return $this->update($id, [
            'type'          => $data['type'],
            'title'         => $data['title'],
            'date'          => $data['date'],
            'next_due_date' => $data['next_due_date'] ?: null,
            'body_part'     => $data['body_part'] ?: null,
            'severity'      => $data['severity'] ?: null,
            'treatment'     => $data['treatment'] ?: null,
            'notes'         => $data['notes'] ?: null,
            'resolved_at'   => $data['resolved_at'] ?: null,
            'weight_kg'     => is_numeric($data['weight_kg'] ?? '') ? (float)$data['weight_kg'] : null,
        ]);
    }

    public function removeRecord(int $id): bool
    {
        return $this->delete($id);
    }
}
