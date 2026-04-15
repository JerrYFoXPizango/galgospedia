<?php
declare(strict_types=1);
namespace Controllers;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = APP_PATH . '/Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $view");
        }
        require $viewFile;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $url, int $code = 302): void
    {
        header("Location: $url", true, $code);
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
    }

    protected function query(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
    }

    protected function currentUserId(): ?int
    {
        return \Services\AuthService::currentUserId();
    }
}
