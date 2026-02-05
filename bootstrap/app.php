<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BossMiddleware;
use App\Http\Middleware\CaisseMiddleware;
use App\Http\Middleware\CaisseControllerMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\Mt1Middleware;
use App\Http\Middleware\PoolControleMiddleware;
use App\Http\Middleware\NumerisationMiddleware;
use App\Http\Middleware\RafMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        // Ajoutez votre middleware ValidatePostSize au groupe 'web'
        $middleware->web(append: [
            \App\Http\Middleware\ValidatePostSize::class,
        ]);

        // Middleware personnalisés
        $middleware->alias([
            'PoolControle' => PoolControleMiddleware::class,
            'Numerisation' => NumerisationMiddleware::class,
            'Admin' => AdminMiddleware::class,
            'Caisse' => CaisseMiddleware::class,
            'MT1' => Mt1Middleware::class,
            'Boss' => BossMiddleware::class,
            'CaisseController' => CaisseControllerMiddleware::class,
            'Raf' => RafMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
