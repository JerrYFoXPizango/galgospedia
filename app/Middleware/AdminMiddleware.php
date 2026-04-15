<?php
declare(strict_types=1);
namespace Middleware;

use Services\AuthService;

class AdminMiddleware
{
    public function handle(): void
    {
        AuthService::adminGuard();
    }
}
