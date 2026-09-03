<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('AuthController@login called with email: ' . $request->input('email'));

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user) {
            Log::info('User not found: ' . $credentials['email']);
            return back()->withErrors(['email' => 'User not found.'])->withInput();
        }

        Log::info('User found: ' . $credentials['email'] . ', Active: ' . ($user->active ? 'yes' : 'no'));

        if (!$user->active) {
            return back()->withErrors(['email' => 'Account is inactive.'])->withInput();
        }

        $check = \Hash::check($credentials['password'], $user->password);
        Log::info('Password check for ' . $credentials['email'] . ': ' . ($check ? 'success' : 'failed'));

        if ($check) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // Load roles and permissions before checking
            $user->load('roles.permissions');

            // Redirect based on permissions
            if ($user->hasPermission('view_reception')) {
                return redirect()->intended('/reception');
            } elseif ($user->hasPermission('view_dashboard')) {
                return redirect()->intended('/dashboard');
            } elseif ($user->hasPermission('view_job_cards')) {
                return redirect()->intended('/jobs');
            } elseif ($user->hasPermission('view_customers')) {
                return redirect()->intended('/customers');
            } else {
                return redirect()->intended('/dashboard');
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
