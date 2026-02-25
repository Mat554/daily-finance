<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Vercel Serverless Fix: Force storage to /tmp AND create the folders
if (isset($_SERVER['VERCEL_URL'])) {
    $storagePath = '/tmp/storage';
    $app->useStoragePath($storagePath);

    // Vercel gives us a blank /tmp folder. We must build the sub-folders 
    // so Laravel doesn't crash when trying to write sessions or views.
    foreach (['/logs', '/framework/views', '/framework/cache', '/framework/sessions'] as $dir) {
        if (!is_dir($storagePath . $dir)) {
            mkdir($storagePath . $dir, 0777, true);
        }
    }
}

return $app;