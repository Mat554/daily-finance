<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::where('username', session('username'))
            ->orderBy('is_active', 'desc')
            ->orderBy('category', 'desc')
            ->orderBy('name')
            ->get();

        $expensesWithSpending = $expenses->map(function ($expense) {
            $spent = $this->getSpentAmount($expense);
            $pct = $expense->allocated_amount > 0
                ? min(100, round($spent / $expense->allocated_amount * 100, 1))
                : 0;
            return [
                'expense' => $expense,
                'spent' => $spent,
                'remaining' => max(0, $expense->allocated_amount - $spent),
                'pct' => $pct,
                'is_over' => $spent > $expense->allocated_amount,
            ];
        });

        return view('expenses.index', compact('expensesWithSpending'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:fixed,variable',
            'allocated_amount' => 'required|numeric|min:0',
            'keywords' => 'nullable|string',
        ]);

        $keywords = null;
        if ($request->keywords) {
            $keywords = array_filter(
                array_map('trim', explode(',', $request->keywords)),
                fn($k) => $k !== ''
            );
        }

        Expense::create([
            'username' => session('username'),
            'name' => $request->name,
            'category' => $request->category,
            'allocated_amount' => $request->allocated_amount,
            'keywords' => $keywords,
            'is_active' => true,
            'rollover' => $request->boolean('rollover'),
        ]);

        return redirect()->route('expenses.index');
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:fixed,variable',
            'allocated_amount' => 'required|numeric|min:0',
            'keywords' => 'nullable|string',
        ]);

        $keywords = null;
        if ($request->keywords) {
            $keywords = array_filter(
                array_map('trim', explode(',', $request->keywords)),
                fn($k) => $k !== ''
            );
        }

        $expense->update([
            'name' => $request->name,
            'category' => $request->category,
            'allocated_amount' => $request->allocated_amount,
            'keywords' => $keywords,
            'rollover' => $request->boolean('rollover'),
        ]);

        return redirect()->route('expenses.index');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index');
    }

    public function toggle(Expense $expense)
    {
        $expense->update(['is_active' => !$expense->is_active]);
        return redirect()->route('expenses.index');
    }

    private function getSpentAmount(Expense $expense): float
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $transactions = Transaction::where('username', session('username'))
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $spent = 0;
        foreach ($transactions as $tx) {
            if ($expense->matchesTransaction($tx->description)) {
                $spent += $tx->amount;
            }
        }

        return $spent;
    }
}
