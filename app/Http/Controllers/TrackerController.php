<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrackerController extends Controller
{
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

    public function history()
    {
        $groupedTransactions = Transaction::where('username', session('username'))
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->groupBy('transaction_date');

        return view('history', compact('groupedTransactions'));
    }
}
