<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index()
    {
        $goal = session('savings_goal', 0);
        return view('savings-goal', compact('goal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'goal' => 'required|numeric|min:0',
        ]);

        session(['savings_goal' => (float) $request->goal]);

        return redirect()->route('savings-goal.index')->with('success', 'Savings goal updated!');
    }

    public function destroy(Request $request)
    {
        session()->forget('savings_goal');
        return redirect()->route('savings-goal.index');
    }
}
