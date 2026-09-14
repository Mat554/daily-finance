<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Models\Transaction;
use App\Models\AccountBalance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(Request $request)
    {
        $analytics = $this->dashboardService->getAnalytics($request, session('username'));
        return view('dashboard', $analytics);
    }

    public function saveDistribution(Request $request)
    {
        $transactionIds = $request->input('transaction_ids', []);
        $distribution = $request->input('distribution');
        $savePct = (float) $request->input('save_pct', 0);
        $spendPct = (float) $request->input('spend_pct', 100 - $savePct);

        if (empty($transactionIds) && empty($distribution)) {
            return $this->redirectAfterAction($request);
        }

        $dist = null;
        if ($distribution) {
            $dist = is_string($distribution) ? json_decode($distribution, true) : $distribution;
        }

        if (!empty($transactionIds)) {
            $txs = Transaction::where('username', session('username'))
                ->whereIn('id', $transactionIds)
                ->where('type', 'in')
                ->where(function ($q) {
                    $q->where('is_split', false)->orWhereNull('is_split');
                })
                ->get();

            foreach ($txs as $tx) {
                $savedAmount = $tx->amount * $savePct / 100;
                $spentAmount = $tx->amount * $spendPct / 100;

                $tx->update([
                    'is_split' => true,
                    'save_pct' => $savePct,
                    'spend_pct' => $spendPct,
                    'saved_amount' => $savedAmount,
                    'spent_amount' => $spentAmount,
                    'split_preset' => 'batch',
                    'savings_distribution' => $dist ? json_encode($dist) : null,
                    'savings_allocated' => !empty($dist),
                ]);
            }

            if ($dist && !$txs->isEmpty()) {
                $totalSaved = $txs->sum(fn($tx) => $tx->amount * $savePct / 100);
                foreach ($dist as &$item) {
                    $item['amount'] = round($totalSaved * ($item['pct'] ?? 0) / 100);
                }
                unset($item);
                $distJson = json_encode($dist);
                foreach ($txs as $tx) {
                    $tx->update(['savings_distribution' => $distJson]);
                }
            }
        }

        return $this->redirectAfterAction($request);
    }

    public function saveDistributionTemplate(Request $request)
    {
        $template = $request->input('template', []);
        $savePct = (float) $request->input('save_pct', 50);

        if (is_string($template)) {
            $template = json_decode($template, true) ?? [];
        }

        $clean = [];
        foreach ($template as $item) {
            $category = trim($item['name'] ?? $item['category'] ?? '');
            $pct = (float) ($item['pct'] ?? 0);
            $icon = $item['icon'] ?? '📦';
            if ($category !== '' && $pct > 0) {
                $clean[] = ['category' => $category, 'pct' => $pct, 'icon' => $icon];
            }
        }

        session(['distribution_template' => $clean]);
        session(['distribution_save_pct' => $savePct]);

        return $this->redirectAfterAction($request);
    }

    public function deleteDistributionTemplate(Request $request)
    {
        session()->forget('distribution_template');
        session()->forget('distribution_save_pct');
        return $this->redirectAfterAction($request);
    }

    public function saveAccountBalances(Request $request)
    {
        $username = session('username');
        $balances = $request->input('balances', []);

        foreach (AccountBalance::TYPES as $type) {
            $amount = isset($balances[$type]) ? (float) $balances[$type] : 0;
            AccountBalance::updateOrCreate(
                ['username' => $username, 'account_type' => $type],
                ['balance' => $amount]
            );
        }

        return $this->redirectAfterAction($request);
    }

    private function redirectAfterAction(Request $request): \Illuminate\Http\RedirectResponse
    {
        $referer = $request->headers->get('referer', '');
        if ($referer && filter_var($referer, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($referer);
            $path = $parsed['path'] ?? '/';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            return redirect($path . $query);
        }

        $landing = session('landing', 'tracker');
        if ($landing === 'dashboard') {
            return redirect('/dashboard');
        }

        $date = $request->input('transaction_date') ?? $request->input('date') ?? now()->toDateString();
        return redirect('/?date=' . $date);
    }
}
