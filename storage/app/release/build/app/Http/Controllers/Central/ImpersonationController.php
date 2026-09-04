<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAdmin;
use App\Models\Tenant;
use App\Services\AuditService;
use App\Services\CurrentContext;
use App\Services\Impersonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct(private readonly Impersonation $impersonation) {}

    public function store(Request $request, Tenant $tenant)
    {
        $admin = $request->user('central');

        ['url' => $url, 'user' => $user] = $this->impersonation->startFor($tenant, $admin);

        // Runs inside the tenant's context so the audit row is stamped with
        // the tenant_id and shows in the tenant's own audit trail too.
        app(CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant, $user, $admin): void {
            app(AuditService::class)->log('impersonation.started', 'Tenant', $tenant->id, null, [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'central_admin' => $admin->email,
            ]);
        });

        return redirect($url);
    }
}
