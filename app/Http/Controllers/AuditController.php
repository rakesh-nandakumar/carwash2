<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest('created_at');

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by entity type
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
        }

        // Mobile devices get fewer results per page
        $perPage = $request->isMobile() ? 10 : 50;
        $logs = $query->paginate($perPage)->withQueryString();

        // Get available action types for filter dropdown
        $actionTypes = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Get available entity types for filter dropdown
        $entityTypes = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        // Get available users for filter dropdown
        $users = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        // Get statistics
        $totalLogs = AuditLog::where('tenant_id', auth()->user()->tenant_id)->count();
        $todayLogs = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->whereDate('created_at', today())
            ->count();
        $weekLogs = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();
        $uniqueUsers = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->distinct('user_id')
            ->count('user_id');

        return view('audit.index', compact('logs', 'actionTypes', 'entityTypes', 'users', 'totalLogs', 'todayLogs', 'weekLogs', 'uniqueUsers'));
    }

    public function show($id)
    {
        // Since AuditLog uses BelongsToTenant, we need to bypass tenant scope
        $auditLog = AuditLog::withoutTenantScope()
            ->where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $auditLog->load('user');

        return view('audit.show', compact('auditLog'));
    }
}