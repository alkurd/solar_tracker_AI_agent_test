<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

if (PHP_VERSION_ID >= 80500) {
    set_error_handler(static function (int $severity, string $message, string $file): bool {
        $normalizedFile = str_replace('\\', '/', $file);

        return $severity === E_DEPRECATED
            && str_contains($message, 'PDO::MYSQL_ATTR_SSL_CA')
            && str_ends_with($normalizedFile, '/config/database.php');
    });
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
