<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
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
            'account_type' => $request->account_type ?: null,
        ];

        if ($request->type === 'in' && $request->has('is_split') && $request->is_split == '1') {
            $savePct = (float) $request->save_pct;
            $spendPct = (float) $request->spend_pct;

            if (abs(($savePct + $spendPct) - 100) > 0.01) {
                $spendPct = 100 - $savePct;
            }

            $savedAmt = $request->amount * $savePct / 100;
            $spentAmt = $request->amount * $spendPct / 100;

            $distJson = null;
            $savingsAllocated = false;
            if ($request->has('savings_distribution') && !empty($request->savings_distribution)) {
                $distJson = json_encode($request->savings_distribution);
                $savingsAllocated = true;
            } else {
                $template = session('distribution_template', []);
                if (!empty($template)) {
                    $dist = [];
                    foreach ($template as $item) {
                        $dist[] = [
                            'category' => $item['name'],
                            'pct' => $item['pct'],
                            'icon' => $item['icon'] ?? '📦',
                            'amount' => round($savedAmt * $item['pct'] / 100),
                        ];
                    }
                    $distJson = json_encode($dist);
                    $savingsAllocated = true;
                }
            }

            $data = array_merge($data, [
                'save_pct' => $savePct,
                'spend_pct' => $spendPct,
                'saved_amount' => $savedAmt,
                'spent_amount' => $spentAmt,
                'is_split' => true,
                'split_preset' => $request->split_preset ?? 'custom',
                'savings_distribution' => $distJson,
                'savings_allocated' => $savingsAllocated,
            ]);
        }

        if ($request->type === 'out') {
            if ($request->has('need_or_want')) {
                $data['need_or_want'] = $request->need_or_want;
            }
            if ($request->has('expense_category')) {
                $data['expense_category'] = $request->expense_category;
            }
            if ($request->has('distribution_category') && !empty($request->distribution_category)) {
                $data['savings_distribution'] = json_encode([
                    ['category' => $request->distribution_category, 'pct' => 100, 'amount' => $request->amount],
                ]);
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
        $data['account_type'] = $request->account_type ?: null;

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

        if ($request->type === 'out') {
            if ($request->has('need_or_want')) { $data['need_or_want'] = $request->need_or_want; }
            if ($request->has('expense_category')) { $data['expense_category'] = $request->expense_category; }
        }

        $transaction->update($data);

        return $this->redirectAfterAction($request);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $date = $transaction->transaction_date;
        $transaction->delete();

        return $this->redirectAfterAction($request->merge(['transaction_date' => $date]));
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
