@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Barcode Labels</h1>
        <p>Print product labels with ID, price and name.</p>
    </div>
    <a class="primary" href="{{ route('barcode-labels.create') }}">+ Create Label</a>
</div>

<div class="search">
    <input id="labelSearch" placeholder="Search labels..." oninput="filterLabels()">
</div>

<div class="panel">
    <!-- Desktop Table -->
    <table class="inventory-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th>Label Name</th>
                <th>Price</th>
                <th>Printed</th>
                <th style="min-width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody id="labelsTable">
            @foreach($labels as $label)
            <tr data-name="{{ $label->name ?? $label->product->name }}" data-code="{{ $label->code }}">
                <td><strong>{{ $label->code }}</strong></td>
                <td>{{ $label->product->name }}</td>
                <td>{{ $label->name ?? '-' }}</td>
                <td>Rs. {{ number_format($label->price, 2) }}</td>
                <td>
                    @if($label->printed_count > 0)
                        <span class="badge success">{{ $label->printed_count }}×</span>
                    @else
                        <span class="badge gray">Never</span>
                    @endif
                </td>
                <td>
                    <button class="action-btn primary-btn" onclick="printLabel({{ $label->id }})" title="Print">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                        Print
                    </button>
                    <button class="action-btn danger-btn" onclick="deleteLabel({{ $label->id }})" title="Delete">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                        Delete
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Mobile Cards -->
    <div class="inventory-cards">
        @foreach($labels as $label)
        <div class="barcode-card" data-name="{{ $label->name ?? $label->product->name }}" data-code="{{ $label->code }}">
            <div class="barcode-card-header">
                <div class="barcode-card-code">{{ $label->code }}</div>
                <div>
                    @if($label->printed_count > 0)
                        <span class="badge success">{{ $label->printed_count }}×</span>
                    @else
                        <span class="badge gray">Never</span>
                    @endif
                </div>
            </div>
            <div class="barcode-card-details">
                <div class="barcode-card-detail">
                    <label>Product</label>
                    <span>{{ $label->product->name }}</span>
                </div>
                <div class="barcode-card-detail">
                    <label>Label Name</label>
                    <span>{{ $label->name ?? '-' }}</span>
                </div>
                <div class="barcode-card-detail">
                    <label>Price</label>
                    <span>Rs. {{ number_format($label->price, 2) }}</span>
                </div>
            </div>
            <div class="barcode-card-actions">
                <button class="action-btn primary-btn" onclick="printLabel({{ $label->id }})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                    Print
                </button>
                <button class="action-btn danger-btn" onclick="deleteLabel({{ $label->id }})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                    Delete
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div id="printModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Print Label</h2>
            <button class="modal-close" onclick="closePrintModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="form-section">
                <label>Copies</label>
                <input type="number" id="printCopies" value="1" min="1" max="200">
            </div>
        </div>
        <div class="modal-footer">
            <button class="secondary" onclick="closePrintModal()">Cancel</button>
            <button class="primary" onclick="confirmPrint()">Print</button>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Delete Label</h2>
            <button class="modal-close" onclick="closeDeleteModal()">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this label? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="secondary" onclick="closeDeleteModal()">Cancel</button>
            <button class="danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
let selectedLabelId = null;
let deleteLabelId = null;

function filterLabels() {
    const search = document.getElementById('labelSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#labelsTable tr');
    const cards = document.querySelectorAll('.barcode-card');
    
    rows.forEach(row => {
        const name = row.dataset.name.toLowerCase();
        const code = row.dataset.code.toLowerCase();
        row.style.display = (name.includes(search) || code.includes(search)) ? '' : 'none';
    });

    cards.forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const code = card.dataset.code.toLowerCase();
        card.style.display = (name.includes(search) || code.includes(search)) ? '' : 'none';
    });
}

function printLabel(id) {
    selectedLabelId = id;
    document.getElementById('printModal').style.display = 'flex';
}

function closePrintModal() {
    document.getElementById('printModal').style.display = 'none';
    selectedLabelId = null;
}

function confirmPrint() {
    const copies = document.getElementById('printCopies').value;
    const ids = selectedLabelId ? selectedLabelId : '';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('barcode-labels.print') }}';
    form.target = '_blank';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    const idsInput = document.createElement('input');
    idsInput.type = 'hidden';
    idsInput.name = 'ids';
    idsInput.value = ids;
    form.appendChild(idsInput);
    
    const copiesInput = document.createElement('input');
    copiesInput.type = 'hidden';
    copiesInput.name = 'copies';
    copiesInput.value = copies;
    form.appendChild(copiesInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    closePrintModal();
}

function deleteLabel(id) {
    deleteLabelId = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteLabelId = null;
}

function confirmDelete() {
    if (deleteLabelId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('barcode-labels.destroy', ['barcodeLabel' => ':id']) }}`.replace(':id', deleteLabelId);
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
    closeDeleteModal();
}
</script>

<style>
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.action-btn.primary-btn {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.action-btn.primary-btn:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.action-btn.danger-btn {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.action-btn.danger-btn:hover {
    background: #dc2626;
    border-color: #dc2626;
}

.action-btn svg {
    flex-shrink: 0;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-box {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    line-height: 1;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #111827;
}

.modal-body {
    padding: 24px;
}

.modal-body p {
    margin: 0;
    font-size: 14px;
    color: #374151;
    line-height: 1.5;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
}

.modal-footer button {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid;
}

.modal-footer .primary {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #111827;
}

.modal-footer .primary:hover {
    background: #d97706;
    border-color: #d97706;
}

.modal-footer .secondary {
    background: #e5e7eb;
    border-color: #d5d7db;
    color: #374151;
}

.modal-footer .secondary:hover {
    background: #d1d5db;
    border-color: #c4c9d1;
}

.modal-footer .danger {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.modal-footer .danger:hover {
    background: #dc2626;
    border-color: #dc2626;
}

.form-section {
    margin-bottom: 16px;
}

.form-section label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

.form-section input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-section input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Responsive Design */
.inventory-cards {
    display: none;
}

@media (max-width: 768px) {
    .panel {
        padding: 12px;
    }
    
    .inventory-table {
        display: none;
    }
    
    .inventory-cards {
        display: block;
    }
    
    .barcode-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }
    
    .barcode-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    
    .barcode-card-code {
        font-weight: 600;
        color: #111827;
        font-size: 14px;
    }
    
    .barcode-card-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .barcode-card-detail {
        display: flex;
        flex-direction: column;
    }
    
    .barcode-card-detail label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    
    .barcode-card-detail span {
        font-size: 13px;
        color: #374151;
    }
    
    .barcode-card-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .action-btn {
        flex: 1;
        justify-content: center;
        min-width: 80px;
    }
    
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .page-head a {
        width: 100%;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .barcode-card-details {
        grid-template-columns: 1fr;
    }
    
    .barcode-card-actions {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
    }
}
</style>
@endsection
