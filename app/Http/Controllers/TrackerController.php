<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrackerController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->query('date', Carbon::today()->toDateString());
        $username = session('username');

        // Seed defaults if user has no categories
        $existingCount = Category::where('username', $username)->count();
        if ($existingCount === 0) {
            $defaults = [
                ['name' => 'Food', 'icon' => '🍔', 'color' => '#f97316'],
                ['name' => 'Transportation', 'icon' => '🚗', 'color' => '#3b82f6'],
                ['name' => 'Entertainment', 'icon' => '🎬', 'color' => '#a855f7'],
                ['name' => 'Shopping', 'icon' => '🛍', 'color' => '#ec4899'],
                ['name' => 'Bills', 'icon' => '📄', 'color' => '#eab308'],
                ['name' => 'Health', 'icon' => '💊', 'color' => '#22c55e'],
                ['name' => 'Education', 'icon' => '📚', 'color' => '#06b6d4'],
                ['name' => 'Other', 'icon' => '📦', 'color' => '#6b7280'],
            ];
            foreach ($defaults as $cat) {
                Category::create([
                    'username' => $username,
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_active' => true,
                ]);
            }
        }

        $categories = Category::where('username', $username)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $lastUsedCategoryId = session('last_used_category_id');

        $transactions = Transaction::whereDate('transaction_date', $currentDate)
            ->where('username', $username)
            ->latest()
            ->get();

        $totalIn = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');
        $balance = $totalIn - $totalOut;

        return view('tracker', compact('transactions', 'totalIn', 'totalOut', 'balance', 'currentDate', 'categories', 'lastUsedCategoryId'));
    }

    public function history()
    {
        $username = session('username');

        $categories = Category::where('username', $username)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $groupedTransactions = Transaction::where('username', $username)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->groupBy('transaction_date');

        return view('history', compact('groupedTransactions', 'categories'));
    }
}
