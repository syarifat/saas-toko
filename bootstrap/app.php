<?php

use App\Http\Middleware\CekAddon;
use App\Http\Middleware\CekPaket;
use App\Http\Middleware\EnsurePeran;
use App\Http\Middleware\EnsureTokoContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'peran' => EnsurePeran::class,
            'konteks_toko' => EnsureTokoContext::class,
            'paket' => CekPaket::class,
            'addon' => CekAddon::class,
        ]);

        $middleware->web(append: [
            EnsureTokoContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
