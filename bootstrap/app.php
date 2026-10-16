<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['jwt.web' => \App\Http\Middleware\EnsureJwtToken::class]);
        // Cookie jwt_token dipasang mentah via JS, jadi dikecualikan dari enkripsi cookie.
        $middleware->encryptCookies(except: ['jwt_token']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
