<?php

declare(strict_types=1);

// ── Bootstrap ────────────────────────────────────────────────
require_once dirname(__DIR__) . '/bootstrap/app.php';

// ── Router ───────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = '/' . trim($uri, '/');

// ── Dispatch ─────────────────────────────────────────────────
$routes  = \Config\Routes::all();
$matched = false;

foreach ($routes as [$routeMethod, $pattern, $controllerClass, $action, $middleware]) {
    // Convert {param} placeholders to named regex groups
    $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
    $regex = '@^' . $regex . '$@';

    if ($routeMethod !== $method && !($routeMethod === 'GET' && $method === 'HEAD')) {
        continue;
    }

    if (!preg_match($regex, $uri, $params)) {
        continue;
    }

    $matched = true;

    // Filter out numeric keys from preg_match results
    $params = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);

    // ── Run middleware ───────────────────────────────────────
    foreach ($middleware as $mw) {
        $mwClass = match ($mw) {
            'auth'  => \Middleware\AuthMiddleware::class,
            'admin' => \Middleware\AdminMiddleware::class,
            default => null,
        };
        if ($mwClass) {
            (new $mwClass())->handle();
        }
    }

    // ── Dispatch to controller ───────────────────────────────
    $controller = new $controllerClass();
    $controller->$action($params);
    break;
}

if (!$matched) {
    http_response_code(404);
    include APP_PATH . '/Views/errors/404.php';
}
