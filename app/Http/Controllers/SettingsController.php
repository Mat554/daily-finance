<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function setPreference(Request $request)
    {
        $request->validate([
            'landing' => 'required|in:tracker,dashboard',
        ]);

        session(['landing' => $request->landing]);

        $target = $request->input('_target');
        if ($target && in_array($target, ['/', '/dashboard'])) {
            return redirect($target);
        }

        return back();
    }
}
