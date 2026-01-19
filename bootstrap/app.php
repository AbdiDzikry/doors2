<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        // Register global web middleware for SSO auto-login
        $middleware->web(append: [
            \App\Http\Middleware\SSOTokenMiddleware::class,
        ]);
        
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'sso.token' => \App\Http\Middleware\SSOTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('sync:employees')->daily();
        $schedule->command('meeting:cancel-unconfirmed')->everyMinute();
        $schedule->command('meetings:cancel-unattended')->everyFiveMinutes();
        $schedule->command('app:update-room-status')->everyMinute();
    })->create();
