<?php
declare(strict_types=1);
namespace Services;

class AuthService
{
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['logged_in'] = true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['logged_in']);
    }

    public static function currentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function currentRole(): string
    {
        return $_SESSION['role'] ?? 'guest';
    }

    public static function isAdmin(): bool
    {
        return in_array($_SESSION['role'] ?? '', ['admin', 'moderator']);
    }

    public static function isPresident(): bool
    {
        return in_array($_SESSION['role'] ?? '', ['president', 'admin']);
    }

    public static function guard(): void
    {
        if (!self::isLoggedIn()) {
            \Helpers\Flash::set('error', 'Debes iniciar sesión para acceder.');
            header('Location: /login');
            exit;
        }
    }

    public static function adminGuard(): void
    {
        self::guard();
        if (!self::isAdmin()) {
            http_response_code(403);
            include APP_PATH . '/Views/errors/403.php';
            exit;
        }
    }
}
