<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class FinanceController extends Controller
{
public function index(Request $request)
    {
        // 1. Look for a date in the URL, otherwise default to today
        $currentDate = $request->query('date', Carbon::today()->toDateString());
        
        // 2. Fetch data ONLY for the currently selected date
        $transactions = Transaction::whereDate('transaction_date', $currentDate)->latest()->get();

        $totalIn = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');
        
        $balance = $totalIn - $totalOut;

        // 3. Pass $currentDate to the view so the calendar knows what day it is
        return view('tracker', compact('transactions', 'totalIn', 'totalOut', 'balance', 'currentDate'));
    }
public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required|in:in,out',
            'transaction_date' => 'required|date' 
        ]);

        Transaction::create($request->only(['description', 'amount', 'type', 'transaction_date']));

        // redirect back to the SPECIFIC DATE you just added data to
        return redirect('/?date=' . $request->transaction_date);
    }
    public function edit(Transaction $transaction)
    {
        // Load a new view and pass the specific transaction to it
        return view('edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'description' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required|in:in,out',
            'transaction_date' => 'required|date'
        ]);

        $transaction->update($request->only(['description', 'amount', 'type', 'transaction_date']));

        // Redirect back to the date of the updated transaction
        return redirect('/?date=' . $request->transaction_date);
    }
    
    public function history()
    {
        // Get all transactions, order by newest date first, and group them by date
        $groupedTransactions = Transaction::orderBy('transaction_date', 'desc')
            ->get()
            ->groupBy('transaction_date');

        return view('history', compact('groupedTransactions'));
    }
}
