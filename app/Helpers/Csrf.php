<?php
declare(strict_types=1);
namespace Helpers;

class Csrf
{
    private const KEY = '_csrf_token';

    public static function generate(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token) . '">';
    }

    public static function verify(): void
    {
        $token    = $_POST['_csrf'] ?? '';
        $expected = $_SESSION[self::KEY] ?? '';
        if (!$expected || !hash_equals($expected, $token)) {
            http_response_code(419);
            die('Token CSRF inválido. Recarga la página e intenta de nuevo.');
        }
        // Rotate token after use
        unset($_SESSION[self::KEY]);
    }
}
