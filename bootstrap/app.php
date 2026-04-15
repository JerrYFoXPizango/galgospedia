<?php
/**
 * Galgospedia — Bootstrap
 * Initializes autoloading, environment, session, and router.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('PUB_PATH',  BASE_PATH . '/public');

// ── Autoloader ──────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    // Convert namespace separators to directory separators
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ── Vendor (Composer) ────────────────────────────────────────
$vendorAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// ── Environment (.env) ───────────────────────────────────────
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\"'");
        if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// ── Session ──────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name('galgo_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (getenv('APP_ENV') === 'production'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Error handling ───────────────────────────────────────────
$debug = getenv('APP_DEBUG') === 'true';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : 0);

if (!$debug) {
    set_error_handler(function (int $errno, string $errstr): bool {
        error_log("[$errno] $errstr");
        return true;
    });
    set_exception_handler(function (Throwable $e): void {
        error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        include APP_PATH . '/Views/errors/500.php';
        exit;
    });
}
