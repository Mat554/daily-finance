<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
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

        // Monthly spending per category
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $monthlyData = [];
        foreach ($categories as $cat) {
            $in = $cat->transactions()
                ->where('username', $username)
                ->where('type', 'in')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');

            $out = $cat->transactions()
                ->where('username', $username)
                ->where('type', 'out')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');

            $monthlyData[$cat->id] = [
                'income' => $in,
                'spending' => $out,
                'net' => $in - $out,
            ];
        }

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = now()->month($m)->format('F');
        }

        return view('categories.index', compact(
            'categories', 'monthlyData', 'month', 'year', 'months'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        Category::create([
            'username' => session('username'),
            'name' => trim($request->name),
            'icon' => $request->icon ?: '📦',
            'color' => $request->color ?: '#6b7280',
            'is_active' => true,
        ]);

        return back()->with('success', 'Category created!');
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($category->username === session('username'), 403);

        $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ]);

        $category->update([
            'name' => trim($request->name),
            'icon' => $request->icon ?: '📦',
            'color' => $request->color ?: '#6b7280',
        ]);

        return back()->with('success', 'Category updated!');
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($category->username === session('username'), 403);

        // Soft delete — just mark inactive
        $category->update(['is_active' => false]);

        return back()->with('success', 'Category deleted!');
    }
}
