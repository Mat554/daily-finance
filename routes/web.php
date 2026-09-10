<?php
use App\Http\Controllers\FinanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUsername;

// 1. Show the simple login form
Route::get('/login', function () {
    return view('login');
})->name('login');

// 2. Save the name and redirect based on the user's preferred landing
Route::post('/login', function (Request $request) {
    $request->validate(['username' => 'required|string|min:2']);
    $username = trim($request->username);
    session(['username' => $username]);
    // Default to 'tracker' for fresh sessions. Users can switch any time via POST /preference.
    session(['landing' => 'tracker']);

    $landing = session('landing', 'tracker');
    if ($landing === 'dashboard') {
        return redirect('/dashboard');
    }
    return redirect('/');
});

// 3. Log Out
Route::get('/logout', function () {
    session()->forget('username');
    // Keep 'landing' preference so the user returns to their chosen default
    return redirect('/login');
});

// 4. Protect your main routes using ONLY the Class!
Route::middleware([CheckUsername::class])->group(function () {
    Route::delete('/transaction/{transaction}', [FinanceController::class, 'destroy'])->name('destroy');

    // Analytics dashboard — available to all logged-in users
    Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::post('/dashboard/distribution', [FinanceController::class, 'saveDistribution'])->name('saveDistribution');
    Route::post('/dashboard/distribution-template', [FinanceController::class, 'saveDistributionTemplate'])->name('saveDistributionTemplate');
    Route::post('/dashboard/distribution-template/delete', [FinanceController::class, 'deleteDistributionTemplate'])->name('deleteDistributionTemplate');
    Route::post('/dashboard/account-balances', [FinanceController::class, 'saveAccountBalances'])->name('saveAccountBalances');

    // Tracker — the default landing for most users
    Route::get('/', [FinanceController::class, 'index']);

    Route::get('/history', [FinanceController::class, 'history'])->name('history');
    Route::post('/transaction', [FinanceController::class, 'store'])->name('store');
    Route::get('/transaction/{transaction}/edit', [FinanceController::class, 'edit'])->name('edit');
    Route::put('/transaction/{transaction}', [FinanceController::class, 'update'])->name('update');

    // User toggles their default landing page (tracker <-> dashboard)
    Route::post('/preference', [FinanceController::class, 'setPreference'])->name('preference');
});