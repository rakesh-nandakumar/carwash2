<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('central')->check()) {
            return redirect()->route('central.dashboard');
        }

        return view('central.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = CentralAdmin::where('email', $credentials['email'])->first();

        if (! $admin || ! $admin->is_active || ! Auth::guard('central')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('central.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('central')->logout();

        return redirect()->route('central.login');
    }
}
