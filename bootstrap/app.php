<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SetAppUserIdForRls;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => Authenticate::class,
            'role' => EnsureUserHasRole::class,
            'set.rls' => SetAppUserIdForRls::class,
        ]);

        // The API uses the session guard, so the session must be started.
        $middleware->prependToGroup('api', [
            StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
