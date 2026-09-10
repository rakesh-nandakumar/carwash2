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
        <div class="stat-icon stat-icon-users">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Active Users</div>
            <div class="stat-value">{{ number_format($uniqueUsers) }}</div>
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
                <label>Action</label>
                <select id="actionFilter" onchange="applyFilters()">
                    <option value="">All Actions</option>
                    @foreach($actionTypes as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-section">
                <label>Entity Type</label>
                <select id="entityTypeFilter" onchange="applyFilters()">
                    <option value="">All Entities</option>
                    @foreach($entityTypes as $entityType)
                        <option value="{{ $entityType }}" {{ request('entity_type') == $entityType ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $entityType)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-section">
                <label>User</label>
                <select id="userFilter" onchange="applyFilters()">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
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
        <div class="modal-header">
            <h2>Audit Log Details</h2>
            <button class="modal-close" onclick="closeDetailsModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="details-reason" style="display:none;">
                <strong>Reason:</strong>
                <span id="modalReason"></span>
            </div>
            <div class="details-content-wrapper">
                <div class="old-value" style="display:none;">
                    <strong>Old Value:</strong>
                    <pre id="modalOldValue"></pre>
                </div>
                <div class="new-value" style="display:none;">
                    <strong>New Value:</strong>
                    <pre id="modalNewValue"></pre>
                </div>
            </div>
        </div>
        <div class="modal-footer">
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
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Details</th>
                    <th>IP Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="timestamp-main">{{ $log->created_at->format('Y-m-d H:i') }}</div>
                            <small class="timestamp-relative">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">
                                    {{ $log->user ? substr($log->user->name, 0, 1) : 'S' }}
                                </div>
                                <div class="user-info">
                                    <strong>{{ $log->user->name ?? 'System' }}</strong>
                                    @if($log->user)
                                        <small class="muted">{{ $log->user->email }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $actionClass = match($log->action) {
                                    'created', 'added' => 'action-created',
                                    'updated', 'modified' => 'action-updated',
                                    'deleted', 'removed' => 'action-deleted',
                                    'cancelled' => 'action-cancelled',
                                    'login', 'logout' => 'action-login',
                                    default => 'action-default'
                                };
                            @endphp
                            <span class="{{ $actionClass }}">
                                @if($log->action === 'created' || $log->action === 'added')
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                @elseif($log->action === 'updated' || $log->action === 'modified')
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                @elseif($log->action === 'deleted' || $log->action === 'removed')
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                @elseif($log->action === 'cancelled')
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                @elseif($log->action === 'login')
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                @elseif($log->action === 'logout')
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                @else
                                    <svg class="action-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                @endif
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td>
                            <div class="entity-cell">
                                <strong>{{ $log->entity_type ?? 'N/A' }}</strong>
                                @if($log->entity_id)
                                    <small class="muted">#{{ $log->entity_id }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($log->reason)
                                <div class="reason-text">{{ $log->reason }}</div>
                            @endif
                            @if($log->old_value || $log->new_value)
                                <button class="details-toggle" onclick="openDetailsModal({{ $log->id }}, {{ $log->old_value ? json_encode($log->old_value) : 'null' }}, {{ $log->new_value ? json_encode($log->new_value) : 'null' }}, {{ $log->reason ? "'" . addslashes($log->reason) . "'" : 'null' }})">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                    View Details
                                </button>
                            @endif
                        </td>
                        <td>
                            <span class="ip-address">{{ $log->ip ?? 'N/A' }}</span>
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
                            {{ $log->user ? substr($log->user->name, 0, 1) : 'S' }}
                        </div>
                        <div class="user-info">
                            <strong>{{ $log->user->name ?? 'System' }}</strong>
                            <small>{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @php
                        $actionClass = match($log->action) {
                            'created', 'added' => 'action-created',
                            'updated', 'modified' => 'action-updated',
                            'deleted', 'removed' => 'action-deleted',
                            'cancelled' => 'action-cancelled',
                            'login', 'logout' => 'action-login',
                            default => 'action-default'
                        };
                    @endphp
                    <span class="{{ $actionClass }} small">
                        @if($log->action === 'created' || $log->action === 'added')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        @elseif($log->action === 'updated' || $log->action === 'modified')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        @elseif($log->action === 'deleted' || $log->action === 'removed')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        @elseif($log->action === 'cancelled')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        @elseif($log->action === 'login')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        @elseif($log->action === 'logout')
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        @else
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        @endif
                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="card-detail">
                        <span class="label">Entity</span>
                        <span class="value">{{ $log->entity_type ?? 'N/A' }} @if($log->entity_id) #{{ $log->entity_id }} @endif</span>
                    </div>
                    <div class="card-detail">
                        <span class="label">Time</span>
                        <span class="value">{{ $log->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @if($log->reason)
                        <div class="card-detail full-width">
                            <span class="label">Reason</span>
                            <span class="value reason">{{ $log->reason }}</span>
                        </div>
                    @endif
                    @if($log->old_value || $log->new_value)
                        <div class="card-detail full-width">
                            <button class="details-toggle mobile" onclick="openDetailsModal({{ $log->id }}, {{ $log->old_value ? json_encode($log->old_value) : 'null' }}, {{ $log->new_value ? json_encode($log->new_value) : 'null' }}, {{ $log->reason ? "'" . addslashes($log->reason) . "'" : 'null' }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                                View Details
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <span class="ip-address small">{{ $log->ip ?? 'N/A' }}</span>
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

.stat-icon-users {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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
    max-width: 700px;
    background: white;
}

.details-reason {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 14px;
    color: #92400e;
}

.details-reason strong {
    display: block;
    margin-bottom: 4px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.details-content-wrapper {
    max-height: 400px;
    overflow-y: auto;
}

.old-value,
.new-value {
    margin-bottom: 16px;
}

.old-value:last-child,
.new-value:last-child {
    margin-bottom: 0;
}

.old-value strong,
.new-value strong {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.old-value strong {
    color: #991b1b;
}

.new-value strong {
    color: #166534;
}

.old-value pre,
.new-value pre {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    padding: 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: pre-wrap;
    word-break: break-all;
    margin: 0;
    line-height: 1.5;
}

.old-value pre {
    background: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
}

.new-value pre {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
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
        max-width: 90%;
    }

    .details-content-wrapper {
        max-height: 300px;
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
    const action = document.getElementById('actionFilter').value;
    const entityType = document.getElementById('entityTypeFilter').value;
    const user = document.getElementById('userFilter').value;
    const fromDate = document.getElementById('fromDateFilter').value;
    const toDate = document.getElementById('toDateFilter').value;
    
    const params = new URLSearchParams();
    if (action) params.set('action', action);
    if (entityType) params.set('entity_type', entityType);
    if (user) params.set('user_id', user);
    if (fromDate) params.set('from_date', fromDate);
    if (toDate) params.set('to_date', toDate);
    
    window.location.href = '{{ route('audit-logs.index') }}?' + params.toString();
}

function clearFilters() {
    document.getElementById('actionFilter').value = '';
    document.getElementById('entityTypeFilter').value = '';
    document.getElementById('userFilter').value = '';
    document.getElementById('fromDateFilter').value = '';
    document.getElementById('toDateFilter').value = '';
    window.location.href = '{{ route('audit-logs.index') }}';
}

function openDetailsModal(id, oldValue, newValue, reason) {
    const modal = document.getElementById('detailsModal');
    const modalOldValue = document.getElementById('modalOldValue');
    const modalNewValue = document.getElementById('modalNewValue');
    const modalReason = document.getElementById('modalReason');
    const detailsReason = document.querySelector('.details-reason');

    // Set the content
    if (oldValue && Object.keys(oldValue).length > 0) {
        modalOldValue.textContent = JSON.stringify(oldValue, null, 2);
        modalOldValue.parentElement.style.display = 'block';
    } else {
        modalOldValue.parentElement.style.display = 'none';
    }

    if (newValue && Object.keys(newValue).length > 0) {
        modalNewValue.textContent = JSON.stringify(newValue, null, 2);
        modalNewValue.parentElement.style.display = 'block';
    } else {
        modalNewValue.parentElement.style.display = 'none';
    }

    if (reason && reason.trim() !== '') {
        modalReason.textContent = reason;
        detailsReason.style.display = 'block';
    } else {
        detailsReason.style.display = 'none';
    }

    modal.style.display = 'flex';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

function exportAuditLogs() {
    const table = document.querySelector('.audit-table');
    const rows = table.querySelectorAll('tbody tr');
    
    if (rows.length === 0 || rows[0].classList.contains('empty-state')) {
        alert('No data to export');
        return;
    }
    
    let csv = 'Timestamp,User Email,User Name,Action,Entity Type,Entity ID,Reason,IP Address\n';
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0 && !row.querySelector('.empty-state')) {
            const timestamp = cells[0].querySelector('.timestamp-main')?.textContent.trim() || cells[0].textContent.trim();
            const userName = cells[1].querySelector('strong')?.textContent.trim() || '';
            const userEmail = cells[1].querySelector('small')?.textContent.trim() || '';
            const action = cells[2].textContent.trim();
            const entityType = cells[3].querySelector('strong')?.textContent.trim() || cells[3].textContent.trim();
            const entityId = cells[3].querySelector('small')?.textContent.replace('#', '').trim() || '';
            const reason = cells[4].querySelector('.reason-text')?.textContent.trim() || '';
            const ip = cells[5].textContent.trim();
            
            csv += `"${timestamp}","${userEmail}","${userName}","${action}","${entityType}","${entityId}","${reason}","${ip}"\n`;
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