<?php
use App\Http\Controllers\FinanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUsername; // <-- We are importing the class here!

// 1. Show the simple login form
Route::get('/login', function () {
    return view('login');
})->name('login');

// 2. Save the name and redirect to the tracker
Route::post('/login', function (Request $request) {
    $request->validate(['username' => 'required|string|min:2']);
    session(['username' => $request->username]); 
    return redirect('/');
});

// 3. Log Out
Route::get('/logout', function () {
    session()->forget('username');
    return redirect('/login');
});

// 4. Protect your main routes using ONLY the Class!
Route::middleware([CheckUsername::class])->group(function () {
    Route::delete('/transaction/{transaction}', [FinanceController::class, 'destroy'])->name('destroy');
    // All your normal routes go inside here!
    Route::get('/', [FinanceController::class, 'index']);
    Route::get('/history', [FinanceController::class, 'history'])->name('history');
    Route::post('/transaction', [FinanceController::class, 'store'])->name('store');
    Route::get('/transaction/{transaction}/edit', [FinanceController::class, 'edit'])->name('edit');
    Route::put('/transaction/{transaction}', [FinanceController::class, 'update'])->name('update');

});