<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Tenancy\TestInstanceService;
use Illuminate\Http\Request;

class TestInstanceController extends Controller
{
    public function __construct(private readonly TestInstanceService $service) {}

    public function create(Request $request, Tenant $tenant)
    {
        $test = $this->service->create($tenant, $request->user('central')->id);

        return redirect()->route('central.tenants.show', $test)
            ->with('success', "Test instance created — reachable at /{$test->slug}/. It holds its own copy of {$tenant->name}'s data.");
    }

    public function sync(Request $request, Tenant $tenant)
    {
        $result = $this->service->syncFromLive($tenant, $request->user('central')->id);

        return back()->with('success', 'Synced from live: '.$result['total_rows'].' rows copied.');
    }

    public function destroy(Request $request, Tenant $tenant)
    {
        $this->service->destroy($tenant);

        return back()->with('success', 'Test instance destroyed.');
    }
}
