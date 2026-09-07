<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function dashboard(Request $request)
    {
        // Dashboard is open to every logged-in account.
        // Per-user landing preference lives in session('landing').

        // --- Filter params ---
        $filter = $request->get('filter', 'month');
        $type = $request->get('type', 'all');
        $search = $request->get('q', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');

        // Date range from preset filter
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

        // Override with custom date range if provided
        if ($from) { $startDate = Carbon::parse($from); }
        if ($to) { $endDate = Carbon::parse($to); }

        // Build query
        $query = Transaction::where('username', session('username'));
        if ($startDate) { $query->where('transaction_date', '>=', $startDate->toDateString()); }
        if ($endDate) { $query->where('transaction_date', '<=', $endDate->toDateString()); }
        if ($type !== 'all') { $query->where('type', $type); }
        if ($search) { $query->where('description', 'like', '%' . $search . '%'); }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Stats for the filtered set
        $totalIn = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');
        $netBalance = $totalIn - $totalOut;
        $transactionCount = $transactions->count();
        $activeDays = $transactions->unique('transaction_date')->count();
        $dailyAvg = $activeDays > 0 ? $netBalance / $activeDays : 0;

        // All-time stats
        $all = Transaction::where('username', session('username'))->get();
        $allTimeIn = $all->where('type', 'in')->sum('amount');
        $allTimeOut = $all->where('type', 'out')->sum('amount');
        $allTimeNet = $allTimeIn - $allTimeOut;

        // This month
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $thisMonth = $all->filter(function ($t) use ($monthStart, $monthEnd) {
            return Carbon::parse($t->transaction_date)->between($monthStart, $monthEnd);
        });
        $monthIn = $thisMonth->where('type', 'in')->sum('amount');
        $monthOut = $thisMonth->where('type', 'out')->sum('amount');
        $monthNet = $monthIn - $monthOut;

        // Monthly trend — last 6 months (all-time)
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

        // ── Save vs Spend (income splits) ─────────────────────────────
        $splitTx = $transactions->where('is_split', true);
        $totalSaved      = (float) $splitTx->sum('saved_amount');
        $totalSpent      = (float) $splitTx->sum('spent_amount');
        $splitCount      = $splitTx->count();
        $totalSplitIncome = (float) $splitTx->sum('amount');
        $savingsRate = $totalSplitIncome > 0 ? ($totalSaved / $totalSplitIncome) * 100 : 0;

        // Save vs Spend donut: rate among rows the user actually split.
        // Clamp to [0, 100] so the donut math in the view never divides oddly.
        $splitSavingsRate = max(0.0, min(100.0, (float) $savingsRate));

        // ── Need vs Want (expense categories) ────────────────────────
        $needTx = $transactions->where('type', 'out')->where('need_or_want', 'need');
        $wantTx = $transactions->where('type', 'out')->where('need_or_want', 'want');
        $needAmount = (float) $needTx->sum('amount');
        $wantAmount = (float) $wantTx->sum('amount');
        $needCount  = $needTx->count();
        $wantCount  = $wantTx->count();
        $totalCategorized = $needAmount + $wantAmount;
        $needPct = $totalCategorized > 0 ? ($needAmount / $totalCategorized) * 100 : 50;
        $wantPct = $totalCategorized > 0 ? ($wantAmount / $totalCategorized) * 100 : 50;

        // ── Savings Distribution (categorized) ───────────────────────
        $distributions = $transactions->where('is_split', true)->whereNotNull('savings_distribution');
        $categorizedSavings = [];
        $totalAllocated = 0.0;

        $SAVINGS_CATEGORIES = ['Emergency Fund', 'Investment', 'Goals', 'Buffer'];
        foreach ($SAVINGS_CATEGORIES as $cat) {
            $categorizedSavings[$cat] = 0.0;
        }

        foreach ($distributions as $tx) {
            $dist = is_string($tx->savings_distribution) ? json_decode($tx->savings_distribution, true) : ($tx->savings_distribution ?? []);
            foreach ($dist as $item) {
                $cat = $item['category'] ?? '';
                $amt = (float) ($item['amount'] ?? 0);
                if (isset($categorizedSavings[$cat])) {
                    $categorizedSavings[$cat] += $amt;
                    $totalAllocated += $amt;
                }
            }
        }

        // Build chart data — sorted by amount desc
        $distributionChart = collect($categorizedSavings)
            ->map(fn($amt, $cat) => ['category' => $cat, 'amount' => $amt, 'pct' => $totalAllocated > 0 ? round($amt / $totalAllocated * 100, 1) : 0])
            ->sortByDesc('amount')
            ->values()
            ->all();

        $hasDistributions = $distributions->isNotEmpty();

        // ── CTA nudges ───────────────────────────────────────────────
        $unsplitIn = (float) $transactions->where('type', 'in')->where('is_split', false)->sum('amount');
        $uncategorizedOut = (float) $transactions->where('type', 'out')->whereNull('need_or_want')->sum('amount');

        // ── Savings streak ───────────────────────────────────────────
        $streak = $this->calculateSavingsStreak($all);

        // ── Badges ──────────────────────────────────────────────────
        $badges = $this->computeBadges($all, $streak, $totalSaved);

        // ── Motivational messages ───────────────────────────────────
        $saveVsSpendMessage = $this->getSavingsMessage($savingsRate, $splitCount);
        $needVsWantMessage = $this->getNeedWantMessage($needPct, $needCount + $wantCount);

        // Group by description
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

        // Pre-group for initial display
        $groupedTx = $transactions->groupBy(function ($t) {
            return Carbon::parse($t->transaction_date)->format('Y-m-d');
        });

        // Pass all transactions as JSON for client-side table
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

        return view('dashboard', compact(
            'filter', 'type', 'search', 'from', 'to',
            'totalIn', 'totalOut', 'netBalance', 'transactionCount',
            'activeDays', 'dailyAvg',
            'allTimeIn', 'allTimeOut', 'allTimeNet',
            'monthIn', 'monthOut', 'monthNet',
            'monthlyTrend',
            'topExpenses', 'topIncome',
            'txJson', 'groupedTx',
            // New split/category vars
            'totalSaved', 'totalSpent', 'splitCount', 'savingsRate',
            'needAmount', 'wantAmount', 'needCount', 'wantCount', 'needPct', 'wantPct',
            'unsplitIn', 'uncategorizedOut',
            'streak', 'badges', 'splitSavingsRate', 'saveVsSpendMessage', 'needVsWantMessage',
            'categorizedSavings', 'distributionChart', 'totalAllocated', 'hasDistributions',
        ));
    }

    private function calculateSavingsStreak($transactions)
    {
        $splitDates = $transactions
            ->where('is_split', true)
            ->pluck('transaction_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->sortByDesc('transaction_date')
            ->values();

        if ($splitDates->isEmpty()) return 0;

        $streak = 0;
        $today = Carbon::today();

        // Walk backwards from today
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

    private function computeBadges($transactions, $streak, $totalSaved)
    {
        $badges = [];
        $now = Carbon::now();

        // First Split — logged first income with a split
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

        // Streak badges
        if ($streak >= 3) {
            $badges[] = [
                'id' => 'streak_3',
                'name' => "{$streak}-Day Streak",
                'icon' => '🔥',
                'earned' => $now->format('M j'),
                'bg' => 'from-amber-100 to-amber-50 border-amber-200',
            ];
        }

        // Saver King — saved 70%+ for 3+ consecutive income entries
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

        // Millionaire — total saved crosses 1,000,000
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

    private function getSavingsMessage($rate, $count)
    {
        if ($count === 0) return 'Ready to take control? Split your next income! 💡';
        if ($rate >= 80) return "Maximum saver mode! You're a legend! 🚀";
        if ($rate >= 70) return "Whoa, " . session('username', 'friend') . " is on fire! 🔥";
        if ($rate >= 60) return 'Solid saving discipline! Keep it up! 💪';
        if ($rate >= 50) return 'A great split starts your month right! 🎯';
        if ($rate >= 30) return 'Every split counts — great job! 🎯';
        return 'Treat yourself, but wisely! ✨';
    }

    private function getNeedWantMessage($needPct, $totalCategorized)
    {
        if ($totalCategorized === 0) return 'Categorize your expenses to see insights! 📊';
        if ($needPct >= 80) return 'Mostly essentials — solid foundation! 🏠';
        if ($needPct >= 70) return 'You prioritize the essentials. Smart move! 🏠';
        if ($needPct >= 50) return 'Nice balance between needs and wants! ⚖️';
        return 'Treat yo\' self, but maybe pump the brakes? 😬';
    }

    public function index(Request $request)
    {
        $currentDate = $request->query('date', Carbon::today()->toDateString());
        $transactions = Transaction::whereDate('transaction_date', $currentDate)
            ->where('username', session('username'))
            ->latest()
            ->get();

        $totalIn = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');
        $balance = $totalIn - $totalOut;

        return view('tracker', compact('transactions', 'totalIn', 'totalOut', 'balance', 'currentDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required|in:in,out',
            'transaction_date' => 'required|date',
        ]);

        $data = [
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'transaction_date' => $request->transaction_date,
            'username' => session('username'),
        ];

        // Handle income split
        if ($request->type === 'in' && $request->has('is_split') && $request->is_split == '1') {
            $savePct = (float) $request->save_pct;
            $spendPct = (float) $request->spend_pct;

            if (abs(($savePct + $spendPct) - 100) > 0.01) {
                $spendPct = 100 - $savePct;
            }

            $data = array_merge($data, [
                'save_pct' => $savePct,
                'spend_pct' => $spendPct,
                'saved_amount' => $request->amount * $savePct / 100,
                'spent_amount' => $request->amount * $spendPct / 100,
                'is_split' => true,
                'split_preset' => $request->split_preset ?? 'custom',
                'savings_distribution' => $request->has('savings_distribution')
                    ? json_encode($request->savings_distribution)
                    : null,
                'savings_allocated' => $request->has('savings_distribution') && !empty($request->savings_distribution),
            ]);
        }

        // Handle expense category
        if ($request->type === 'out') {
            if ($request->has('need_or_want')) {
                $data['need_or_want'] = $request->need_or_want;
            }
            if ($request->has('expense_category')) {
                $data['expense_category'] = $request->expense_category;
            }
        }

        Transaction::create($data);

        return $this->redirectAfterAction($request);
    }

    public function edit(Transaction $transaction)
    {
        return view('edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'description' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required|in:in,out',
            'transaction_date' => 'required|date',
        ]);

        $data = $request->only(['description', 'amount', 'type', 'transaction_date']);

        // Handle income split update
        if ($request->type === 'in' && $request->has('is_split') && $request->is_split == '1') {
            $savePct = (float) $request->save_pct;
            $spendPct = 100 - $savePct;
            $data = array_merge($data, [
                'save_pct' => $savePct,
                'spend_pct' => $spendPct,
                'saved_amount' => $request->amount * $savePct / 100,
                'spent_amount' => $request->amount * $spendPct / 100,
                'is_split' => true,
                'split_preset' => $request->split_preset ?? 'custom',
                'savings_distribution' => $request->has('savings_distribution')
                    ? json_encode($request->savings_distribution)
                    : null,
                'savings_allocated' => $request->has('savings_distribution') && !empty($request->savings_distribution),
            ]);
        } elseif ($request->type === 'in') {
            $data = array_merge($data, [
                'save_pct' => null, 'spend_pct' => null,
                'saved_amount' => null, 'spent_amount' => null,
                'is_split' => false, 'split_preset' => null,
                'savings_distribution' => null, 'savings_allocated' => false,
            ]);
        }

        // Handle expense category update
        if ($request->type === 'out') {
            if ($request->has('need_or_want')) { $data['need_or_want'] = $request->need_or_want; }
            if ($request->has('expense_category')) { $data['expense_category'] = $request->expense_category; }
        }

        $transaction->update($data);

        return $this->redirectAfterAction($request);
    }

    public function history()
    {
        $groupedTransactions = Transaction::where('username', session('username'))
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->groupBy('transaction_date');

        return view('history', compact('groupedTransactions'));
    }

    public function destroy(Transaction $transaction)
    {
        $date = $transaction->transaction_date;
        $transaction->delete();

        return $this->redirectAfterAction(request()->merge(['transaction_date' => $date]));
    }

    public function setPreference(Request $request)
    {
        $request->validate([
            'landing' => 'required|in:tracker,dashboard',
        ]);

        session(['landing' => $request->landing]);

        return back();
    }

    /**
     * Centralised post-action redirect.
     *
     * Honors the user's session('landing') preference:
     *   - 'dashboard' → /dashboard
     *   - 'tracker'   → /?date=<context date> (defaults to today)
     *
     * Used by store/update/destroy so the same rule applies to every mutation.
     */
    private function redirectAfterAction(Request $request): \Illuminate\Http\RedirectResponse
    {
        $landing = session('landing', 'tracker');

        if ($landing === 'dashboard') {
            return redirect('/dashboard');
        }

        $date = $request->input('transaction_date')
            ?? $request->input('date')
            ?? now()->toDateString();

        return redirect('/?date=' . $date);
    }
}
