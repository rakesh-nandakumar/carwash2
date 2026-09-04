<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class CentralDashboardController extends Controller
{
    public function index()
    {
        $tenants = Tenant::query()->withCount(['users'])->live()->orderBy('name')->get();

        $totals = [
            'tenants' => Tenant::query()->withTrashed()->count(),
            'live' => Tenant::query()->live()->count(),
            'test' => Tenant::query()->testInstances()->count(),
            'users' => DB::table('users')->count(),
        ];

        return view('central.dashboard', compact('tenants', 'totals'));
    }
}
