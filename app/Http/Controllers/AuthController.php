<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class AuthController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // User is tenant-scoped via TenantScope, so the same email may exist
        // in two tenants and each /{slug}/login finds its own. `active` + the
        // standard attempt also gets throttling for free.
        if (Auth::attempt([...$credentials, 'active' => 1], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            $this->auditService->logLogin();

            if ($user->hasPermissionTo('reception.access')) {
                return redirect()->intended(route('reception.index'));
            }

            if ($user->hasPermissionTo('dashboard.access')) {
                return redirect()->intended(route('dashboard'));
            }

            if ($user->hasPermissionTo('job_cards.access')) {
                return redirect()->intended(route('jobs.index'));
            }

            if ($user->hasPermissionTo('customers.access')) {
                return redirect()->intended(route('customers.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials or inactive account.'])->withInput();
    }

    public function logout(Request $request)
    {
        $this->auditService->logLogout();
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}