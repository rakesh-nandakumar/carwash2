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

        // Log in the tenant's context
        app(AuditService::class)->log(
            'impersonation.started',
            "Impersonation started for tenant '{$tenant->name}' by central admin {$admin->email}",
            'info',
            'central_admin',
            $admin->email,
            [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
            ],
            false,
            $tenant->id
        );

        return redirect($url);
    }
}
