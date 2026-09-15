<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\AccountBalance;
use App\Models\Expense;
use App\Models\ExpensePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getAnalytics(Request $request, string $username): array
    {
        $filter = $request->get('filter', 'month');
        $type = $request->get('type', 'all');
        $search = $request->get('q', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        $startDate = null;
        $endDate = null;
        switch ($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'all':
            default:
                $startDate = null;
                $endDate = null;
                break;
        }

        if ($from) { $startDate = Carbon::parse($from); }
        if ($to) { $endDate = Carbon::parse($to); }

        $query = Transaction::where('username', $username);
        if ($startDate) { $query->where('transaction_date', '>=', $startDate->toDateString()); }
        if ($endDate) { $query->where('transaction_date', '<=', $endDate->toDateString()); }
        if ($type !== 'all') { $query->where('type', $type); }
        if ($search) { $query->where('description', 'like', '%' . $search . '%'); }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $totalIn = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');
        $netBalance = $totalIn - $totalOut;
        $transactionCount = $transactions->count();
        $activeDays = $transactions->unique('transaction_date')->count();
        $dailyAvg = $activeDays > 0 ? $netBalance / $activeDays : 0;

        $all = Transaction::where('username', $username)->get();
        $allTimeIn = $all->where('type', 'in')->sum('amount');
        $allTimeOut = $all->where('type', 'out')->sum('amount');
        $allTimeNet = $allTimeIn - $allTimeOut;

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $thisMonth = $all->filter(function ($t) use ($monthStart, $monthEnd) {
            return Carbon::parse($t->transaction_date)->between($monthStart, $monthEnd);
        });
        $monthIn = $thisMonth->where('type', 'in')->sum('amount');
        $monthOut = $thisMonth->where('type', 'out')->sum('amount');
        $monthNet = $monthIn - $monthOut;

        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $monthData = $all->filter(function ($t) use ($start, $end) {
                return Carbon::parse($t->transaction_date)->between($start, $end);
            });
            $monthlyTrend[] = [
                'label' => $month->format('M Y'),
                'in' => $monthData->where('type', 'in')->sum('amount'),
                'out' => $monthData->where('type', 'out')->sum('amount'),
                'net' => $monthData->where('type', 'in')->sum('amount') - $monthData->where('type', 'out')->sum('amount'),
            ];
        }

        $splitTx = $transactions->where('is_split', true);
        $totalSaved = (float) $splitTx->sum('saved_amount');
        $totalSpent = (float) $splitTx->sum('spent_amount');
        $splitCount = $splitTx->count();
        $totalSplitIncome = (float) $splitTx->sum('amount');
        $savingsRate = $totalSplitIncome > 0 ? ($totalSaved / $totalSplitIncome) * 100 : 0;
        $splitSavingsRate = max(0.0, min(100.0, (float) $savingsRate));

        $needTx = $transactions->where('type', 'out')->where('need_or_want', 'need');
        $wantTx = $transactions->where('type', 'out')->where('need_or_want', 'want');
        $needAmount = (float) $needTx->sum('amount');
        $wantAmount = (float) $wantTx->sum('amount');
        $needCount = $needTx->count();
        $wantCount = $wantTx->count();
        $totalCategorized = $needAmount + $wantAmount;
        $needPct = $totalCategorized > 0 ? ($needAmount / $totalCategorized) * 100 : 50;
        $wantPct = $totalCategorized > 0 ? ($wantAmount / $totalCategorized) * 100 : 50;

        $distributionTemplate = session('distribution_template', []);
        $distributionSavePct = session('distribution_save_pct', 50);

        // Normalize template keys: always use 'category', accept legacy 'name'
        $distributionTemplate = array_values(array_filter(array_map(function ($t) {
            $cat = trim($t['category'] ?? $t['name'] ?? '');
            if ($cat === '') return null;
            return [
                'category' => $cat,
                'pct' => (float) ($t['pct'] ?? 0),
                'icon' => $t['icon'] ?? '📦',
                'color' => $t['color'] ?? '',
            ];
        }, $distributionTemplate)));

        $distributions = $transactions->where('is_split', true)->whereNotNull('savings_distribution');
        $categorizedSavings = [];
        $totalAllocated = 0.0;

        $distCategories = [];
        foreach ($distributionTemplate as $t) {
            $cat = trim($t['category'] ?? $t['name'] ?? '');
            if ($cat !== '') {
                $distCategories[$cat] = 0.0;
            }
        }
        if (empty($distCategories)) {
            foreach (['Emergency Fund', 'Investment', 'Goals', 'Buffer'] as $cat) {
                $distCategories[$cat] = 0.0;
            }
        }

        $categorizedSavings = $distCategories;
        foreach ($distributions as $tx) {
            $raw = $tx->savings_distribution;
            if (!is_string($raw) || $raw === '') {
                $dist = [];
            } else {
                $decoded = json_decode($raw, true);
                $dist = is_array($decoded) ? $decoded : [];
            }
            foreach ($dist as $item) {
                $cat = $item['category'] ?? '';
                $amt = (float) ($item['amount'] ?? 0);
                if (isset($categorizedSavings[$cat])) {
                    $categorizedSavings[$cat] += $amt;
                    $totalAllocated += $amt;
                }
            }
        }

        $distributionChart = collect($categorizedSavings)
            ->map(fn($amt, $cat) => ['category' => $cat, 'amount' => $amt, 'pct' => $totalAllocated > 0 ? round($amt / $totalAllocated * 100, 1) : 0])
            ->sortByDesc('amount')
            ->values()
            ->all();

        $hasDistributions = $distributions->isNotEmpty();

        $unsplitIn = (float) $transactions->where('type', 'in')->where('is_split', false)->sum('amount');
        $unsplitInTx = $transactions->where('type', 'in')->where('is_split', false)->sortByDesc('transaction_date')->take(20)->values();
        $uncategorizedOut = (float) $transactions->where('type', 'out')->whereNull('need_or_want')->sum('amount');

        $streak = $this->calculateSavingsStreak($all);
        $badges = $this->computeBadges($all, $streak, $totalSaved);
        $saveVsSpendMessage = $this->getSavingsMessage($savingsRate, $splitCount, $username);
        $needVsWantMessage = $this->getNeedWantMessage($needPct, $needCount + $wantCount);

        $byDescription = $transactions
            ->groupBy('description')
            ->map(function ($group) {
                return [
                    'description' => $group->first()->description,
                    'type' => $group->first()->type,
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $topExpenses = $byDescription->where('type', 'out')->take(5);
        $topIncome = $byDescription->where('type', 'in')->take(5);

        $groupedTx = $transactions->groupBy(fn($t) => Carbon::parse($t->transaction_date)->format('Y-m-d'));

        $txJson = $transactions->map(function ($t) {
            return [
                'id' => $t->id,
                'description' => $t->description,
                'amount' => (float) $t->amount,
                'type' => $t->type,
                'date' => $t->transaction_date,
                'dateDisplay' => Carbon::parse($t->transaction_date)->format('M j, Y'),
                'dateSort' => Carbon::parse($t->transaction_date)->format('Y-m-d'),
                'amountDisplay' => ($t->type === 'in' ? '+' : '-') . 'Rp ' . number_format($t->amount, 0),
                'amountRaw' => (float) $t->amount,
                'typeClass' => $t->type === 'in' ? 'in' : 'out',
                'is_split' => $t->is_split,
                'save_pct' => $t->save_pct,
                'spend_pct' => $t->spend_pct,
                'saved_amount' => (float) $t->saved_amount,
                'spent_amount' => (float) $t->spent_amount,
                'need_or_want' => $t->need_or_want,
                'expense_category' => $t->expense_category,
            ];
        });

        $accountBalances = [];
        foreach (AccountBalance::TYPES as $type) {
            $inTotal = (float) $all->where('account_type', $type)->where('type', 'in')->sum('amount');
            $outTotal = (float) $all->where('account_type', $type)->where('type', 'out')->sum('amount');
            $accountBalances[$type] = $inTotal - $outTotal;
        }
        $totalAccountBalance = array_sum($accountBalances);

        return [
            'filter' => $filter,
            'type' => $type,
            'search' => $search,
            'from' => $from,
            'to' => $to,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'netBalance' => $netBalance,
            'transactionCount' => $transactionCount,
            'activeDays' => $activeDays,
            'dailyAvg' => $dailyAvg,
            'allTimeIn' => $allTimeIn,
            'allTimeOut' => $allTimeOut,
            'allTimeNet' => $allTimeNet,
            'monthIn' => $monthIn,
            'monthOut' => $monthOut,
            'monthNet' => $monthNet,
            'monthlyTrend' => $monthlyTrend,
            'topExpenses' => $topExpenses,
            'topIncome' => $topIncome,
            'txJson' => $txJson,
            'groupedTx' => $groupedTx,
            'totalSaved' => $totalSaved,
            'totalSpent' => $totalSpent,
            'splitCount' => $splitCount,
            'savingsRate' => $savingsRate,
            'splitSavingsRate' => $splitSavingsRate,
            'needAmount' => $needAmount,
            'wantAmount' => $wantAmount,
            'needCount' => $needCount,
            'wantCount' => $wantCount,
            'needPct' => $needPct,
            'wantPct' => $wantPct,
            'unsplitIn' => $unsplitIn,
            'unsplitInTx' => $unsplitInTx,
            'uncategorizedOut' => $uncategorizedOut,
            'streak' => $streak,
            'badges' => $badges,
            'saveVsSpendMessage' => $saveVsSpendMessage,
            'needVsWantMessage' => $needVsWantMessage,
            'categorizedSavings' => $categorizedSavings,
            'distributionChart' => $distributionChart,
            'totalAllocated' => $totalAllocated,
            'hasDistributions' => $hasDistributions,
            'accountBalances' => $accountBalances,
            'totalAccountBalance' => $totalAccountBalance,
            'distributionTemplate' => $distributionTemplate,
            'distributionSavePct' => $distributionSavePct,

            // Health score (filter-aware savings rate)
            'healthScore' => $totalIn > 0 ? max(0, min(100, (int) round(($totalSaved / $totalIn) * 100))) : 50,

            // Expense budget data
            'expensesWithSpending' => $this->getExpenseBudgetData($username),
            'savingsGoal' => (float) session('savings_goal', 0),
            'savingsProgress' => $this->getSavingsProgress($username),
        ];
    }

    public function calculateSavingsStreak(Collection $transactions): int
    {
        $splitDates = $transactions
            ->where('is_split', true)
            ->pluck('transaction_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values();

        if ($splitDates->isEmpty()) return 0;

        $streak = 0;
        $today = Carbon::today();
        $checkDate = $today;

        foreach ($splitDates as $date) {
            if ($date === $checkDate->format('Y-m-d')) {
                $streak++;
                $checkDate = $checkDate->subDay();
            } elseif (Carbon::parse($date)->lt($checkDate)) {
                break;
            }
        }

        return $streak;
    }

    public function computeBadges(Collection $transactions, int $streak, float $totalSaved): array
    {
        $badges = [];
        $now = Carbon::now();

        $hasSplit = $transactions->where('is_split', true)->isNotEmpty();
        if ($hasSplit) {
            $firstSplit = $transactions->where('is_split', true)->sortBy('created_at')->first();
            $badges[] = [
                'id' => 'first_split',
                'name' => 'First Split',
                'icon' => '💎',
                'earned' => $firstSplit ? Carbon::parse($firstSplit->created_at)->format('M j') : '',
                'bg' => 'from-purple-100 to-purple-50 border-purple-200',
            ];
        }

        if ($streak >= 3) {
            $badges[] = [
                'id' => 'streak_3',
                'name' => "{$streak}-Day Streak",
                'icon' => '🔥',
                'earned' => $now->format('M j'),
                'bg' => 'from-amber-100 to-amber-50 border-amber-200',
            ];
        }

        $splitTx = $transactions->where('is_split', true)->where('save_pct', '>=', 70);
        if ($splitTx->count() >= 3) {
            $badges[] = [
                'id' => 'saver_king',
                'name' => 'Saver King',
                'icon' => '👑',
                'earned' => $now->format('M j'),
                'bg' => 'from-yellow-100 to-yellow-50 border-yellow-200',
            ];
        }

        if ($totalSaved >= 1000000) {
            $badges[] = [
                'id' => 'millionaire',
                'name' => 'Millionaire',
                'icon' => '💰',
                'earned' => $now->format('M j'),
                'bg' => 'from-green-100 to-green-50 border-green-200',
            ];
        }

        return $badges;
    }

    public function getSavingsMessage(float $rate, int $count, string $username = 'friend'): string
    {
        if ($count === 0) return 'Ready to take control? Split your next income! 💡';
        if ($rate >= 80) return "Maximum saver mode! You're a legend! 🚀";
        if ($rate >= 70) return "Whoa, {$username} is on fire! 🔥";
        if ($rate >= 60) return 'Solid saving discipline! Keep it up! 💪';
        if ($rate >= 50) return 'A great split starts your month right! 🎯';
        if ($rate >= 30) return 'Every split counts — great job! 🎯';
        return 'Treat yourself, but wisely! ✨';
    }

    public function getNeedWantMessage(float $needPct, int $totalCategorized): string
    {
        if ($totalCategorized === 0) return 'Categorize your expenses to see insights! 📊';
        if ($needPct >= 80) return 'Mostly essentials — solid foundation! 🏠';
        if ($needPct >= 70) return 'You prioritize the essentials. Smart move! 🏠';
        if ($needPct >= 50) return 'Nice balance between needs and wants! ⚖️';
        return 'Treat yo\' self, but maybe pump the brakes? 😬';
    }

    public function getExpenseBudgetData(string $username): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $expenses = Expense::where('username', $username)->where('is_active', true)->get();
        $txs = Transaction::where('username', $username)
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('type', 'out')
            ->get();

        $result = [];
        foreach ($expenses as $expense) {
            $matched = $txs->filter(fn($t) => $expense->matchesTransaction($t->description));
            $spent = (float) $matched->sum('amount');
            $allocated = (float) $expense->allocated_amount;
            $remaining = $allocated - $spent;
            $pct = $allocated > 0 ? round(($spent / $allocated) * 100) : 0;

            // Due date calculations
            $daysUntilDue = null;
            $isPastDue = false;
            $isUpcoming = false;
            $recommendedDailySave = null;
            $amountToPursue = $allocated - $spent;
            $nextDueDateFormatted = null;
            $nextDueMonth = null;

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

                if ($daysUntilDue > 0 && $amountToPursue > 0) {
                    $recommendedDailySave = round($amountToPursue / max(1, $daysUntilDue));
                }
            }

            // Credit-specific calculations
            $creditUtilization = null;
            $minPaymentReminder = null;
            if ($expense->is_credit) {
                $creditLimit = $expense->credit_limit ?? 0;
                $currentBalance = $expense->current_balance ?? 0;
                $amountToPursue = $currentBalance;
                if ($creditLimit > 0) {
                    $creditUtilization = round(($currentBalance / $creditLimit) * 100, 1);
                }
                if ($expense->minimum_payment && $daysUntilDue !== null && $daysUntilDue <= ($expense->reminder_days ?? 3)) {
                    $minPaymentReminder = $expense->minimum_payment;
                }
            }

            // Payment calculations
            $frequencyDivisor = match ($expense->payment_frequency ?? 'monthly') {
                'biweekly' => 2,
                'weekly' => 4,
                'onetime' => 1,
                default => 1,
            };
            $monthlyDueAmount = $allocated / max(1, $frequencyDivisor);

            // Get payments for this billing period (current calendar month)
            $periodPayments = $expense->payments()
                ->where('username', $username)
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $paymentRemaining = max(0, $monthlyDueAmount - $periodPayments);
            $isPaymentComplete = $periodPayments >= $monthlyDueAmount;

            $recentPayments = $expense->payments()
                ->where('username', $username)
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->orderBy('payment_date', 'desc')
                ->limit(5)
                ->get();

            $result[] = [
                'expense' => $expense,
                'spent' => $spent,
                'remaining' => $remaining,
                'pct' => $pct,
                'is_over' => $spent > $allocated,
                'days_until_due' => $daysUntilDue,
                'is_past_due' => $isPastDue,
                'is_upcoming' => $isUpcoming,
                'recommended_daily_save' => $recommendedDailySave,
                'amount_to_pursue' => $amountToPursue,
                'credit_utilization' => $creditUtilization,
                'min_payment_reminder' => $minPaymentReminder,
                'next_due_date_formatted' => $nextDueDateFormatted,
                'next_due_month' => $nextDueMonth,
                'monthly_due_amount' => $monthlyDueAmount,
                'total_paid' => $periodPayments,
                'payment_remaining' => $paymentRemaining,
                'is_payment_complete' => $isPaymentComplete,
                'recent_payments' => $recentPayments,
            ];
        }

        return $result;
    }

    public function getSavingsProgress(string $username): array
    {
        $goal = (float) session('savings_goal', 0);
        if ($goal <= 0) return [];

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $saved = (float) Transaction::where('username', $username)
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('type', 'in')
            ->where('is_split', true)
            ->sum('saved_amount');

        $daysInMonth = Carbon::now()->daysInMonth;
        $daysElapsed = Carbon::now()->day;
        $expectedByNow = round($goal * $daysElapsed / $daysInMonth);
        $onTrack = $saved >= $expectedByNow;
        $projected = round($saved / max(1, $daysElapsed) * $daysInMonth);
        $progress = min(100, round($saved / $goal * 100));

        return [
            'saved' => $saved,
            'goal' => $goal,
            'progress' => $progress,
            'expected_by_now' => $expectedByNow,
            'on_track' => $onTrack,
            'projected' => $projected,
            'remaining' => max(0, $goal - $saved),
        ];
    }
}
