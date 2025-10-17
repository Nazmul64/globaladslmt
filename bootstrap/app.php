<?php

use App\Http\Middleware\Adminmiddelware;
use App\Http\Middleware\AgentMiddleware;
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
        'is_admin' => Adminmiddelware::class,
        'agent' => AgentMiddleware::class,
        'user' => AgentMiddleware::class
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

    })->create();
