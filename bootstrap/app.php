<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsurePhoneIsVerified;
use App\Http\Middleware\EnsureUserHasAdminRole;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'phone.verified' => EnsurePhoneIsVerified::class,
            'account.active' => EnsureAccountIsActive::class,
            'admin' => EnsureUserHasAdminRole::class,
            'superadmin' => EnsureUserIsSuperAdmin::class,
        ]);

        // Session selection must be applied to every web route, including
        // guest pages and the language switch itself.
        $middleware->web(append: [SetLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
