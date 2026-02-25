<?php

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
        
        // 2. Fetch data ONLY for the currently selected date AND the logged-in user
        $transactions = Transaction::whereDate('transaction_date', $currentDate)
            ->where('username', session('username')) // <-- This isolates the data!
            ->latest()
            ->get();

        $totalIn = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->where('type', 'out')->sum('amount');
        
        $balance = $totalIn - $totalOut;

        // 3. Pass $currentDate to the view so the calendar knows what day it is
        return view('tracker', compact('transactions', 'totalIn', 'totalOut', 'balance', 'currentDate'));
    }

   public function store(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'description' => 'required',
            'amount' => 'required|numeric',
            'type' => 'required|in:in,out',
            'transaction_date' => 'required|date' 
        ]);

        // 2. Create the transaction explicitly so we ignore the _token
        Transaction::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'transaction_date' => $request->transaction_date,
            'username' => session('username'), // Attach the logged-in user!
        ]);
        
        // 3. Redirect back to the specific date
        return redirect('/?date=' . $request->transaction_date);
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
            'transaction_date' => 'required|date'
        ]);

        $transaction->update($request->only(['description', 'amount', 'type', 'transaction_date']));

        return redirect('/?date=' . $request->transaction_date);
    }
    
    public function history()
    {
        // Get all transactions for the LOGGED-IN USER ONLY, order by newest, group by date
        $groupedTransactions = Transaction::where('username', session('username')) // <-- Isolates the data!
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->groupBy('transaction_date');

        return view('history', compact('groupedTransactions'));
    }
}