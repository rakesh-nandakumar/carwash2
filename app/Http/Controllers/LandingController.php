<?php

namespace App\Http\Controllers;

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

        if ($user->hasPermission('view_reception')) {
            return redirect()->route('reception.index');
        }

        if ($user->hasPermission('view_dashboard')) {
            return redirect()->route('dashboard');
        }

        if ($user->hasPermission('view_job_cards')) {
            return redirect()->route('jobs.index');
        }

        if ($user->hasPermission('view_customers')) {
            return redirect()->route('customers.index');
        }

        return redirect()->route('dashboard');
    }
}
