<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'track.login' => \App\Http\Middleware\TrackLoginActivity::class,
            'permission.404' => \App\Http\Middleware\PermissionOr404::class,
            'redirect.after.login' => \App\Http\Middleware\RedirectAfterLogin::class,
        ]);
        
        // Áp dụng middleware redirect.after.login cho tất cả các request
        $middleware->append(\App\Http\Middleware\RedirectAfterLogin::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
