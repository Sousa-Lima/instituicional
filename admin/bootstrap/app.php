<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'api.public' => \App\Http\Middleware\AuthenticateJwtOrApiReadToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Evitar HTML em erros ao testar no Swagger (Accept por vezes ausente ou */*).
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return str_starts_with($request->path(), 'api/v1/')
                || $request->is('docs');
        });
    })->create();
