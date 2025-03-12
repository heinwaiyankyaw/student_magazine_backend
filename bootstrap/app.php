<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/hwyk/api.php'));
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/mhma/api.php'));
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/mt/api.php'));
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/wyp/api.php'));
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/yzl/api.php'));
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
            Route::middleware('web')
                ->prefix('web')
                ->group(base_path('routes/hwyk/api.php'));
            Route::middleware('web')
                ->prefix('web')
                ->group(base_path('routes/mhma/api.php'));
            Route::middleware('web')
                ->prefix('web')
                ->group(base_path('routes/mt/api.php'));
            Route::middleware('web')
                ->prefix('web')
                ->group(base_path('routes/wyp/api.php'));
            Route::middleware('web')
                ->prefix('web')
                ->group(base_path('routes/yzl/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies
        Request::setTrustedProxies(
            ['*'], // Trust all proxies
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();