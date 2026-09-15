<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpensePaymentController extends Controller
{
    public function store(Request $request, Expense $expense)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        ExpensePayment::create([
            'expense_id' => $expense->id,
            'username' => session('username'),
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('expenses.index');
    }

    public function destroy(Expense $expense, ExpensePayment $payment)
    {
        if ($payment->expense_id !== $expense->id) {
            abort(404);
        }

        $payment->delete();
        return redirect()->route('expenses.index');
    }
}
