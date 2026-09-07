@extends('layouts.app')
@section('content')
<div class="shift-container">
    <div class="shift-header">
        <h1>Shift Details #{{ $closure->id }}</h1>
        <p>Detailed breakdown of till closure</p>
    </div>

    <div class="shift-card">
        <div class="shift-info">
            <h2>{{ $till->name }}</h2>
            <p>Till Closure Record</p>
        </div>

        <div class="shift-timeline">
            <div class="timeline-item">
                <div class="timeline-label">Opened</div>
                <div class="timeline-value">{{ $closure->opened_at->format('l, F j, Y \a\t g:i A') }}</div>
            </div>

            @if($closure->closed_at)
                <div class="timeline-item">
                    <div class="timeline-label">Closed</div>
                    <div class="timeline-value">{{ $closure->closed_at->format('l, F j, Y \a\t g:i A') }}</div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-label">Duration</div>
                    <div class="timeline-value">{{ $closure->opened_at->diffForHumans($closure->closed_at, true) }}</div>
                </div>
            @endif

            <div class="timeline-item">
                <div class="timeline-label">Closed By</div>
                <div class="timeline-value">{{ $closure->user ? $closure->user->name : 'Unknown' }}</div>
            </div>
        </div>

        <div class="balance-details">
            <h3>Balance Summary</h3>

            <div class="balance-grid">
                <div class="balance-row">
                    <span>Opening Balance</span>
                    <strong>Rs. {{ number_format($closure->opening_balance, 2) }}</strong>
                </div>

                <div class="balance-row positive">
                    <span>Cash Sales</span>
                    <strong>+Rs. {{ number_format($closure->cash_sales, 2) }}</strong>
                </div>

                <div class="balance-row positive">
                    <span>Card Sales</span>
                    <strong>+Rs. {{ number_format($closure->card_sales, 2) }}</strong>
                </div>

                <div class="balance-row positive">
                    <span>UPI Sales</span>
                    <strong>+Rs. {{ number_format($closure->mobile_money_sales, 2) }}</strong>
                </div>

                <div class="balance-row positive">
                    <span>Bank Transfer Sales</span>
                    <strong>+Rs. {{ number_format($closure->bank_transfer_sales, 2) }}</strong>
                </div>

                <div class="balance-row positive">
                    <span>Other Payment Sales</span>
                    <strong>+Rs. {{ number_format($closure->other_payment_sales, 2) }}</strong>
                </div>

                <div class="balance-row total">
                    <span>Total Sales</span>
                    <strong>Rs. {{ number_format($closure->total_sales, 2) }}</strong>
                </div>

                <div class="balance-row positive">
                    <span>Cash In</span>
                    <strong>+Rs. {{ number_format($closure->cash_in, 2) }}</strong>
                </div>

                <div class="balance-row negative">
                    <span>Cash Out</span>
                    <strong>-Rs. {{ number_format($closure->cash_out, 2) }}</strong>
                </div>

                <div class="balance-row negative">
                    <span>Cash Refunds</span>
                    <strong>-Rs. {{ number_format($closure->cash_refunds, 2) }}</strong>
                </div>

                <div class="balance-row negative">
                    <span>Cash Drops</span>
                    <strong>-Rs. {{ number_format($closure->cash_drops, 2) }}</strong>
                </div>

                <div class="balance-row total">
                    <span>Expected Balance</span>
                    <strong>Rs. {{ number_format($closure->expected_balance, 2) }}</strong>
                </div>

                <div class="balance-row {{ $closure->discrepancy == 0 ? 'match' : ($closure->discrepancy > 0 ? 'overage' : 'shortage') }}">
                    <span>Counted Balance</span>
                    <strong>Rs. {{ number_format($closure->counted_balance, 2) }}</strong>
                </div>

                <div class="balance-row {{ $closure->discrepancy == 0 ? 'match' : ($closure->discrepancy > 0 ? 'overage' : 'shortage') }}">
                    <span>Discrepancy</span>
                    <strong>
                        @if($closure->discrepancy == 0)
                            None
                        @elseif($closure->discrepancy > 0)
                            +Rs. {{ number_format($closure->discrepancy, 2) }} (Overage)
                        @else
                            -Rs. {{ number_format(abs($closure->discrepancy), 2) }} (Shortage)
                        @endif
                    </strong>
                </div>
            </div>
        </div>

        @if($closure->denomination_breakdown)
            <div class="denomination-breakdown">
                <h3>Denomination Breakdown</h3>
                <div class="denomination-grid">
                    @foreach($closure->denomination_breakdown as $denomination => $count)
                        @if($count > 0)
                            <div class="denomination-item">
                                <span class="denom-label">{{ $denomination === 'coins' ? 'Coins' : 'Rs. ' . $denomination }}</span>
                                <span class="denom-count">x {{ $count }}</span>
                                <span class="denom-total">
                                    @if($denomination === 'coins')
                                        Rs. {{ number_format($count, 2) }}
                                    @else
                                        Rs. {{ number_format($denomination * $count, 2) }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if($closure->notes)
            <div class="notes-section">
                <h3>Notes</h3>
                <p>{{ $closure->notes }}</p>
            </div>
        @endif

        <div class="back-link">
            <a href="{{ route('cashier.shift-history') }}" class="btn-secondary">
                Back to History
            </a>
        </div>
    </div>
</div>

<style>
.shift-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

.shift-header {
    margin-bottom: 24px;
}

.shift-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.shift-header p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.shift-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 32px;
}

.shift-info {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.shift-info h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.shift-info p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.shift-timeline {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.timeline-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.timeline-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.timeline-value {
    font-size: 14px;
    color: #1e293b;
    font-weight: 600;
}

.balance-details {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.balance-details h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 16px 0;
}

.balance-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.balance-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 8px;
}

.balance-row span {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.balance-row strong {
    font-size: 15px;
    color: #1e293b;
    font-weight: 700;
}

.balance-row.positive strong {
    color: #10b981;
}

.balance-row.negative strong {
    color: #ef4444;
}

.balance-row.total {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
}

.balance-row.total strong {
    color: #15803d;
    font-size: 17px;
}

.balance-row.match {
    background: #f0fdf4;
}

.balance-row.match strong {
    color: #15803d;
}

.balance-row.overage {
    background: #fef3c7;
}

.balance-row.overage strong {
    color: #d97706;
}

.balance-row.shortage {
    background: #fee2e2;
}

.balance-row.shortage strong {
    color: #dc2626;
}

.denomination-breakdown {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.denomination-breakdown h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 16px 0;
}

.denomination-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
}

.denomination-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
}

.denom-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.denom-count {
    font-size: 14px;
    color: #1e293b;
    font-weight: 600;
}

.denom-total {
    font-size: 13px;
    color: #10b981;
    font-weight: 700;
}

.notes-section {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.notes-section h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 12px 0;
}

.notes-section p {
    font-size: 14px;
    color: #475569;
    line-height: 1.6;
    margin: 0;
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
}

.back-link {
    display: flex;
    justify-content: flex-end;
}

.btn-secondary {
    padding: 10px 20px;
    background: white;
    color: #64748b;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    text-decoration: none;
    display: inline-block;
}

.btn-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
</style>
@endsection