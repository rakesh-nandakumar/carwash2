@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Audit Logs</h1>
        <p>Track all system activities and changes for security and compliance.</p>
    </div>
    <button type="button" class="primary" onclick="exportAuditLogs()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        Export
    </button>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-total">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Logs</div>
            <div class="stat-value">{{ number_format($totalLogs) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-today">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Today</div>
            <div class="stat-value">{{ number_format($todayLogs) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-week">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">This Week</div>
            <div class="stat-value">{{ number_format($weekLogs) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-flagged">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                <line x1="4" y1="22" x2="4" y2="15"></line>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Flagged</div>
            <div class="stat-value">{{ number_format($flaggedLogs) }}</div>
        </div>
    </div>
</div>

<div class="search">
    <input id="auditSearch" placeholder="Search audit logs..." oninput="filterAuditLogs()">
    <button class="filter-btn" onclick="openFilterModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
    </button>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Filter Audit Logs</h2>
            <button class="modal-close" onclick="closeFilterModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="filter-section">
                <label>Quick Date Filter</label>
                <div class="quick-filters">
                    <button type="button" class="quick-filter-btn" onclick="setQuickDate('today')">Today</button>
                    <button type="button" class="quick-filter-btn" onclick="setQuickDate('week')">This Week</button>
                    <button type="button" class="quick-filter-btn" onclick="setQuickDate('month')">This Month</button>
                    <button type="button" class="quick-filter-btn" onclick="setQuickDate('clear')">Clear</button>
                </div>
            </div>
            <div class="filter-section">
                <label>Event Key</label>
                <select id="eventKeyFilter" onchange="applyFilters()">
                    <option value="">All Events</option>
                    @foreach($eventKeys as $eventKey)
                        <option value="{{ $eventKey }}" {{ request('event_key') == $eventKey ? 'selected' : '' }}>
                            {{ $eventKey }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-section">
                <label>Severity</label>
                <select id="severityFilter" onchange="applyFilters()">
                    <option value="">All Severities</option>
                    @foreach($severities as $severity)
                        <option value="{{ $severity }}" {{ request('severity') == $severity ? 'selected' : '' }}>
                            {{ ucfirst($severity) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-section">
                <label>Actor Type</label>
                <select id="actorTypeFilter" onchange="applyFilters()">
                    <option value="">All Actor Types</option>
                    @foreach($actorTypes as $actorType)
                        <option value="{{ $actorType }}" {{ request('actor_type') == $actorType ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $actorType)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-section">
                <label>Actor Email</label>
                <select id="actorEmailFilter" onchange="applyFilters()">
                    <option value="">All Actors</option>
                    @foreach($actorEmails as $actorEmail)
                        <option value="{{ $actorEmail }}" {{ request('actor_email') == $actorEmail ? 'selected' : '' }}>
                            {{ $actorEmail }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-section">
                <label>Flagged Only</label>
                <select id="flaggedFilter" onchange="applyFilters()">
                    <option value="">All</option>
                    <option value="1" {{ request('is_flagged') == '1' ? 'selected' : '' }}>Flagged Only</option>
                </select>
            </div>
            <div class="filter-section">
                <label>From Date</label>
                <input type="date" id="fromDateFilter" value="{{ request('from_date') }}" onchange="applyFilters()">
            </div>
            <div class="filter-section">
                <label>To Date</label>
                <input type="date" id="toDateFilter" value="{{ request('to_date') }}" onchange="applyFilters()">
            </div>
        </div>
        <div class="modal-footer">
            <button class="secondary" onclick="clearFilters()">Clear Filters</button>
            <button class="primary" onclick="closeFilterModal()">Apply</button>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal-overlay" style="display:none;">
    <div class="modal-box details-modal-box">
        <div class="modal-header modal-header-enhanced">
            <div class="modal-title-group">
                <div class="modal-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div>
                    <h2>Audit Log Details</h2>
                    <p class="modal-subtitle">View complete information about this system activity</p>
                </div>
            </div>
            <button class="modal-close modal-close-enhanced" onclick="closeDetailsModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body modal-body-enhanced">
            <div class="details-content-wrapper">
                <!-- Quick Info Cards -->
                <div class="quick-info-grid">
                    <div class="quick-info-card">
                        <div class="quick-info-label">Event Key</div>
                        <div class="quick-info-value" id="modalEventKey">-</div>
                    </div>
                    <div class="quick-info-card">
                        <div class="quick-info-label">Severity</div>
                        <div class="quick-info-value" id="modalSeverity">-</div>
                    </div>
                    <div class="quick-info-card">
                        <div class="quick-info-label">Timestamp</div>
                        <div class="quick-info-value" id="modalTimestamp">-</div>
                    </div>
                    <div class="quick-info-card">
                        <div class="quick-info-label">IP Address</div>
                        <div class="quick-info-value" id="modalIpAddress">-</div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="detail-section">
                    <div class="detail-section-header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <h3>Description</h3>
                    </div>
                    <div class="detail-content">
                        <p id="modalDescription">-</p>
                    </div>
                </div>

                <!-- Actor Information -->
                <div class="detail-section">
                    <div class="detail-section-header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <h3>Actor Information</h3>
                    </div>
                    <div class="detail-content">
                        <div class="actor-info-grid">
                            <div class="actor-info-item">
                                <span class="actor-label">Email:</span>
                                <span class="actor-value" id="modalActorEmail">-</span>
                            </div>
                            <div class="actor-info-item">
                                <span class="actor-label">Type:</span>
                                <span class="actor-value" id="modalActorType">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meta Data Section -->
                <div class="detail-section">
                    <div class="detail-section-header">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                        <h3>Meta Data</h3>
                        <button class="copy-btn" onclick="copyMetaData()" title="Copy to clipboard">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Copy
                        </button>
                    </div>
                    <div class="detail-content">
                        <div class="meta-content-enhanced">
                            <pre id="modalMeta"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer modal-footer-enhanced">
            <button class="primary" onclick="closeDetailsModal()">Close</button>
        </div>
    </div>
</div>

<div class="panel">
    <!-- Desktop Table -->
    <div class="table-wrap">
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Event</th>
                    <th>Severity</th>
                    <th>Actor</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr data-log-id="{{ $log->id }}">
                        <td>
                            <div class="timestamp-main">{{ $log->created_at->format('Y-m-d H:i') }}</div>
                            <small class="timestamp-relative">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="event-key">{{ $log->event_key }}</div>
                            @if($log->is_flagged)
                                <span class="flagged-badge">⚠️ Flagged</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $severityClass = match($log->severity) {
                                    'info' => 'severity-info',
                                    'warning' => 'severity-warning',
                                    'error' => 'severity-error',
                                    'critical' => 'severity-critical',
                                    default => 'severity-default'
                                };
                            @endphp
                            <span class="{{ $severityClass }}">
                                {{ ucfirst($log->severity) }}
                            </span>
                        </td>
                        <td>
                            <div class="actor-cell">
                                <div class="user-avatar">
                                    {{ $log->actor_email ? substr($log->actor_email, 0, 1) : 'S' }}
                                </div>
                                <div class="user-info">
                                    <strong>{{ $log->actor_email ?? 'System' }}</strong>
                                    <small class="muted">{{ ucfirst(str_replace('_', ' ', $log->actor_type ?? 'unknown')) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="description-text">{{ $log->description }}</div>
                            @if($log->meta)
                                <button class="details-toggle" onclick="openDetailsModal({{ $log->id }}, {{ json_encode($log->meta) }})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                    View Meta
                                </button>
                            @endif
                        </td>
                        <td>
                            <span class="ip-address">{{ $log->ip_address ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('audit-logs.show', $log->id) }}" class="action-link">
                                View →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            No audit logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="audit-cards">
        @forelse($logs as $log)
            <div class="audit-card">
                <div class="card-header">
                    <div class="card-user">
                        <div class="user-avatar small">
                            {{ $log->actor_email ? substr($log->actor_email, 0, 1) : 'S' }}
                        </div>
                        <div class="user-info">
                            <strong>{{ $log->actor_email ?? 'System' }}</strong>
                            <small>{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @php
                        $severityClass = match($log->severity) {
                            'info' => 'severity-info',
                            'warning' => 'severity-warning',
                            'error' => 'severity-error',
                            'critical' => 'severity-critical',
                            default => 'severity-default'
                        };
                    @endphp
                    <span class="{{ $severityClass }} small">
                        {{ ucfirst($log->severity) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="card-detail">
                        <span class="label">Event</span>
                        <span class="value">{{ $log->event_key }}</span>
                    </div>
                    <div class="card-detail">
                        <span class="label">Time</span>
                        <span class="value">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @if($log->description)
                        <div class="card-detail full-width">
                            <span class="label">Description</span>
                            <span class="value">{{ $log->description }}</span>
                        </div>
                    @endif
                    @if($log->meta)
                        <div class="card-detail full-width">
                            <button class="details-toggle mobile" onclick="openDetailsModal({{ $log->id }}, {{ json_encode($log->meta) }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                                View Meta
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <span class="ip-address small">{{ $log->ip_address ?? 'N/A' }}</span>
                    <a href="{{ route('audit-logs.show', $log->id) }}" class="action-link">View →</a>
                </div>
            </div>
        @empty
            <div class="empty-state">No audit logs found.</div>
        @endforelse
    </div>

    @if($logs->total() > 20)
    <div class="pagination-wrap">
        {{ $logs->links() }}
    </div>
    @endif
</div>

<style>
/* Statistics Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-total {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-icon-today {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-icon-week {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-icon-flagged {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
}

/* Audit Table Styling */
.audit-table {
    width: 100%;
    border-collapse: collapse;
}

.audit-cards {
    display: none;
}

.audit-table th,
.audit-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    vertical-align: middle;
}

.audit-table th {
    background: #f8fafc;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.audit-table td {
    font-size: 14px;
}

.audit-table small {
    display: block;
    margin-top: 3px;
}

/* Timestamp styling */
.timestamp-main {
    font-weight: 500;
    color: #111827;
}

.timestamp-relative {
    color: #6b7280;
    font-size: 12px;
}

/* User cell styling */
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.user-avatar.small {
    width: 28px;
    height: 28px;
    font-size: 12px;
}

.user-info {
    flex: 1;
}

.user-info strong {
    display: block;
    color: #111827;
}

.user-info small {
    color: #6b7280;
    font-size: 12px;
}

/* Entity cell styling */
.entity-cell strong {
    display: block;
    color: #111827;
}

.entity-cell small {
    color: #6b7280;
    font-size: 12px;
}

/* Action badges with icons */
.action-created,
.action-updated,
.action-deleted,
.action-cancelled,
.action-login,
.action-default {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.action-icon {
    display: flex;
    align-items: center;
}

.action-created {
    background: #dcfce7;
    color: #166534;
}

.action-updated {
    background: #dbeafe;
    color: #1e40af;
}

.action-deleted {
    background: #fee2e2;
    color: #991b1b;
}

.action-cancelled {
    background: #fef3c7;
    color: #92400e;
}

.action-login {
    background: #e0e7ff;
    color: #3730a3;
}

.action-default {
    background: #f3f4f6;
    color: #4b5563;
}

/* Severity badges */
.severity-info,
.severity-warning,
.severity-error,
.severity-critical,
.severity-default {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.severity-info {
    background: #dcfce7;
    color: #166534;
}

.severity-warning {
    background: #fef3c7;
    color: #92400e;
}

.severity-error {
    background: #fee2e2;
    color: #991b1b;
}

.severity-critical {
    background: #7f1d1d;
    color: white;
}

.severity-default {
    background: #f3f4f6;
    color: #4b5563;
}

.severity-info.small,
.severity-warning.small,
.severity-error.small,
.severity-critical.small,
.severity-default.small {
    padding: 4px 8px;
    font-size: 11px;
}

/* Event key styling */
.event-key {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 4px;
    margin-bottom: 4px;
}

/* Flagged badge */
.flagged-badge {
    display: inline-block;
    background: #fee2e2;
    color: #991b1b;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 4px;
}

/* Description text */
.description-text {
    font-size: 14px;
    color: #374151;
    margin-bottom: 8px;
}

/* Actor cell */
.actor-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.action-created.small,
.action-updated.small,
.action-deleted.small,
.action-cancelled.small,
.action-login.small,
.action-default.small {
    padding: 4px 8px;
    font-size: 11px;
}

/* Reason text styling */
.reason-text {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 8px 12px;
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 13px;
    color: #92400e;
}

/* Details toggle button */
.details-toggle {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
}

.details-toggle:hover {
    background: #e5e7eb;
}

.details-toggle.mobile {
    padding: 4px 8px;
    font-size: 12px;
}

/* Details Modal Specific Styles */
.details-modal-box {
    max-width: 1100px;
    background: rgba(10, 31, 51, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Enhanced Modal Styles */
.modal-header-enhanced {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-title-group {
    display: flex;
    align-items: center;
    gap: 16px;
}

.modal-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.modal-header-enhanced h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #ffffff;
}

.modal-subtitle {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
}

.modal-close-enhanced {
    border: none;
    background: rgba(255, 255, 255, 0.1);
    font-size: 16px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    padding: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.15s ease;
}

.modal-close-enhanced:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.modal-body-enhanced {
    margin-bottom: 24px;
    overflow: visible;
    /* Hide scrollbar but keep functionality */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.modal-body-enhanced::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

.quick-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.quick-info-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 12px 16px;
}

.quick-info-label {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.quick-info-value {
    font-size: 14px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    word-break: break-word;
}

.detail-section {
    margin-bottom: 20px;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.detail-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.detail-section-header svg {
    color: rgba(255, 255, 255, 0.6);
}

.detail-section-header h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
}

.copy-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.8);
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.copy-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.detail-content {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 16px;
}

.detail-content p {
    margin: 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.5;
}

.actor-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.actor-info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.actor-label {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.actor-value {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.85);
    word-break: break-word;
}

.meta-content-enhanced {
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 16px;
    max-height: 300px;
    overflow-y: auto;
    /* Hide scrollbar but keep functionality */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.meta-content-enhanced::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

.meta-content-enhanced pre {
    font-size: 12px;
    white-space: pre-wrap;
    word-break: break-all;
    margin: 0;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.85);
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.modal-footer-enhanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-footer-enhanced .secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.15s ease;
}

.modal-footer-enhanced .secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

/* Add data-log-id attribute support */
tr[data-log-id] {
    /* This allows us to find specific rows by ID */
}

.meta-content {
    margin-bottom: 16px;
}

.meta-content strong {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.meta-content pre {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    padding: 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: pre-wrap;
    word-break: break-all;
    margin: 0;
    line-height: 1.5;
    color: #374151;
}

.details-content-wrapper {
    max-height: 400px;
    overflow-y: auto;
    /* Hide scrollbar but keep functionality */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.details-content-wrapper::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

/* IP address styling */
.ip-address {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 13px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
}

.ip-address.small {
    font-size: 11px;
    padding: 1px 4px;
}

/* Action link */
.action-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.15s ease;
}

.action-link:hover {
    color: #1d4ed8;
}

/* Quick filter buttons */
.quick-filters {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}

.quick-filter-btn {
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.quick-filter-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
}

.quick-filter-btn:active {
    background: rgba(255, 255, 255, 0.3);
}

/* Modal styling */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5000;
    padding: 20px;
}

.modal-box {
    background: rgba(10, 31, 51, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    width: 100%;
    max-width: 400px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 25px 60px rgba(0,0,0,.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
}

.modal-close {
    border: none;
    background: none;
    font-size: 28px;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background 0.15s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.modal-body {
    margin-bottom: 20px;
    overflow: visible;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer button {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.modal-footer .secondary {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
}

.modal-footer .secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.modal-footer .primary {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.9);
    color: #0a1f33;
}

.modal-footer .primary:hover {
    background: #ffffff;
    border-color: #ffffff;
}

.filter-section {
    margin-bottom: 12px;
}

.filter-section:last-child {
    margin-bottom: 0;
}

.filter-section label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-section select,
.filter-section input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
}

.filter-section select:focus,
.filter-section input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.filter-section select option {
    background: #0a1f33;
    color: #ffffff;
}

/* Search bar styling */
.search {
    display: flex;
    gap: 8px;
    position: relative;
    align-items: center;
}

.search input {
    flex: 1;
    height: 42px;
    box-sizing: border-box;
    padding: 0 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    height: 38px;
    box-sizing: border-box;
}

.filter-btn:hover {
    background: #d1d5db;
    border-color: #6b7280;
    color: #111827;
}

.filter-btn:active {
    background: #0a1f33;
    border-color: #0a1f33;
    color: white;
    transform: translateY(1px);
}

.filter-btn svg {
    width: 16px;
    height: 16px;
}

/* Pagination styling */
.pagination-wrap {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.pagination-wrap nav {
    display: flex;
    justify-content: center;
}

.pagination-wrap .pagination,
.pagination-wrap nav > div {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination-wrap a,
.pagination-wrap span {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none !important;
    color: #374151;
    background: #fff;
    border: 1px solid #e5e7eb;
    transition: all 0.15s ease;
    line-height: 1;
}

.pagination-wrap a:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
}

.pagination-wrap span[aria-current="page"],
.pagination-wrap .active span,
.pagination-wrap [aria-current="page"] span {
    background: #111827 !important;
    color: #fff !important;
    border-color: #111827 !important;
    font-weight: 600;
}

.pagination-wrap span[aria-disabled="true"],
.pagination-wrap .disabled span {
    color: #9ca3af !important;
    background: #f9fafb !important;
    border-color: #e5e7eb !important;
    cursor: not-allowed;
    opacity: 0.7;
}

.pagination-wrap svg,
.pagination-wrap .pagination svg,
nav[role="navigation"] svg {
    width: 16px !important;
    height: 16px !important;
    max-width: 16px !important;
    max-height: 16px !important;
}

.pagination-wrap a[rel="prev"],
.pagination-wrap a[rel="next"] {
    font-weight: 500;
    padding: 0 14px;
}

/* Responsive pagination for mobile */
@media (max-width: 768px) {
    .pagination-wrap {
        margin-top: 20px;
        gap: 8px;
    }

    .pagination-wrap .pagination,
    .pagination-wrap nav > div {
        gap: 4px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-wrap a,
    .pagination-wrap span {
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        font-size: 13px;
        border-radius: 8px;
    }

    .pagination-wrap a[rel="prev"],
    .pagination-wrap a[rel="next"] {
        padding: 0 10px;
        font-size: 12px;
    }

    .pagination-wrap svg,
    .pagination-wrap .pagination svg,
    nav[role="navigation"] svg {
        width: 14px !important;
        height: 14px !important;
        max-width: 14px !important;
        max-height: 14px !important;
    }

    /* Hide some page numbers on very small screens */
    @media (max-width: 480px) {
        .pagination-wrap .pagination {
            gap: 2px;
        }

        .pagination-wrap a,
        .pagination-wrap span {
            min-width: 28px;
            height: 28px;
            padding: 0 6px;
            font-size: 12px;
        }

        .pagination-wrap a[rel="prev"],
        .pagination-wrap a[rel="next"] {
            padding: 0 8px;
            font-size: 11px;
        }
    }
}

.empty-state {
    text-align: center;
    padding: 40px !important;
    color: #6b7280;
}

/* Mobile Cards Styling */
.audit-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}

.audit-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f3f4f6;
}

.card-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-user .user-info strong {
    display: block;
    font-size: 14px;
    color: #111827;
}

.card-user .user-info small {
    font-size: 12px;
    color: #6b7280;
}

.card-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.card-detail {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.card-detail.full-width {
    grid-column: 1 / -1;
}

.card-detail .label {
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 600;
}

.card-detail .value {
    font-size: 13px;
    color: #1f2937;
    font-weight: 500;
}

.card-detail .value.reason {
    background: #fef3c7;
    border-left: 3px solid #f59e0b;
    padding: 8px 10px;
    border-radius: 4px;
    font-size: 12px;
    color: #92400e;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #f3f4f6;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .stat-card {
        padding: 16px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
    }

    .stat-value {
        font-size: 20px;
    }

    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head button.primary {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search {
        position: relative;
    }

    .search input {
        height: 40px;
    }

    .filter-btn {
        padding: 0 12px;
        font-size: 13px;
        height: 40px;
    }

    .modal-box {
        max-width: 90%;
        padding: 20px;
    }

    .details-modal-box {
        max-width: 98%;
    }

    .details-content-wrapper {
        max-height: 300px;
    }

    .quick-info-grid {
        grid-template-columns: 1fr;
    }

    .actor-info-grid {
        grid-template-columns: 1fr;
    }

    .modal-title-group {
        gap: 12px;
    }

    .modal-icon {
        width: 40px;
        height: 40px;
    }

    .modal-header-enhanced h2 {
        font-size: 18px;
    }

    .modal-subtitle {
        font-size: 12px;
    }

    .meta-content-enhanced {
        max-height: 200px;
    }

    .modal-footer-enhanced {
        flex-direction: column;
        gap: 8px;
    }

    .modal-footer-enhanced .secondary,
    .modal-footer-enhanced .primary {
        width: 100%;
    }

    .quick-filters {
        grid-template-columns: repeat(2, 1fr);
    }

    /* Hide desktop table */
    .audit-table {
        display: none;
    }

    /* Show mobile cards */
    .audit-cards {
        display: block;
    }

    .card-body {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .card-detail.full-width {
        grid-column: 1;
    }
}
</style>

<script>
function filterAuditLogs() {
    const query = document.getElementById('auditSearch').value.trim();
    const params = new URLSearchParams();
    if (query) params.set('q', query);
    window.location.href = '{{ route('audit-logs.index') }}?' + params.toString();
}

function openFilterModal() {
    document.getElementById('filterModal').style.display = 'flex';
}

function closeFilterModal() {
    document.getElementById('filterModal').style.display = 'none';
}

function setQuickDate(period) {
    const today = new Date();
    let fromDate = '';
    let toDate = '';

    switch(period) {
        case 'today':
            fromDate = today.toISOString().split('T')[0];
            toDate = today.toISOString().split('T')[0];
            break;
        case 'week':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            fromDate = weekStart.toISOString().split('T')[0];
            toDate = today.toISOString().split('T')[0];
            break;
        case 'month':
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            fromDate = monthStart.toISOString().split('T')[0];
            toDate = today.toISOString().split('T')[0];
            break;
        case 'clear':
            fromDate = '';
            toDate = '';
            break;
    }

    document.getElementById('fromDateFilter').value = fromDate;
    document.getElementById('toDateFilter').value = toDate;
    applyFilters();
}

function applyFilters() {
    const eventKey = document.getElementById('eventKeyFilter').value;
    const severity = document.getElementById('severityFilter').value;
    const actorType = document.getElementById('actorTypeFilter').value;
    const actorEmail = document.getElementById('actorEmailFilter').value;
    const flagged = document.getElementById('flaggedFilter').value;
    const fromDate = document.getElementById('fromDateFilter').value;
    const toDate = document.getElementById('toDateFilter').value;
    
    const params = new URLSearchParams();
    if (eventKey) params.set('event_key', eventKey);
    if (severity) params.set('severity', severity);
    if (actorType) params.set('actor_type', actorType);
    if (actorEmail) params.set('actor_email', actorEmail);
    if (flagged) params.set('is_flagged', flagged);
    if (fromDate) params.set('from_date', fromDate);
    if (toDate) params.set('to_date', toDate);
    
    window.location.href = '{{ route('audit-logs.index') }}?' + params.toString();
}

function clearFilters() {
    document.getElementById('eventKeyFilter').value = '';
    document.getElementById('severityFilter').value = '';
    document.getElementById('actorTypeFilter').value = '';
    document.getElementById('actorEmailFilter').value = '';
    document.getElementById('flaggedFilter').value = '';
    document.getElementById('fromDateFilter').value = '';
    document.getElementById('toDateFilter').value = '';
    window.location.href = '{{ route('audit-logs.index') }}';
}

function openDetailsModal(id, meta) {
    const modal = document.getElementById('detailsModal');
    const modalMeta = document.getElementById('modalMeta');

    // Find the log row to get additional data
    const logRow = document.querySelector(`tr[data-log-id="${id}"]`);
    if (!logRow) {
        // Fallback to meta-only view if row not found
        if (meta && Object.keys(meta).length > 0) {
            modalMeta.textContent = JSON.stringify(meta, null, 2);
        } else {
            modalMeta.textContent = 'No meta data available';
        }
        modal.style.display = 'flex';
        return;
    }

    // Extract data from the row
    const eventKey = logRow.querySelector('.event-key')?.textContent.trim() || '-';
    const severity = logRow.querySelector('.severity-info, .severity-warning, .severity-error, .severity-critical, .severity-default')?.textContent.trim() || '-';
    const timestamp = logRow.querySelector('.timestamp-main')?.textContent.trim() || '-';
    const ipAddress = logRow.querySelector('.ip-address')?.textContent.trim() || '-';
    const description = logRow.querySelector('.description-text')?.textContent.trim() || '-';
    const actorEmail = logRow.querySelector('.user-info strong')?.textContent.trim() || '-';
    const actorType = logRow.querySelector('.user-info small')?.textContent.trim() || '-';

    // Populate the enhanced modal fields
    document.getElementById('modalEventKey').textContent = eventKey;
    document.getElementById('modalSeverity').textContent = severity;
    document.getElementById('modalTimestamp').textContent = timestamp;
    document.getElementById('modalIpAddress').textContent = ipAddress;
    document.getElementById('modalDescription').textContent = description;
    document.getElementById('modalActorEmail').textContent = actorEmail;
    document.getElementById('modalActorType').textContent = actorType;

    // Set the meta data
    if (meta && Object.keys(meta).length > 0) {
        modalMeta.textContent = JSON.stringify(meta, null, 2);
    } else {
        modalMeta.textContent = 'No meta data available';
    }

    modal.style.display = 'flex';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

function copyMetaData() {
    const modalMeta = document.getElementById('modalMeta');
    const text = modalMeta.textContent;

    navigator.clipboard.writeText(text).then(() => {
        // Show a brief success indicator
        const copyBtn = document.querySelector('.copy-btn');
        const originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Copied!
        `;
        setTimeout(() => {
            copyBtn.innerHTML = originalText;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

function exportAuditLogs() {
    const table = document.querySelector('.audit-table');
    const rows = table.querySelectorAll('tbody tr');
    
    if (rows.length === 0 || rows[0].classList.contains('empty-state')) {
        alert('No data to export');
        return;
    }
    
    let csv = 'Timestamp,Event Key,Severity,Actor Email,Actor Type,Description,IP Address,Flagged\n';
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0 && !row.querySelector('.empty-state')) {
            const timestamp = cells[0].querySelector('.timestamp-main')?.textContent.trim() || cells[0].textContent.trim();
            const eventKey = cells[1].querySelector('.event-key')?.textContent.trim() || cells[1].textContent.trim();
            const severity = cells[2].textContent.trim();
            const actorEmail = cells[3].querySelector('strong')?.textContent.trim() || '';
            const actorType = cells[3].querySelector('small')?.textContent.trim() || '';
            const description = cells[4].querySelector('.description-text')?.textContent.trim() || cells[4].textContent.trim();
            const ip = cells[5].textContent.trim();
            const flagged = cells[1].querySelector('.flagged-badge') ? 'Yes' : 'No';
            
            csv += `"${timestamp}","${eventKey}","${severity}","${actorEmail}","${actorType}","${description}","${ip}","${flagged}"\n`;
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'audit_logs_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endsection