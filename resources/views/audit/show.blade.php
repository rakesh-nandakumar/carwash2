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
                {{ $auditLog->actor_email ? substr($auditLog->actor_email, 0, 1) : 'S' }}
            </div>
            <div class="user-info">
                <strong>{{ $auditLog->actor_email ?? 'System' }}</strong>
                <small>{{ ucfirst(str_replace('_', ' ', $auditLog->actor_type ?? 'unknown')) }}</small>
            </div>
        </div>
        @php
            $severityClass = match($auditLog->severity) {
                'info' => 'severity-info',
                'warning' => 'severity-warning',
                'error' => 'severity-error',
                'critical' => 'severity-critical',
                default => 'severity-default'
            };
        @endphp
        <div>
            <span class="{{ $severityClass }} large">
                {{ ucfirst($auditLog->severity) }}
            </span>
            @if($auditLog->is_flagged)
                <span class="flagged-badge large">⚠️ Flagged</span>
            @endif
        </div>
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
            <label>Event Key</label>
            <div class="detail-value">
                <span class="event-key">{{ $auditLog->event_key }}</span>
            </div>
        </div>

        <div class="detail-item">
            <label>Severity</label>
            <div class="detail-value">
                <span class="{{ $severityClass }}">
                    {{ ucfirst($auditLog->severity) }}
                </span>
            </div>
        </div>

        <div class="detail-item">
            <label>IP Address</label>
            <div class="detail-value">
                <span class="ip-address">{{ $auditLog->ip_address ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    @if($auditLog->description)
        <div class="description-section">
            <label>Description</label>
            <div class="description-text">{{ $auditLog->description }}</div>
        </div>
    @endif

    <!-- Meta Data Section -->
    @if($auditLog->meta && !empty($auditLog->meta))
        <div class="meta-section">
            <h3>Meta Data</h3>
            <div class="meta-content">
                <pre>{{ json_encode($auditLog->meta, JSON_PRETTY_PRINT) }}</pre>
            </div>
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

.severity-info.large,
.severity-warning.large,
.severity-error.large,
.severity-critical.large,
.severity-default.large {
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

.flagged-badge.large {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
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

.event-key {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
    color: #6b7280;
    background: white;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
}

/* Description Section */
.description-section {
    background: #f8fafc;
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}

.description-section label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 8px;
}

.description-text {
    font-size: 14px;
    color: #111827;
    line-height: 1.5;
}

/* Meta Data Section */
.meta-section {
    margin-top: 24px;
}

.meta-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 16px;
}

.meta-content {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
}

.meta-content pre {
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

    .severity-info.large,
    .severity-warning.large,
    .severity-error.large,
    .severity-critical.large,
    .severity-default.large {
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