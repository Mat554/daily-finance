<?php

namespace App\Http\Controllers;

use App\Models\MonthlyReport;
use App\Models\Transaction;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyReportController extends Controller
{
    public function show(Request $request, int $year, int $month)
    {
        $username = session('username');

        $report = MonthlyReport::where('username', $username)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $shouldGenerate = !$report || $request->boolean('refresh');
        if ($shouldGenerate) {
            $report = $this->generateReport($username, $year, $month);
        }

        $prevReport = MonthlyReport::where('username', $username)
            ->where(function ($q) use ($year, $month) {
                if ($month === 1) {
                    $q->where('year', $year - 1)->where('month', 12);
                } else {
                    $q->where('year', $year)->where('month', $month - 1);
                }
            })
            ->first();

        $monthName = Carbon::create($year, $month, 1)->format('F Y');
        $summary = $report->getSummary();

        return view('monthly-report', compact(
            'report', 'prevReport', 'monthName', 'year', 'month', 'summary'
        ));
    }

    public function generate(Request $request, int $year, int $month)
    {
        $username = session('username');
        $report = $this->generateReport($username, $year, $month);

        $monthName = Carbon::create($year, $month, 1)->format('F Y');
        return redirect()->route('monthly-report.show', [$year, $month]);
    }

    private function generateReport(string $username, int $year, int $month): MonthlyReport
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $transactions = Transaction::where('username', $username)
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $totalIncome = (float) $transactions->where('type', 'in')->sum('amount');
        $totalMoneyOut = (float) $transactions->where('type', 'out')->sum('amount');
        $totalSaved = (float) $transactions->where('type', 'in')->where('is_split', true)->sum('saved_amount');

        $expenses = Expense::where('username', $username)->where('is_active', true)->get();
        $totalAllocated = (float) $expenses->sum('allocated_amount');

        $totalExpenseUsed = 0;
        foreach ($expenses as $expense) {
            $expenseTransactions = $transactions->where('type', 'out')->filter(fn($t) => $expense->matchesTransaction($t->description));
            $totalExpenseUsed += $expenseTransactions->sum('amount');
        }

        $netBalance = $totalIncome - $totalMoneyOut - $totalExpenseUsed;

        $condition = match (true) {
            $netBalance > $totalAllocated * 0.5 => 'surplus',
            $netBalance > 0 => 'break_even',
            $netBalance === 0 => 'break_even',
            default => 'deficit',
        };

        $score = $this->computeScore($transactions, $totalIncome, $totalMoneyOut, $totalSaved, $netBalance, $totalAllocated);
        $summary = $this->computeSummary($transactions, $totalIncome, $totalMoneyOut, $totalSaved, $netBalance, $totalAllocated, $expenses);

        $report = MonthlyReport::updateOrCreate(
            ['username' => $username, 'year' => $year, 'month' => $month],
            [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenseUsed,
                'total_money_out' => $totalMoneyOut,
                'total_saved' => $totalSaved,
                'net_balance' => $netBalance,
                'condition' => $condition,
                'score' => $score,
                'summary_json' => $summary,
            ]
        );

        return $report;
    }

    private function computeScore($transactions, float $totalIncome, float $totalMoneyOut, float $totalSaved, float $netBalance, float $totalAllocated): int
    {
        $score = 0;

        // Savings rate vs target (30%) — assume 20% of income is target
        if ($totalIncome > 0) {
            $savingsRate = ($totalSaved / $totalIncome) * 100;
            $targetRate = 20;
            $rateScore = match (true) {
                $savingsRate >= $targetRate * 1.5 => 30,
                $savingsRate >= $targetRate => 22,
                $savingsRate >= $targetRate * 0.5 => 12,
                default => 0,
            };
        } else {
            $rateScore = 0;
        }
        $score += $rateScore;

        // Need vs Want ratio (20%)
        $needTx = $transactions->where('type', 'out')->where('need_or_want', 'need');
        $wantTx = $transactions->where('type', 'out')->where('need_or_want', 'want');
        $totalCategorized = $needTx->sum('amount') + $wantTx->sum('amount');
        if ($totalCategorized > 0) {
            $needPct = ($needTx->sum('amount') / $totalCategorized) * 100;
            $needScore = match (true) {
                $needPct >= 70 => 20,
                $needPct >= 50 => 13,
                $needPct >= 30 => 7,
                default => 0,
            };
        } else {
            $needScore = 5; // neutral if uncategorized
        }
        $score += $needScore;

        // Expense coverage (20%) — did spending stay within allocated budget?
        $expenses = Expense::where('username', session('username'))->where('is_active', true)->get();
        $totalAllocated = (float) $expenses->sum('allocated_amount');
        if ($totalAllocated > 0) {
            $coverageScore = match (true) {
                $totalMoneyOut <= $totalAllocated * 0.7 => 20,
                $totalMoneyOut <= $totalAllocated => 15,
                $totalMoneyOut <= $totalAllocated * 1.2 => 8,
                default => 0,
            };
        } else {
            $coverageScore = 10; // neutral if no expenses set
        }
        $score += $coverageScore;

        // Income consistency (15%)
        $incomeCount = $transactions->where('type', 'in')->count();
        $consistencyScore = match (true) {
            $incomeCount >= 4 => 15,
            $incomeCount >= 2 => 10,
            $incomeCount === 1 => 5,
            default => 0,
        };
        $score += $consistencyScore;

        // No deficit (15%)
        $deficitScore = $netBalance >= 0 ? 15 : 0;
        $score += $deficitScore;

        return min(100, $score);
    }

    private function computeSummary($transactions, float $totalIncome, float $totalMoneyOut, float $totalSaved, float $netBalance, float $totalAllocated, $expenses): array
    {
        $wentWell = [];
        $improvements = [];
        $recommendations = [];

        // Savings
        if ($totalIncome > 0) {
            $savingsRate = ($totalSaved / $totalIncome) * 100;
            if ($savingsRate >= 50) {
                $wentWell[] = "You saved {$savingsRate}% of your income this month — exceptional discipline!";
            } elseif ($savingsRate >= 30) {
                $wentWell[] = "You saved {$savingsRate}% of your income — solid saving habit.";
            } elseif ($totalSaved > 0) {
                $improvements[] = "Your savings rate is {$savingsRate}% — aim for at least 20% of income.";
                $recommendations[] = "Consider increasing your income split to 20-30% to build savings faster.";
            } else {
                $improvements[] = "No savings recorded this month.";
                $recommendations[] = "Start by saving even 10% of your next income — small steps build habits.";
            }
        }

        // Net balance
        if ($netBalance > 0) {
            $wentWell[] = "You ended the month with a surplus of Rp " . number_format($netBalance, 0) . ".";
        } elseif ($netBalance === 0) {
            $improvements[] = "You broke even this month — income matched outflows.";
            $recommendations[] = "Look for one recurring expense to trim to create a surplus.";
        } else {
            $improvements[] = "You ended the month with a deficit of Rp " . number_format(abs($netBalance), 0) . ".";
            $recommendations[] = "Review your variable expenses — small cuts in daily spending add up quickly.";
        }

        // Expense budgets
        foreach ($expenses as $expense) {
            $expenseTx = $transactions->where('type', 'out')->filter(fn($t) => $expense->matchesTransaction($t->description));
            $spent = (float) $expenseTx->sum('amount');
            $allocated = $expense->allocated_amount;

            if ($allocated > 0) {
                $overPct = round(($spent - $allocated) / $allocated * 100);
                if ($spent > $allocated) {
                    $improvements[] = "{$expense->name} exceeded budget by {$overPct}% (Rp " . number_format($spent - $allocated, 0) . " over).";
                    $recommendations[] = "Consider reviewing your {$expense->name} spending — tracking purchases helps control costs.";
                } elseif ($spent < $allocated * 0.5) {
                    $saved = $allocated - $spent;
                    $wentWell[] = "You spent Rp " . number_format($saved, 0) . " less than your {$expense->name} budget.";
                }
            }
        }

        // Need vs Want
        $needTx = $transactions->where('type', 'out')->where('need_or_want', 'need');
        $wantTx = $transactions->where('type', 'out')->where('need_or_want', 'want');
        $totalCategorized = $needTx->sum('amount') + $wantTx->sum('amount');
        if ($totalCategorized > 0) {
            $needPct = ($needTx->sum('amount') / $totalCategorized) * 100;
            if ($needPct >= 70) {
                $wentWell[] = "{$needPct}% of your expenses were needs — smart prioritization.";
            } elseif ($needPct < 40) {
                $improvements[] = "{$needPct}% of expenses were needs — consider prioritizing essentials.";
                $recommendations[] = "Track impulse purchases in a wants category to increase awareness.";
            }
        }

        // Income consistency
        $incomeCount = $transactions->where('type', 'in')->count();
        if ($incomeCount === 0) {
            $recommendations[] = "No income recorded this month — ensure all income is being logged.";
        }

        // General recommendations if list is empty
        if (empty($recommendations) && $netBalance > 0) {
            $recommendations[] = "You're doing well! Consider setting a higher savings goal for next month.";
        }

        return [
            'went_well' => array_slice($wentWell, 0, 4),
            'improvements' => array_slice($improvements, 0, 4),
            'recommendations' => array_slice($recommendations, 0, 4),
        ];
    }
}
