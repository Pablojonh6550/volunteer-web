<?php

use App\Http\Middleware\EnsureUserType;
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
        // Habilita a autenticação stateful do Sanctum (cookie de sessão + CSRF)
        // para requisições vindas do SPA Vue servido pelo mesmo domínio.
        $middleware->statefulApi();

        $middleware->alias([
            'user.type' => EnsureUserType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Logs sem informações sensíveis: estes campos nunca são gravados na
        // sessão/“old input” nem aparecem em relatórios de exceção.
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'document',
        ]);

        // Contexto mínimo e não sensível para cada log de erro (sem e-mail, nome ou IP).
        $exceptions->context(fn () => [
            'user_id' => auth()->id(),
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
