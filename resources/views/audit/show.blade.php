@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')
<div class="page-head">
    <div>
        <h1>Audit Log Details</h1>
        <p>Complete information about this system activity.</p>
    </div>
    <a href="{{ route('audit-logs.index') }}" class="secondary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Back to Logs
    </a>
</div>

<div class="panel">
    <!-- Header Section -->
    <div class="audit-detail-header">
        <div class="audit-detail-user">
            <div class="user-avatar large">
                {{ $auditLog->user ? substr($auditLog->user->name, 0, 1) : 'S' }}
            </div>
            <div class="user-info">
                <strong>{{ $auditLog->user->name ?? 'System' }}</strong>
                @if($auditLog->user)
                    <small>{{ $auditLog->user->email }}</small>
                @endif
            </div>
        </div>
        @php
            $actionClass = match($auditLog->action) {
                'created', 'added' => 'action-created',
                'updated', 'modified' => 'action-updated',
                'deleted', 'removed' => 'action-deleted',
                'cancelled' => 'action-cancelled',
                'login', 'logout' => 'action-login',
                default => 'action-default'
            };
        @endphp
        <span class="{{ $actionClass }} large">
            @if($auditLog->action === 'created' || $auditLog->action === 'added')
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            @elseif($auditLog->action === 'updated' || $auditLog->action === 'modified')
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            @elseif($auditLog->action === 'deleted' || $auditLog->action === 'removed')
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            @elseif($auditLog->action === 'cancelled')
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            @elseif($auditLog->action === 'login')
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
            @elseif($auditLog->action === 'logout')
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            @else
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            @endif
            {{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}
        </span>
    </div>

    <!-- Details Grid -->
    <div class="detail-grid">
        <div class="detail-item">
            <label>Timestamp</label>
            <div class="detail-value">
                <strong>{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</strong>
                <small>{{ $auditLog->created_at->diffForHumans() }}</small>
            </div>
        </div>

        <div class="detail-item">
            <label>Entity Type</label>
            <div class="detail-value">
                <strong>{{ $auditLog->entity_type ?? 'N/A' }}</strong>
            </div>
        </div>

        <div class="detail-item">
            <label>Entity ID</label>
            <div class="detail-value">
                <strong>#{{ $auditLog->entity_id ?? 'N/A' }}</strong>
            </div>
        </div>

        <div class="detail-item">
            <label>IP Address</label>
            <div class="detail-value">
                <span class="ip-address">{{ $auditLog->ip ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    @if($auditLog->reason)
        <div class="reason-section">
            <label>Reason</label>
            <div class="reason-text">{{ $auditLog->reason }}</div>
        </div>
    @endif

    <!-- Data Changes Section -->
    @if($auditLog->old_value || $auditLog->new_value)
        <div class="data-changes-section">
            <h3>Data Changes</h3>
            
            @if($auditLog->old_value && !empty($auditLog->old_value))
                <div class="data-change-card old-value-card">
                    <div class="data-change-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <strong>Old Value</strong>
                    </div>
                    <div class="data-change-content">
                        <pre>{{ json_encode($auditLog->old_value, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif

            @if($auditLog->new_value && !empty($auditLog->new_value))
                <div class="data-change-card new-value-card">
                    <div class="data-change-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <strong>New Value</strong>
                    </div>
                    <div class="data-change-content">
                        <pre>{{ json_encode($auditLog->new_value, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<style>
/* Audit Detail Header */
.audit-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    margin-bottom: 24px;
    color: white;
}

.audit-detail-user {
    display: flex;
    align-items: center;
    gap: 16px;
}

.user-avatar.large {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 24px;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.audit-detail-user .user-info strong {
    display: block;
    font-size: 18px;
    color: white;
}

.audit-detail-user .user-info small {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
}

.action-created.large,
.action-updated.large,
.action-deleted.large,
.action-cancelled.large,
.action-login.large,
.action-default.large {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Detail Grid */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.detail-item {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
}

.detail-item label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 8px;
}

.detail-value strong {
    display: block;
    font-size: 15px;
    color: #111827;
}

.detail-value small {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

.ip-address {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
    color: #6b7280;
    background: white;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
}

/* Reason Section */
.reason-section {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}

.reason-section label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #92400e;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 8px;
}

.reason-text {
    font-size: 14px;
    color: #111827;
    line-height: 1.5;
}

/* Data Changes Section */
.data-changes-section {
    margin-top: 24px;
}

.data-changes-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 16px;
}

.data-change-card {
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 16px;
    border: 1px solid;
}

.old-value-card {
    border-color: #fecaca;
    background: #fef2f2;
}

.new-value-card {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.data-change-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 14px;
}

.old-value-card .data-change-header {
    color: #991b1b;
    background: #fee2e2;
    border-bottom: 1px solid #fecaca;
}

.new-value-card .data-change-header {
    color: #166534;
    background: #dcfce7;
    border-bottom: 1px solid #bbf7d0;
}

.data-change-content {
    padding: 16px;
}

.data-change-content pre {
    font-size: 13px;
    white-space: pre-wrap;
    word-break: break-all;
    margin: 0;
    color: #111827;
    line-height: 1.5;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .audit-detail-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
        padding: 20px;
    }

    .audit-detail-user {
        width: 100%;
    }

    .action-created.large,
    .action-updated.large,
    .action-deleted.large,
    .action-cancelled.large,
    .action-login.large,
    .action-default.large {
        width: 100%;
        justify-content: center;
    }

    .detail-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head a.secondary {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
</style>
@endsection