@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>RGRN No: <span class="rgrn-number">{{ $returnGrn->return_grn_number }}</span></h1>
        <p>View return GRN details.</p>
    </div>
    <div class="page-actions">
        <a class="secondary" href="{{ route('return_grns.index') }}">← Back to Return GRNs</a>
    </div>
</div>

<div class="panel">
    <div class="grn-header">
        <div class="grn-info">
            <div class="info-row">
                <span class="label">Return GRN Number:</span>
                <span class="value">{{ $returnGrn->return_grn_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="status-badge status-{{ strtolower($returnGrn->status?->key ?? 'draft') }}">
                    {{ $returnGrn->status?->value ?? 'Draft' }}
                </span>
            </div>
            <div class="info-row">
                <span class="label">Supplier:</span>
                <span class="value">{{ $returnGrn->supplier ? $returnGrn->supplier->name : '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Reference:</span>
                <span class="value">{{ $returnGrn->reference ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Address:</span>
                <span class="value">{{ $returnGrn->address ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Reason:</span>
                <span class="value">{{ $returnGrn->reason ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Returned Date:</span>
                <span class="value">{{ $returnGrn->returned_at ? $returnGrn->returned_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>

        <div class="grn-actions">
            @if($returnGrn->isDraft())
                <button onclick="window.returnGrnConfirm()" class="primary">Confirm Return GRN</button>
            @endif
            @if(!$returnGrn->isDeleted())
                <button onclick="window.returnGrnDelete()" class="danger">Delete Return GRN</button>
            @endif
        </div>
    </div>

    @if($returnGrn->notes)
    <div class="form-group">
        <label>Notes</label>
        <div class="notes-text">{{ $returnGrn->notes }}</div>
    </div>
    @endif

    <div class="form-group">
        <label>Items ({{ $returnGrn->items->count() }})</label>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returnGrn->items as $item)
                    <tr>
                        <td>{{ $item->product ? $item->product->name : '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit_cost ? 'Rs. ' . number_format($item->unit_cost, 2) : '-' }}</td>
                        <td>{{ $item->unit_cost ? 'Rs. ' . number_format($item->unit_cost * $item->quantity, 2) : '-' }}</td>
                        <td>{{ $item->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No items</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total:</strong></td>
                    <td colspan="2">
                        <strong>
                            Rs. {{ number_format($returnGrn->items->sum(function($item) {
                                return ($item->unit_cost ?? 0) * $item->quantity;
                            }), 2) }}
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="modal modal-hidden">
    <div class="modal-content modal-confirm">
        <div class="modal-icon modal-icon-success">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="modal-header">
            <h2>Confirm Return GRN</h2>
            <button class="close-btn" onclick="window.returnGrnCloseConfirm()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to confirm this Return GRN?</p>
            <p class="modal-subtext">This will adjust inventory and credit supplier ledger.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary" onclick="window.returnGrnCloseConfirm()">Cancel</button>
            <button type="button" class="primary" onclick="window.returnGrnExecuteConfirm()">Confirm Return GRN</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal modal-hidden">
    <div class="modal-content modal-delete">
        <div class="modal-icon modal-icon-danger">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="modal-header">
            <h2>Delete Return GRN</h2>
            <button class="close-btn" onclick="window.returnGrnCloseDelete()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this Return GRN?</p>
            <p class="modal-subtext">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="secondary" onclick="window.returnGrnCloseDelete()">Cancel</button>
            <button type="button" class="danger" onclick="window.returnGrnExecuteDelete()">Delete Return GRN</button>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0, 0, 0, 0.7) !important;
    display: none !important;
    z-index: 999999 !important;
    backdrop-filter: blur(15px) !important;
    -webkit-backdrop-filter: blur(15px) !important;
}

.modal.modal-visible {
    display: block !important;
}

.modal-content {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    background: white !important;
    border-radius: 12px !important;
    width: 90% !important;
    max-width: 500px !important;
    max-height: 90vh !important;
    overflow-y: auto !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
    animation: modalSlideIn 0.3s ease !important;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

.modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 24px !important;
    border-bottom: 1px solid #e5e7eb !important;
}

.modal-header h2 {
    margin: 0 !important;
    font-size: 20px !important;
    font-weight: 600 !important;
    color: #111827 !important;
}

.close-btn {
    background: none !important;
    border: none !important;
    font-size: 28px !important;
    cursor: pointer !important;
    color: #6b7280 !important;
    padding: 0 !important;
    width: 32px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 6px !important;
    transition: all 0.2s !important;
}

.close-btn:hover {
    background: #f3f4f6 !important;
    color: #111827 !important;
}

.modal-footer {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 12px !important;
    padding: 20px 24px !important;
    border-top: 1px solid #e5e7eb !important;
}

.modal-footer button {
    padding: 12px 24px !important;
    border: none !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
}

.modal-footer button.secondary {
    background: #f3f4f6 !important;
    color: #374151 !important;
}

.modal-footer button.secondary:hover {
    background: #e5e7eb !important;
}

.modal-footer button.primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: white !important;
}

.modal-footer button.primary:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
}

.modal-footer button.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
}

.modal-footer button.danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
}

.modal-body {
    padding: 20px;
    text-align: center;
}

.modal-body p {
    margin: 0 0 8px 0;
    color: #374151;
    font-size: 16px;
    font-weight: 500;
}

.modal-subtext {
    color: #6b7280;
    font-size: 14px;
    margin: 0 !important;
}

.modal-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
}

.modal-icon-success {
    color: #10b981;
}

.modal-icon-danger {
    color: #ef4444;
}

.modal-confirm {
    border-top: 4px solid #10b981;
}

.modal-delete {
    border-top: 4px solid #ef4444;
}

.grn-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.grn-info {
    flex: 1;
}

.info-row {
    display: flex;
    margin-bottom: 12px;
}

.info-row .label {
    width: 140px;
    font-weight: 600;
    color: #6b7280;
}

.info-row .value {
    color: #111827;
}

.grn-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.grn-actions button {
    min-width: 160px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.grn-actions button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.grn-actions button:active {
    transform: translateY(0);
}

.grn-actions button.primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.grn-actions button.primary:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.grn-actions button.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.grn-actions button.danger:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.notes-text {
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.table tfoot {
    background: #f9fafb;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-draft,
.status-badge.status-return_draft {
    background: #f59e0b;
    color: white;
}

.status-badge.status-confirmed,
.status-badge.status-return_confirmed {
    background: #10b981;
    color: white;
}

.status-badge.status-deleted,
.status-badge.status-return_deleted {
    background: #ef4444;
    color: white;
}

.rgrn-number {
    background: #dbeafe;
    color: #1e40af;
    padding: 4px 12px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    letter-spacing: 1px;
    border: 1px solid #93c5fd;
}

.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 16px 24px;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
    z-index: 10000;
}

.toast-show {
    opacity: 1;
    transform: translateY(0);
}

.toast-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.toast-error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

@media (max-width: 768px) {
    .grn-header {
        flex-direction: column;
    }

    .grn-actions {
        margin-top: 16px;
        width: 100%;
    }

    .grn-actions button {
        width: 100%;
    }
}
</style>

<script>
window.returnGrnCurrentId = {{ $returnGrn->id }};

window.returnGrnConfirm = function() {
    document.getElementById('confirmModal').classList.remove('modal-hidden');
    document.getElementById('confirmModal').classList.add('modal-visible');
};

window.returnGrnCloseConfirm = function() {
    document.getElementById('confirmModal').classList.remove('modal-visible');
    document.getElementById('confirmModal').classList.add('modal-hidden');
};

window.returnGrnExecuteConfirm = function() {
    const tenant = window.location.pathname.split('/')[1];
    const confirmUrl = window.location.origin + '/' + tenant + '/api/return-grns/' + window.returnGrnCurrentId + '/confirm';
    fetch(confirmUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        window.returnGrnCloseConfirm();
        if (data.success) {
            window.returnGrnShowToast('Return GRN confirmed successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            window.returnGrnShowToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        window.returnGrnCloseConfirm();
        window.returnGrnShowToast('Error: ' + error.message, 'error');
    });
};

window.returnGrnDelete = function() {
    document.getElementById('deleteModal').classList.remove('modal-hidden');
    document.getElementById('deleteModal').classList.add('modal-visible');
};

window.returnGrnCloseDelete = function() {
    document.getElementById('deleteModal').classList.remove('modal-visible');
    document.getElementById('deleteModal').classList.add('modal-hidden');
};

window.returnGrnExecuteDelete = function() {
    const tenant = window.location.pathname.split('/')[1];
    const deleteUrl = window.location.origin + '/' + tenant + '/api/return-grns/' + window.returnGrnCurrentId;
    fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        window.returnGrnCloseDelete();
        if (data.success) {
            window.returnGrnShowToast('Return GRN deleted successfully!', 'success');
            setTimeout(() => window.location.href = `/${tenant}/return-grns`, 1500);
        } else {
            window.returnGrnShowToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        window.returnGrnCloseDelete();
        window.returnGrnShowToast('Error: ' + error.message, 'error');
    });
};

window.returnGrnShowToast = function(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-show');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('toast-show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};
</script>
@endsection
