<?php

// Power up the autoloader manually
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);