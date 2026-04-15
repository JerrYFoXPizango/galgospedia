<?php

namespace Models;

use Config\Database;

class Training
{
    // ─── Labels ────────────────────────────────────────────────────────────────

    public static function typeLabel(string $type): string
    {
        return [
            'run_free'    => 'Carrera libre',
            'run_hare'    => 'Carrera con liebre',
            'walk'        => 'Paseo',
            'track'       => 'Pista',
            'active_rest' => 'Descanso activo',
            'competition' => 'Competición',
        ][$type] ?? $type;
    }

    public static function typeIcon(string $type): string
    {
        return [
            'run_free'    => '🏃',
            'run_hare'    => '🐇',
            'walk'        => '🦶',
            'track'       => '🏟️',
            'active_rest' => '😴',
            'competition' => '🏆',
        ][$type] ?? '📋';
    }

    public static function typeBadgeClass(string $type): string
    {
        return [
            'run_free'    => 'bg-blue-100 text-blue-700',
            'run_hare'    => 'bg-orange-100 text-orange-700',
            'walk'        => 'bg-green-100 text-green-700',
            'track'       => 'bg-purple-100 text-purple-700',
            'active_rest' => 'bg-gray-100 text-gray-500',
            'competition' => 'bg-yellow-100 text-yellow-700',
        ][$type] ?? 'bg-gray-100 text-gray-600';
    }

    public static function terrainLabel(string $terrain): string
    {
        return [
            'campo'  => 'Campo',
            'monte'  => 'Monte',
            'pista'  => 'Pista',
            'arena'  => 'Arena',
            'hierba' => 'Hierba',
            'barro'  => 'Barro',
            'mixto'  => 'Mixto',
        ][$terrain] ?? $terrain;
    }

    public static function intensityLabel(string $i): string
    {
        return ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'][$i] ?? $i;
    }

    public static function intensityClass(string $i): string
    {
        return [
            'low'    => 'bg-green-100 text-green-700',
            'medium' => 'bg-yellow-100 text-yellow-700',
            'high'   => 'bg-red-100 text-red-700',
        ][$i] ?? 'bg-gray-100 text-gray-600';
    }

    public static function conditionLabel(string $c): string
    {
        return [
            'good'       => 'Bien',
            'tired'      => 'Cansado',
            'very_tired' => 'Muy cansado',
        ][$c] ?? $c;
    }

    public static function conditionIcon(string $c): string
    {
        return ['good' => '😊', 'tired' => '😓', 'very_tired' => '😩'][$c] ?? '';
    }

    // ─── Distance helpers ──────────────────────────────────────────────────────

    /** Formatea metros de forma legible: < 1000 → "450 m", >= 1000 → "3.5 km" */
    public static function formatDistance(?int $meters): string
    {
        if ($meters === null) return '—';
        if ($meters < 1000) return $meters . ' m';
        return number_format($meters / 1000, 1) . ' km';
    }

    public static function metersToKm(?int $meters): float
    {
        return $meters ? round($meters / 1000, 2) : 0.0;
    }

    // ─── Config ────────────────────────────────────────────────────────────────

