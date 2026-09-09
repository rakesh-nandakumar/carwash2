@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <h1>Stock Adjustments</h1>
        <p>Correct physical stock quantities and maintain a complete adjustment history.</p>
    </div>
    @if(auth()->user()->hasPermissionTo('stock_adjustments.create'))
        <button
            type="button"
            class="primary"
            onclick="openAdjustmentModal()"
        >
            + New Adjustment
        </button>
    @endif
</div>

<div class="search">
    <input id="adjustmentSearch" placeholder="Search adjustments..." oninput="filterAdjustments()">
</div>
<div class="panel">
    <div class="table-wrap">
        <table class="adjustment-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Before</th>
                    <th>Change</th>
                    <th>After</th>
                    <th>Reason</th>
                    <th>Adjusted By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adjustment)
                    <tr>
                        <td>
                            {{ $adjustment->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            <strong>
                                {{ $adjustment->product->name }}
                            </strong>
                            <small class="muted">
                                {{ $adjustment->product->sku }}
                            </small>
                        </td>
                        <td>
                            {{ number_format((float)$adjustment->before_quantity, 3) }}
                        </td>
                        <td>
                            @if((float)$adjustment->difference > 0)
                                <span class="qty-positive">
                                    +{{ number_format((float)$adjustment->difference, 3) }}
                                </span>
                            @else
                                <span class="qty-negative">
                                    {{ number_format((float)$adjustment->difference, 3) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong>
                                {{ number_format((float)$adjustment->new_quantity, 3) }}
                            </strong>
                        </td>
                        <td>
                            {{ ucwords(str_replace('_', ' ', $adjustment->reason)) }}
                        </td>
                        <td>
                            {{ $adjustment->adjustedBy->name ?? '-' }}
                        </td>
                        <td>
                            @if($adjustment->reversed_at)
                                <span class="status-reversed">
                                    Reversed
                                </span>
                            @else
                                <span class="status-active">
                                    Active
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(
                                !$adjustment->reversed_at &&
                                auth()->user()->hasPermission('reverse_stock_adjustments')
                            )
                                <button
                                    type="button"
                                    class="danger-outline"
                                    onclick="openReverseModal(
                                        '{{ $adjustment->id }}',
                                        '{{ addslashes($adjustment->product->name) }}',
                                        '{{ $adjustment->before_quantity }}',
                                        '{{ $adjustment->new_quantity }}',
                                        '{{ $adjustment->difference }}'
                                    )"
                                >
                                    Reverse
                                </button>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            No stock adjustments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        {{ $adjustments->links() }}
    </div>
</div>
{{-- CREATE ADJUSTMENT MODAL --}}
<div
    id="adjustmentModal"
    class="modal-overlay"
    style="display:none;"
>
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2>New Stock Adjustment</h2>
                <p>Set the actual physical stock quantity.</p>
            </div>
            <button
                type="button"
                class="modal-close"
                onclick="closeAdjustmentModal()"
            >
                ×
            </button>
        </div>
        <form
            method="POST"
            action="{{ route('stock-adjustments.store') }}"
        >
            @csrf
            <div class="form-grid">
                <label>
                    Product
                    <select
                        name="product_id"
                        id="product_id"
                        required
                        onchange="loadCurrentStock()"
                    >
                        <option value="">Select product</option>
                        @foreach($products as $product)
                            <option
                                value="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                            >
                                {{ $product->name }}
                                @if($product->sku)
                                    — {{ $product->sku }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Current Stock
                    <input
                        type="text"
                        id="current_stock_display"
                        value="0.000"
                        readonly
                    >
                </label>
                <label>
                    New Stock
                    <input
                        type="number"
                        name="new_quantity"
                        id="new_quantity"
                        step="0.001"
                        min="0"
                        required
                        oninput="calculateDifference()"
                    >
                </label>
                <label>
                    Difference
                    <input
                        type="text"
                        id="difference_display"
                        value="0.000"
                        readonly
                    >
                </label>
                <label>
                    Reason
                    <select
                        name="reason"
                        required
                    >
                        <option value="">
                            Select reason
                        </option>
                        @foreach($reasons as $reason)
                            <option value="{{ $reason->value }}">
                                {{ $reason->label() }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="wide">
                    Notes
                    <textarea
                        name="notes"
                        rows="4"
                        placeholder="Explain why the physical stock differs..."
                    ></textarea>
                </label>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="secondary"
                    onclick="closeAdjustmentModal()"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="primary"
                >
                    Save Adjustment
                </button>
            </div>
        </form>
    </div>
</div>
{{-- REVERSE MODAL --}}
<div
    id="reverseModal"
    class="modal-overlay"
    style="display:none;"
>
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2>Reverse Stock Adjustment</h2>
                <p>This will create an opposite stock movement.</p>
            </div>
            <button
                type="button"
                class="modal-close"
                onclick="closeReverseModal()"
            >
                ×
            </button>
        </div>
        <div class="reverse-summary">
            <strong id="reverseProduct"></strong>
            <div class="reverse-row">
                <span>Original stock</span>
                <strong id="reverseBefore"></strong>
            </div>
            <div class="reverse-row">
                <span>Adjusted stock</span>
                <strong id="reverseAfter"></strong>
            </div>
            <div class="reverse-row">
                <span>Original change</span>
                <strong id="reverseDifference"></strong>
            </div>
        </div>
        <form
            id="reverseForm"
            method="POST"
        >
            @csrf
            <label>
                Reversal Reason
                <textarea
                    name="reversal_reason"
                    rows="4"
                    required
                    placeholder="Why is this adjustment being reversed?"
                ></textarea>
            </label>
            <div class="modal-footer">
                <button
                    type="button"
                    class="secondary"
                    onclick="closeReverseModal()"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="danger"
                >
                    Reverse Adjustment
                </button>
            </div>
        </form>
    </div>
</div>
<style>
.adjustment-table {
    width: 100%;
    border-collapse: collapse;
}
.adjustment-table th,
.adjustment-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    vertical-align: middle;
}
.adjustment-table th {
    background: #f8fafc;
    font-size: 13px;
    font-weight: 600;
}
.adjustment-table td {
    font-size: 14px;
}
.adjustment-table small {
    display: block;
    margin-top: 3px;
}
.qty-positive {
    color: #15803d;
    font-weight: 700;
}
.qty-negative {
    color: #dc2626;
    font-weight: 700;
}
.status-active,
.status-reversed {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}
.status-active {
    background: #dcfce7;
    color: #166534;
}
.status-reversed {
    background: #fef3c7;
    color: #92400e;
}
.danger-outline {
    border: 1px solid #dc2626;
    background: white;
    color: #dc2626;
    padding: 7px 12px;
    border-radius: 6px;
    cursor: pointer;
}
.danger-outline:hover {
    background: #fef2f2;
}
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
    background: white;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 25px 60px rgba(0,0,0,.2);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
}
.modal-header h2 {
    margin: 0;
    font-size: 20px;
}
.modal-header p {
    margin: 5px 0 0;
    color: #6b7280;
    font-size: 14px;
}
.modal-close {
    border: none;
    background: none;
    font-size: 28px;
    cursor: pointer;
    color: #6b7280;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}
.reverse-summary {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    padding: 16px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.reverse-summary > strong {
    display: block;
    margin-bottom: 12px;
    font-size: 16px;
}
.reverse-row {
    display: flex;
    justify-content: space-between;
    padding: 7px 0;
    border-top: 1px solid #e5e7eb;
}
.empty-state {
    text-align: center;
    padding: 40px !important;
    color: #6b7280;
}

/* Search bar styling */
.search {
    display: flex;
    gap: 8px;
}

.search input {
    flex: 1;
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

@media (max-width: 900px) {
    .table-wrap {
        overflow-x: auto;
    }
    .adjustment-table {
        min-width: 900px;
    }
}

@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head button.primary {
        width: 100%;
        text-align: center;
    }

    .search {
        display: flex;
        gap: 8px;
    }

    .search input {
        flex: 1;
    }
}
</style>
<script>
function openAdjustmentModal() {
    document.getElementById('adjustmentModal').style.display = 'flex';
    document.getElementById('current_stock_display').value = '0.000';
    document.getElementById('difference_display').value = '0.000';
    document.getElementById('new_quantity').value = '';
    loadCurrentStock();
}
function closeAdjustmentModal() {
    document.getElementById('adjustmentModal').style.display = 'none';
}
function calculateDifference() {
    const current = parseFloat(
        document.getElementById('current_stock_display').value
    ) || 0;
    const newQuantity = parseFloat(
        document.getElementById('new_quantity').value
    );
    if (Number.isNaN(newQuantity)) {
        document.getElementById('difference_display').value = '0.000';
        return;
    }
    const difference = newQuantity - current;
    document.getElementById('difference_display').value =
        difference.toFixed(3);
}
async function loadCurrentStock() {
    const productSelect =
        document.getElementById('product_id');
    const currentStock =
        document.getElementById('current_stock_display');
    const difference =
        document.getElementById('difference_display');

    if (!productSelect || !currentStock || !difference) {
        return;
    }

    const productId = productSelect.value;

    if (!productId) {
        currentStock.value = '0.000';
        difference.value = '0.000';
        return;
    }

    const branchId = '{{ auth()->user()->branch_id }}';

    currentStock.value = 'Loading...';

    try {
        /*
         * IMPORTANT:
         * Generate the URL using Laravel's named route.
         * This automatically includes:
         * /autocare-pro-service-center/
         */
        const stockUrl =
            `{{ route('inventory.stock', ['product' => '__PRODUCT__']) }}`
                .replace('__PRODUCT__', productId);

        console.log('Loading stock:', {
            productId: productId,
            branchId: branchId,
            url: stockUrl
        });

        const response = await fetch(
            `${stockUrl}${branchId ? '?branch_id=' + encodeURIComponent(branchId) : ''}`,
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }
        );

        if (!response.ok) {
            const errorText =
                await response.text();

            console.error(
                'Stock API error:',
                response.status,
                errorText
            );

            throw new Error(
                `Stock request failed (${response.status})`
            );
        }

        const data =
            await response.json();

        console.log('Stock API response:', data);

        const quantity =
            Number(data.current_stock);

        if (Number.isNaN(quantity)) {
            throw new Error(
                'Invalid stock quantity returned by server.'
            );
        }

        currentStock.value =
            quantity.toFixed(3);

        calculateDifference();
    } catch (error) {
        console.error(
            'Failed to load current stock:',
            error
        );

        currentStock.value = 'Error';
        difference.value = '0.000';
    }
}
function openReverseModal(
    id,
    product,
    before,
    after,
    difference
) {
    document.getElementById('reverseProduct').textContent = product;
    document.getElementById('reverseBefore').textContent =
        Number(before).toFixed(3);
    document.getElementById('reverseAfter').textContent =
        Number(after).toFixed(3);
    document.getElementById('reverseDifference').textContent =
        Number(difference).toFixed(3);
    document.getElementById('reverseForm').action =
        '{{ url('/stock-adjustments') }}/' +
        id +
        '/reverse';
    document.getElementById('reverseModal').style.display = 'flex';
}
function closeReverseModal() {
    document.getElementById('reverseModal').style.display = 'none';
}

// Filter stock adjustments by search
function filterAdjustments() {
    const query = document.getElementById('adjustmentSearch').value.toLowerCase().trim();
    const tableRows = document.querySelectorAll('.adjustment-table tbody tr');

    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>
@endsection