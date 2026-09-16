<?php

use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

Route::get('/backup/run', [BackupController::class, 'run']);