    public static function getConfig(int $dogId): array
    {
        $db = Database::pdo();
        $stmt = $db->prepare('SELECT * FROM training_config WHERE dog_id = ?');
        $stmt->execute([$dogId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [
            'max_weekly_km'               => 30.00,
            'max_consecutive_high'        => 3,
            'rest_days_after_competition' => 2,
        ];
    }

    public static function saveConfig(int $dogId, int $userId, float $maxWeeklyKm, int $maxConsecutiveHigh, int $restDays): void
    {
        $db = Database::pdo();
        $db->prepare('
            INSERT INTO training_config (dog_id, user_id, max_weekly_km, max_consecutive_high, rest_days_after_competition)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                max_weekly_km = VALUES(max_weekly_km),
                max_consecutive_high = VALUES(max_consecutive_high),
                rest_days_after_competition = VALUES(rest_days_after_competition)
        ')->execute([$dogId, $userId, $maxWeeklyKm, $maxConsecutiveHigh, $restDays]);
    }

    // ─── CRUD sesiones ─────────────────────────────────────────────────────────

    public static function forDog(int $dogId, int $userId): array
    {
        $db = Database::pdo();
        $stmt = $db->prepare('
            SELECT * FROM training_sessions
            WHERE dog_id = ? AND user_id = ?
            ORDER BY date DESC, id DESC
        ');
        $stmt->execute([$dogId, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findOwned(int $id, int $userId): ?array
    {
        $db = Database::pdo();
        $stmt = $db->prepare('SELECT * FROM training_sessions WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function addSession(int $dogId, int $userId, array $data): int
    {
        $db = Database::pdo();
        $db->prepare('
            INSERT INTO training_sessions
                (dog_id, user_id, date, type, terrain, distance_m, duration_min, intensity, dog_condition, temperature_c, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ')->execute([
            $dogId, $userId,
            $data['date'],
            $data['type'],
            $data['terrain']      ?: null,
            $data['distance_m']   ?: null,
            $data['duration_min'] ?: null,
            $data['intensity'],
            $data['dog_condition'] ?: null,
            $data['temperature_c'] !== '' ? $data['temperature_c'] : null,
            $data['notes']        ?: null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function updateSession(int $id, array $data): void
    {
        $db = Database::pdo();
        $db->prepare('
            UPDATE training_sessions
            SET date=?, type=?, terrain=?, distance_m=?, duration_min=?, intensity=?,
                dog_condition=?, temperature_c=?, notes=?
            WHERE id=?
        ')->execute([
            $data['date'],
            $data['type'],
            $data['terrain']      ?: null,
            $data['distance_m']   ?: null,
            $data['duration_min'] ?: null,
            $data['intensity'],
            $data['dog_condition'] ?: null,
            $data['temperature_c'] !== '' ? $data['temperature_c'] : null,
            $data['notes']        ?: null,
            $id,
        ]);
    }

    public static function removeSession(int $id): void
    {
        $db = Database::pdo();
        $db->prepare('DELETE FROM training_sessions WHERE id = ?')->execute([$id]);
    }

    // ─── Estadísticas ──────────────────────────────────────────────────────────

    /** Kilómetros totales en la semana actual (lunes-domingo) */
    public static function weeklyKm(int $dogId, int $userId): float
    {
        $db = Database::pdo();
        $monday = date('Y-m-d', strtotime('monday this week'));
        $sunday = date('Y-m-d', strtotime('sunday this week'));
        $stmt = $db->prepare('
            SELECT COALESCE(SUM(distance_m), 0) as total_m
            FROM training_sessions
            WHERE dog_id = ? AND user_id = ? AND date BETWEEN ? AND ?
        ');
        $stmt->execute([$dogId, $userId, $monday, $sunday]);
        return round(($stmt->fetchColumn() ?? 0) / 1000, 2);
    }

    /** Días consecutivos (hasta hoy) con sesiones de intensidad alta */
    public static function consecutiveHighDays(int $dogId, int $userId): int
    {
        $db = Database::pdo();
        $stmt = $db->prepare('
            SELECT DISTINCT date FROM training_sessions
            WHERE dog_id = ? AND user_id = ? AND intensity = "high"
            ORDER BY date DESC
        ');
        $stmt->execute([$dogId, $userId]);
        $dates = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'date');

        $count = 0;
        $check = date('Y-m-d');
        foreach ($dates as $d) {
            if ($d === $check) {
                $count++;
                $check = date('Y-m-d', strtotime($check . ' -1 day'));
            } elseif ($d < $check) {
                break;
            }
        }
        return $count;
    }

    /** Fecha de la última competición (tipo competition en training o null) */
    public static function lastCompetitionDate(int $dogId, int $userId): ?string
    {
        $db = Database::pdo();
        $stmt = $db->prepare('
            SELECT date FROM training_sessions
            WHERE dog_id = ? AND user_id = ? AND type = "competition"
            ORDER BY date DESC LIMIT 1
        ');
        $stmt->execute([$dogId, $userId]);
        $r = $stmt->fetchColumn();
        return $r ?: null;
    }

    /** Días desde la última sesión de cualquier tipo */
    public static function daysSinceLastSession(int $dogId, int $userId): ?int
    {
        $db = Database::pdo();
        $stmt = $db->prepare('
            SELECT date FROM training_sessions
            WHERE dog_id = ? AND user_id = ?
            ORDER BY date DESC LIMIT 1
        ');
        $stmt->execute([$dogId, $userId]);
        $last = $stmt->fetchColumn();
        if (!$last) return null;
        return (int)(new \DateTime())->diff(new \DateTime($last))->days;
    }

    /**
     * Semáforo de sobreentrenamiento.
     * Fusión criterio A (días consecutivos alta intensidad) + B (km semanales).
     * Returns: 'red' | 'orange' | 'green' | 'gray'
     */
    public static function overtrain(int $dogId, int $userId): array
    {
        $cfg         = self::getConfig($dogId);
        $weeklyKm    = self::weeklyKm($dogId, $userId);
        $consHigh    = self::consecutiveHighDays($dogId, $userId);
        $maxKm       = (float)$cfg['max_weekly_km'];
        $maxConsHigh = (int)$cfg['max_consecutive_high'];

        $kmPct = $maxKm > 0 ? ($weeklyKm / $maxKm) : 0;

        // RED: supera límites
        if ($consHigh >= $maxConsHigh || $kmPct >= 1.0) {
            return [
                'status' => 'red',
                'label'  => 'Sobreentrenamiento',
                'detail' => $consHigh >= $maxConsHigh
                    ? "{$consHigh} días seguidos alta intensidad"
                    : number_format($weeklyKm, 1) . " km esta semana (límite {$maxKm})",
            ];
        }

        // ORANGE: cerca del límite (≥85% km o ≥ maxConsHigh-1 días)
        if ($kmPct >= 0.85 || $consHigh >= $maxConsHigh - 1) {
            return [
                'status' => 'orange',
                'label'  => 'Cerca del límite',
                'detail' => $kmPct >= 0.85
                    ? number_format($weeklyKm, 1) . " / {$maxKm} km esta semana"
                    : "{$consHigh} días seguidos alta intensidad",
            ];
        }

        // GRAY: sin datos recientes
        $days = self::daysSinceLastSession($dogId, $userId);
        if ($days === null) {
            return ['status' => 'gray', 'label' => 'Sin registros', 'detail' => 'Añade la primera sesión'];
        }

        return [
            'status' => 'green',
            'label'  => 'Carga normal',
            'detail' => number_format($weeklyKm, 1) . " / {$maxKm} km esta semana",
        ];
    }

    /**
     * Últimas N semanas con estadísticas para el gráfico.
     * Devuelve array de semanas (más reciente primero) con:
     * total_m, sessions, high_count, medium_count, low_count, week_label
     */
    public static function recentWeeks(int $dogId, int $userId, int $weeks = 6): array
    {
        $db = Database::pdo();
        $result = [];

        for ($i = 0; $i < $weeks; $i++) {
            $monday = date('Y-m-d', strtotime("monday this week -{$i} weeks"));
            $sunday = date('Y-m-d', strtotime("sunday this week -{$i} weeks"));

            $stmt = $db->prepare('
                SELECT
                    COALESCE(SUM(distance_m), 0)                          AS total_m,
                    COUNT(*)                                               AS sessions,
                    SUM(intensity = "high")                               AS high_count,
                    SUM(intensity = "medium")                             AS medium_count,
                    SUM(intensity = "low")                                AS low_count,
                    SUM(type = "competition")                             AS competition_count
                FROM training_sessions
                WHERE dog_id = ? AND user_id = ? AND date BETWEEN ? AND ?
            ');
            $stmt->execute([$dogId, $userId, $monday, $sunday]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            $label = $i === 0 ? 'Esta sem.' : ($i === 1 ? 'Sem. ant.' : 'S-' . $i);

            $result[] = [
                'monday'            => $monday,
                'sunday'            => $sunday,
                'label'             => $label,
                'total_m'           => (int)$row['total_m'],
                'total_km'          => round($row['total_m'] / 1000, 1),
                'sessions'          => (int)$row['sessions'],
                'high_count'        => (int)$row['high_count'],
                'medium_count'      => (int)$row['medium_count'],
                'low_count'         => (int)$row['low_count'],
                'competition_count' => (int)$row['competition_count'],
            ];
        }

        return array_reverse($result); // cronológico (más antiguo primero)
    }

    /** Stats globales del mes actual */
    public static function monthStats(int $dogId, int $userId): array
    {
        $db = Database::pdo();
        $firstDay = date('Y-m-01');
        $lastDay  = date('Y-m-t');

        $stmt = $db->prepare('
            SELECT
                COUNT(*)                     AS sessions,
                COALESCE(SUM(distance_m), 0) AS total_m,
                COALESCE(SUM(duration_min),0) AS total_min,
                MAX(distance_m)              AS best_distance_m
            FROM training_sessions
            WHERE dog_id = ? AND user_id = ? AND date BETWEEN ? AND ?
        ');
        $stmt->execute([$dogId, $userId, $firstDay, $lastDay]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /** Terreno más usado (último mes) */
    public static function favoriteTerrain(int $dogId, int $userId): ?string
    {
        $db = Database::pdo();
        $stmt = $db->prepare('
            SELECT terrain, COUNT(*) as c FROM training_sessions
            WHERE dog_id = ? AND user_id = ? AND terrain IS NOT NULL
              AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY terrain ORDER BY c DESC LIMIT 1
        ');
        $stmt->execute([$dogId, $userId]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $r ? $r['terrain'] : null;
    }

    /** Resumen por galgo para el hub */
    public static function summaryByDog(int $userId): array
    {
        $db = Database::pdo();
        $monday = date('Y-m-d', strtotime('monday this week'));
        $sunday = date('Y-m-d', strtotime('sunday this week'));

        $stmt = $db->prepare('
            SELECT
                d.id, d.name, d.slug, d.photo_thumb,
                COUNT(ts.id)                                              AS total_sessions,
                COALESCE(SUM(CASE WHEN ts.date BETWEEN ? AND ? THEN ts.distance_m ELSE 0 END),0) AS week_m,
                MAX(ts.date)                                              AS last_session,
                SUM(CASE WHEN ts.date BETWEEN ? AND ? AND ts.intensity="high" THEN 1 ELSE 0 END) AS week_high
            FROM dogs d
            LEFT JOIN training_sessions ts ON ts.dog_id = d.id AND ts.user_id = d.owner_user_id
            WHERE d.owner_user_id = ?
            GROUP BY d.id
            ORDER BY d.name ASC
        ');
        $stmt->execute([$monday, $sunday, $monday, $sunday, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
