<?php

use App\Http\Middleware\CekModul;
use App\Http\Middleware\EnsureTokoContext;
use App\Http\Middleware\Peran;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'peran' => Peran::class,
            'konteks_toko' => EnsureTokoContext::class,
            'modul' => CekModul::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if (isset($_ENV['VERCEL']) || env('VERCEL') || env('LARAVEL_STORAGE_PATH')) {
    $app->useStoragePath(env('LARAVEL_STORAGE_PATH', '/tmp/storage'));
}

return $app;
