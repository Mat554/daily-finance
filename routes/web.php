<?php
use App\Http\Controllers\FinanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUsername;

// 1. Show the simple login form
Route::get('/login', function () {
    return view('login');
})->name('login');

// 2. Save the name and redirect — Matius gets the analytics dashboard
Route::post('/login', function (Request $request) {
    $request->validate(['username' => 'required|string|min:2']);
    $username = trim($request->username);
    session(['username' => $username]);
    $isMatius = strtolower($username) === 'matius';
    return redirect($isMatius ? '/dashboard' : '/');
});

// 3. Log Out
Route::get('/logout', function () {
    session()->forget('username');
    return redirect('/login');
});

// 4. Protect your main routes using ONLY the Class!
Route::middleware([CheckUsername::class])->group(function () {
    Route::delete('/transaction/{transaction}', [FinanceController::class, 'destroy'])->name('destroy');

    // Matius gets the analytics dashboard
    Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');

    // Others get the tracker — but Matius visiting "/" goes to dashboard instead
    Route::get('/', function () {
        if (strtolower(session('username', '')) === 'matius') {
            return redirect('/dashboard');
        }
        return app(FinanceController::class)->index(request());
    });

    Route::get('/history', [FinanceController::class, 'history'])->name('history');
    Route::post('/transaction', [FinanceController::class, 'store'])->name('store');
    Route::get('/transaction/{transaction}/edit', [FinanceController::class, 'edit'])->name('edit');
    Route::put('/transaction/{transaction}', [FinanceController::class, 'update'])->name('update');

});