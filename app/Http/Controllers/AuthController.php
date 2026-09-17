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
        // Get business settings for logo display on login page
        $settings = [
            'company_name' => 'AutoCare Pro',
            'logo_path' => ''
        ];
        
        // Try to get the business from the current tenant context
        $tenant = app(\App\Services\CurrentContext::class)->tenant();
        if ($tenant) {
            // Read settings directly using Settings service
            $settings = [
                'company_name' => \App\Services\Settings::str('business.company_name', 'AutoCare Pro'),
                'logo_path' => \App\Services\Settings::str('branding.logo_path', ''),
            ];
        }
        
        return view('auth.login', compact('settings'));
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

            // Redirect to the first accessible module based on user permissions
            $permissionToRoute = [
                'reception.access' => 'reception.index',
                'dashboard.access' => 'dashboard',
                'job_cards.access' => 'jobs.index',
                'customers.access' => 'customers.index',
                'vehicles.access' => 'vehicles.index',
                'appointments.access' => 'appointments.index',
                'inventory.access' => 'inventory.index',
                'barcode_labels.access' => 'barcode-labels.index',
                'stock_adjustments.access' => 'stock-adjustments.index',
                'categories.access' => 'categories.index',
                'services.access' => 'services.index',
                'suppliers.access' => 'suppliers.index',
                'grns.access' => 'grns.index',
                'return_grns.access' => 'return_grns.index',
                'invoices.access' => 'invoices.index',
                'cashier.access' => 'cashier.index',
                'cheque_payments.access' => 'cheque-payments.index',
                'notifications.access' => 'notifications.index',
                'reports.access' => 'reports',
                'users.access' => 'users.index',
                'roles.access' => 'roles.index',
                'settings.access' => 'tills.index',
                'audit_logs.access' => 'audit-logs.index',
            ];

            foreach ($permissionToRoute as $permission => $route) {
                if ($user->hasPermissionTo($permission)) {
                    return redirect()->route($route);
                }
            }

            // If user has no permissions, logout and redirect to login with error message
            Auth::logout();
            return redirect()->route('login')->with('error', 'You do not have access to any modules. Please contact your administrator.');
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