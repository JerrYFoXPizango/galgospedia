<?php

namespace Models;

use Config\Database;

class Sponsor
{
    /** Todos los activos ordenados para el carrusel */
    public static function allActive(): array
    {
        $db = Database::pdo();
        return $db->query('SELECT * FROM sponsors WHERE active = 1 ORDER BY sort_order ASC, id ASC')
                  ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Todos (activos + inactivos) para el panel admin */
    public static function all(): array
    {
        $db = Database::pdo();
        return $db->query('SELECT * FROM sponsors ORDER BY sort_order ASC, id ASC')
                  ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $db   = Database::pdo();
        $stmt = $db->prepare('SELECT * FROM sponsors WHERE id = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create(string $name, string $logoPath, ?string $url, int $order): int
    {
        $db = Database::pdo();
        $db->prepare('INSERT INTO sponsors (name, logo_path, website_url, sort_order) VALUES (?, ?, ?, ?)')
           ->execute([$name, $logoPath, $url ?: null, $order]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, string $name, string $logoPath, ?string $url, int $order, int $active): void
    {
        $db = Database::pdo();
        $db->prepare('UPDATE sponsors SET name=?, logo_path=?, website_url=?, sort_order=?, active=? WHERE id=?')
           ->execute([$name, $logoPath, $url ?: null, $order, $active, $id]);
    }

    public static function toggleActive(int $id): void
    {
        $db = Database::pdo();
        $db->prepare('UPDATE sponsors SET active = 1 - active WHERE id = ?')->execute([$id]);
    }

    public static function delete(int $id): ?string
    {
        $sponsor = self::find($id);
        if (!$sponsor) return null;

        $db = Database::pdo();
        $db->prepare('DELETE FROM sponsors WHERE id = ?')->execute([$id]);

        // Devuelve la ruta del logo para que el controlador borre el archivo
        return $sponsor['logo_path'];
    }
}
