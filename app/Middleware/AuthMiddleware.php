<?php
declare(strict_types=1);
namespace Middleware;

use Services\AuthService;

class AuthMiddleware
{
    public function handle(): void
    {
        AuthService::guard();
    }
}
