<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpensePaymentController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Middleware\CheckUsername;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::delete('/transaction/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');

    // Analytics dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/distribution', [DashboardController::class, 'saveDistribution'])->name('saveDistribution');
    Route::post('/dashboard/distribution-template', [DashboardController::class, 'saveDistributionTemplate'])->name('saveDistributionTemplate');
    Route::post('/dashboard/distribution-template/delete', [DashboardController::class, 'deleteDistributionTemplate'])->name('deleteDistributionTemplate');
    Route::post('/dashboard/account-balances', [DashboardController::class, 'saveAccountBalances'])->name('saveAccountBalances');

    // Tracker — the default landing for most users
    Route::get('/', [TrackerController::class, 'index']);

    Route::get('/history', [TrackerController::class, 'history'])->name('history');
    Route::post('/transaction', [TransactionController::class, 'store'])->name('store');
    Route::get('/transaction/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
    Route::put('/transaction/{transaction}', [TransactionController::class, 'update'])->name('update');

    // User toggles their default landing page (tracker <-> dashboard)
    Route::post('/preference', [SettingsController::class, 'setPreference'])->name('preference');

    // Monthly Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/expenses/{expense}/toggle', [ExpenseController::class, 'toggle'])->name('expenses.toggle');

    // Expense Payments
    Route::post('/expenses/{expense}/payments', [ExpensePaymentController::class, 'store'])->name('expenses.payments.store');
    Route::delete('/expenses/{expense}/payments/{payment}', [ExpensePaymentController::class, 'destroy'])->name('expenses.payments.destroy');

    // Monthly Report
    Route::get('/monthly-report/{year}/{month}', [MonthlyReportController::class, 'show'])->name('monthly-report.show');
    Route::post('/monthly-report/{year}/{month}', [MonthlyReportController::class, 'generate'])->name('monthly-report.generate');

    // Savings Goal
    Route::get('/savings-goal', [SavingsGoalController::class, 'index'])->name('savings-goal.index');
    Route::post('/savings-goal', [SavingsGoalController::class, 'store'])->name('savings-goal.store');
    Route::delete('/savings-goal', [SavingsGoalController::class, 'destroy'])->name('savings-goal.destroy');
});
