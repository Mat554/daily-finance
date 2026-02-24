<?php
use App\Http\Controllers\FinanceController;


Route::get('/transaction/{transaction}/edit', [FinanceController::class, 'edit'])->name('edit');
Route::put('/transaction/{transaction}', [FinanceController::class, 'update'])->name('update');
Route::get('/', [FinanceController::class, 'index']);
Route::get('/history', [FinanceController::class, 'history'])->name('history');
Route::post('/transaction', [FinanceController::class, 'store'])->name('store');

