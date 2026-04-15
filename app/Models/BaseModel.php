<?php

declare(strict_types=1);

namespace Models;

use Config\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::pdo();
    }

    protected function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    protected function findBy(string $column, mixed $value): ?array
    {
        // Validate column name: only letters, digits, underscores
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new \InvalidArgumentException("Invalid column name: $column");
        }
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `$column` = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    protected function all(string $orderBy = 'created_at DESC', int $limit = 100, int $offset = 0): array
    {
        // Validate orderBy: only allow col names, spaces, commas, ASC/DESC
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*(\s+(ASC|DESC))?(\s*,\s*[a-zA-Z_][a-zA-Z0-9_.]*(\s+(ASC|DESC))?)*$/i', $orderBy)) {
            $orderBy = 'created_at DESC';
        }
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` ORDER BY $orderBy LIMIT $limit OFFSET $offset"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    protected function count(string $where = '1=1', array $params = []): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    protected function insert(array $data): int
    {
        $cols        = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare(
            "INSERT INTO `{$this->table}` ($cols) VALUES ($placeholders)"
        );
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    protected function update(int $id, array $data): bool
    {
        $set  = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET $set WHERE id = ?");
        return $stmt->execute([...array_values($data), $id]);
    }

    protected function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
