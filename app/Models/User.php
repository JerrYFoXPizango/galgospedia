<?php

declare(strict_types=1);

namespace Models;

class User extends BaseModel
{
    protected string $table = 'users';

    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    public function create(array $data): int
    {
        return $this->insert([
            'username'       => $data['username'],
            'email'          => $data['email'],
            'password_hash'  => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'           => 'user',
            'is_active'      => 1,
            'email_verified' => 0,
        ]);
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function markEmailVerified(int $userId): bool
    {
        return $this->update($userId, ['email_verified' => 1]);
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);
    }

    public function updateAvatar(int $userId, string $path): bool
    {
        return $this->update($userId, ['avatar_path' => $path]);
    }

    public function setRole(int $userId, string $role): bool
    {
        return $this->update($userId, ['role' => $role]);
    }

    public function updateClubLogo(int $userId, string $path): bool
    {
        return $this->update($userId, ['club_logo_path' => $path]);
    }

    /** Save email verification or password reset token */
    public function createToken(int $userId, string $type): string
    {
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $this->db->prepare(
            "INSERT INTO auth_tokens (user_id, token, type, expires_at) VALUES (?, ?, ?, ?)"
        )->execute([$userId, $token, $type, $expiresAt]);

        return $token;
    }

    /** Find and validate a token; returns user_id or null if invalid/expired */
    public function consumeToken(string $token, string $type): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM auth_tokens
             WHERE token = ? AND type = ? AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([$token, $type]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        // Mark as used
        $this->db->prepare("UPDATE auth_tokens SET used_at = NOW() WHERE id = ?")
                 ->execute([$row['id']]);

        return (int) $row['user_id'];
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt   = $this->db->prepare(
            "SELECT id, username, email, role, is_active, email_verified, created_at
             FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);
        return [
            'data'  => $stmt->fetchAll(),
            'total' => $this->count(),
            'page'  => $page,
            'perPage' => $perPage,
        ];
    }
}
