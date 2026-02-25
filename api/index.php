<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel 12 and handle the request directly
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());