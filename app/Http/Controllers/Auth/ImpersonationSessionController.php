<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Impersonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Consumes an impersonation token minted by Central\ImpersonationController
 * and turns it into a real tenant session on the tenant's own prefix. The
 * session is regenerated so the central session id is never carried over.
 */
class ImpersonationSessionController extends Controller
{
    public function __construct(private readonly Impersonation $impersonation) {}

    public function store(Request $request, string $token)
    {
        $tenantId = app(\App\Services\CurrentContext::class)->tenantId();

        if ($tenantId === null) {
            abort(404);
        }

        $user = $this->impersonation->consume($token, $tenantId);

        if ($user === null) {
            abort(404, 'This impersonation link is invalid or has expired.');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $request->session()->put('impersonated_by_central', true);

        return redirect()->route('tenant.home');
    }
}
