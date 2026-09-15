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

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $expensesWithSpending = $expenses->map(function ($expense) use ($monthStart, $monthEnd) {
            $spent = $this->getSpentAmount($expense);
            $pct = $expense->allocated_amount > 0
                ? min(100, round($spent / $expense->allocated_amount * 100, 1))
                : 0;

            $frequencyDivisor = match ($expense->payment_frequency ?? 'monthly') {
                'biweekly' => 2,
                'weekly' => 4,
                'onetime' => 1,
                default => 1,
            };
            $monthlyDueAmount = $expense->allocated_amount / max(1, $frequencyDivisor);

            $periodPayments = $expense->payments()
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $recentPayments = $expense->payments()
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->orderBy('payment_date', 'desc')
                ->limit(5)
                ->get();

            $paymentRemaining = max(0, $monthlyDueAmount - $periodPayments);
            $isPaymentComplete = $periodPayments >= $monthlyDueAmount;

            // Due date
            $daysUntilDue = null;
            $isPastDue = false;
            $isUpcoming = false;
            $nextDueDateFormatted = null;
            $nextDueMonth = null;
            $recommendedDailySave = null;

            if ($expense->due_date) {
                $today = Carbon::now();
                $dueDay = (int) $expense->due_date;
                $thisMonthDue = Carbon::now()->setDay(min($dueDay, Carbon::now()->daysInMonth));
                if ($thisMonthDue->lt($today) || $thisMonthDue->equalTo($today)) {
                    $thisMonthDue->addMonth();
                }
                $nextDueDateFormatted = $thisMonthDue->format('jS');
                $nextDueMonth = $thisMonthDue->format('F Y');
                $daysUntilDue = (int) $today->diffInDays($thisMonthDue, false);
                $isPastDue = $daysUntilDue < 0;
                $reminderThreshold = (int) ($expense->reminder_days ?? 3);
                $isUpcoming = !$isPastDue && $daysUntilDue <= $reminderThreshold;

                if ($daysUntilDue > 0 && $paymentRemaining > 0) {
                    $recommendedDailySave = round($paymentRemaining / max(1, $daysUntilDue));
                }
            }

            // Credit utilization
            $creditUtilization = null;
            if ($expense->is_credit && $expense->credit_limit > 0) {
                $creditUtilization = round((($expense->current_balance ?? 0) / $expense->credit_limit) * 100, 1);
            }

            return [
                'expense' => $expense,
                'spent' => $spent,
                'remaining' => max(0, $expense->allocated_amount - $spent),
                'pct' => $pct,
                'is_over' => $spent > $expense->allocated_amount,
                'monthly_due_amount' => $monthlyDueAmount,
                'total_paid' => $periodPayments,
                'payment_remaining' => $paymentRemaining,
                'is_payment_complete' => $isPaymentComplete,
                'recent_payments' => $recentPayments,
                'days_until_due' => $daysUntilDue,
                'is_past_due' => $isPastDue,
                'is_upcoming' => $isUpcoming,
                'next_due_date_formatted' => $nextDueDateFormatted,
                'next_due_month' => $nextDueMonth,
                'recommended_daily_save' => $recommendedDailySave,
                'credit_utilization' => $creditUtilization,
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
            'due_date' => 'nullable|integer|min:1|max:31',
            'is_credit' => 'nullable|boolean',
            'credit_limit' => 'nullable|numeric|min:0',
            'current_balance' => 'nullable|numeric|min:0',
            'minimum_payment' => 'nullable|numeric|min:0',
            'reminder_days' => 'nullable|integer|min:1|max:30',
            'payment_frequency' => 'nullable|in:monthly,biweekly,weekly,onetime',
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
            'due_date' => $request->due_date,
            'is_credit' => $request->boolean('is_credit'),
            'credit_limit' => $request->is_credit ? $request->credit_limit : null,
            'current_balance' => $request->is_credit ? $request->current_balance : null,
            'minimum_payment' => $request->is_credit ? $request->minimum_payment : null,
            'reminder_days' => $request->reminder_days ?? 3,
            'payment_frequency' => $request->payment_frequency ?? 'monthly',
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
            'due_date' => 'nullable|integer|min:1|max:31',
            'is_credit' => 'nullable|boolean',
            'credit_limit' => 'nullable|numeric|min:0',
            'current_balance' => 'nullable|numeric|min:0',
            'minimum_payment' => 'nullable|numeric|min:0',
            'reminder_days' => 'nullable|integer|min:1|max:30',
            'payment_frequency' => 'nullable|in:monthly,biweekly,weekly,onetime',
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
            'due_date' => $request->due_date,
            'is_credit' => $request->boolean('is_credit'),
            'credit_limit' => $request->is_credit ? $request->credit_limit : null,
            'current_balance' => $request->is_credit ? $request->current_balance : null,
            'minimum_payment' => $request->is_credit ? $request->minimum_payment : null,
            'reminder_days' => $request->reminder_days ?? 3,
            'payment_frequency' => $request->payment_frequency ?? 'monthly',
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
