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
        $query = AuditLog::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest('created_at');

        // Filter by event key
        if ($request->filled('event_key')) {
            $query->where('event_key', $request->event_key);
        }

        // Filter by severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter by actor email
        if ($request->filled('actor_email')) {
            $query->where('actor_email', $request->actor_email);
        }

        // Filter by actor type
        if ($request->filled('actor_type')) {
            $query->where('actor_type', $request->actor_type);
        }

        // Filter by flagged status
        if ($request->filled('is_flagged')) {
            $query->where('is_flagged', $request->boolean('is_flagged'));
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

        // Get available event keys for filter dropdown
        $eventKeys = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->select('event_key')
            ->distinct()
            ->orderBy('event_key')
            ->pluck('event_key');

        // Get available severities for filter dropdown
        $severities = ['info', 'warning', 'error', 'critical'];

        // Get available actor types for filter dropdown
        $actorTypes = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->select('actor_type')
            ->distinct()
            ->orderBy('actor_type')
            ->pluck('actor_type');

        // Get available actor emails for filter dropdown
        $actorEmails = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->select('actor_email')
            ->distinct()
            ->orderBy('actor_email')
            ->pluck('actor_email');

        // Get statistics
        $totalLogs = AuditLog::where('tenant_id', auth()->user()->tenant_id)->count();
        $todayLogs = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->whereDate('created_at', today())
            ->count();
        $weekLogs = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();
        $flaggedLogs = AuditLog::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_flagged', true)
            ->count();

        return view('audit.index', compact('logs', 'eventKeys', 'severities', 'actorTypes', 'actorEmails', 'totalLogs', 'todayLogs', 'weekLogs', 'flaggedLogs'));
    }

    public function show($id)
    {
        // Since AuditLog uses BelongsToTenant, we need to bypass tenant scope
        $auditLog = AuditLog::withoutTenantScope()
            ->where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        return view('audit.show', compact('auditLog'));
    }
}