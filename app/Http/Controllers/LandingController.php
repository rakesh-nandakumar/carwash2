<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * The tenant's landing page: redirects a signed-in tenant user to whichever
 * screen they're allowed onto, verbatim from the old `/` closure in
 * routes/web.php. Lives at /{slug}/.
 */
class LandingController extends Controller
{
    public function __invoke()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Redirect to the first accessible module based on user permissions
        $permissionToRoute = [
            'reception.access' => 'reception.index',
            'dashboard.access' => 'dashboard',
            'job_cards.access' => 'jobs.index',
            'customers.access' => 'customers.index',
            'vehicles.access' => 'vehicles.index',
            'appointments.access' => 'appointments.index',
            'inventory.access' => 'inventory.index',
            'categories.access' => 'categories.index',
            'services.access' => 'services.index',
            'invoices.access' => 'invoices.index',
            'cashier.access' => 'cashier.index',
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
}