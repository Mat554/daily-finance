<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpensePaymentController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Middleware\CheckUsername;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', fn() => redirect('/login'));

// Protected routes
Route::middleware([CheckUsername::class])->group(function () {
    Route::delete('/transaction/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/distribution', [DashboardController::class, 'saveDistribution'])->name('saveDistribution');
    Route::post('/dashboard/distribution-template', [DashboardController::class, 'saveDistributionTemplate'])->name('saveDistributionTemplate');
    Route::post('/dashboard/distribution-template/delete', [DashboardController::class, 'deleteDistributionTemplate'])->name('deleteDistributionTemplate');
    Route::post('/dashboard/account-balances', [DashboardController::class, 'saveAccountBalances'])->name('saveAccountBalances');

    Route::get('/', [TrackerController::class, 'index']);
    Route::get('/history', [TrackerController::class, 'history'])->name('history');
    Route::post('/transaction', [TransactionController::class, 'store'])->name('store');
    Route::get('/transaction/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
    Route::put('/transaction/{transaction}', [TransactionController::class, 'update'])->name('update');

    Route::post('/preference', [SettingsController::class, 'setPreference'])->name('preference');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/expenses/{expense}/toggle', [ExpenseController::class, 'toggle'])->name('expenses.toggle');
    Route::post('/expenses/{expense}/payments', [ExpensePaymentController::class, 'store'])->name('expenses.payments.store');
    Route::delete('/expenses/{expense}/payments/{payment}', [ExpensePaymentController::class, 'destroy'])->name('expenses.payments.destroy');

    Route::get('/monthly-report/{year}/{month}', [MonthlyReportController::class, 'show'])->name('monthly-report.show');
    Route::post('/monthly-report/{year}/{month}', [MonthlyReportController::class, 'generate'])->name('monthly-report.generate');

    Route::get('/savings-goal', [SavingsGoalController::class, 'index'])->name('savings-goal.index');
    Route::post('/savings-goal', [SavingsGoalController::class, 'store'])->name('savings-goal.store');
    Route::delete('/savings-goal', [SavingsGoalController::class, 'destroy'])->name('savings-goal.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});
