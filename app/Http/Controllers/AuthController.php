<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:2',
        ]);

        $identifier = trim($request->username);
        $password = $request->password;

        // Find user by username OR email
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        // "Mama" logs in with username only, no password needed
        if (strtolower($identifier) === 'mama') {
            $user = User::firstOrCreate(
                ['username' => 'Mama'],
                ['name' => 'Mama', 'password' => null, 'password_change_required' => false]
            );

            $this->setAuthSession($user);
            return $this->redirectToLanding();
        }

        // All other users need a password
        if (!$user || !$user->password) {
            return back()->withErrors(['username' => 'Account not found.'])->onlyInput('username');
        }

        if (!Hash::check($password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.'])->onlyInput('username');
        }

        $this->setAuthSession($user);
        return $this->redirectToLanding();
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['user_id', 'username', 'landing']);
        return redirect('/login');
    }

    public function showRegister()
    {
        return view('register');
    }

    public function showForgotPassword()
    {
        return view('forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'min:2',
                function ($attribute, $value, $fail) {
                    if (strtolower(trim($value)) === 'mama') {
                        $fail('Mama cannot reset password this way.');
                    }
                },
            ],
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $identifier = trim($request->username);

        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$user) {
            return back()->with('error', 'User not found.')->onlyInput('username');
        }

        $user->password = $request->new_password;
        $user->save();

        return redirect('/login')->with('success', 'Password changed successfully. You can now sign in with your new password.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'unique:users,username',
                function ($attribute, $value, $fail) {
                    if (strtolower(trim($value)) === 'mama') {
                        $fail('The username "Mama" is reserved.');
                    }
                },
            ],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'username' => trim($request->username),
            'email' => strtolower(trim($request->email)),
            'name' => trim($request->username),
            'password' => $request->password,
            'password_change_required' => false,
        ]);

        $this->setAuthSession($user);
        return $this->redirectToLanding();
    }

    private function setAuthSession(User $user): void
    {
        session([
            'user_id' => $user->id,
            'username' => $user->username,
            'landing' => 'tracker',
        ]);
    }

    private function redirectToLanding(): \Illuminate\Http\RedirectResponse
    {
        if (session('landing') === 'dashboard') {
            return redirect('/dashboard');
        }
        return redirect('/');
    }
}
