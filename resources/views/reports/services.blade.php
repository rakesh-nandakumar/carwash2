@extends('layouts.app')

@section('content')
<style>
@media print {
    body * { visibility: hidden; }
    #report-content, #report-content * { visibility: visible; }
    #report-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print { display: none !important; }
}
</style>

<div class="page-head no-print">
    <div>
        <h1>Services Report</h1>
        <p>Service usage statistics and revenue by service type.</p>
    </div>
    <div class="page-head-actions">
        <button onclick="window.print()" class="primary">📄 Print PDF</button>
        <button onclick="printThermal()" class="secondary" style="background:#f59e0b;color:white;">🖨️ Thermal</button>
    </div>
</div>

<div id="report-content" class="panel">
    <!-- Filters -->
    <div class="no-print filters">
        <form method="get" class="filter-form">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}">
            </div>
            <div>
                <label>End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="filter-btn">
                <button type="submit" class="primary">Generate Report</button>
            </div>
        </form>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        <h1>Services Report</h1>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <!-- Desktop Table -->
    <div class="table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Average Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($serviceStats as $serviceName => $stats)
                <tr>
                    <td>{{ $serviceName }}</td>
                    <td class="text-right">{{ $stats['count'] }}</td>
                    <td class="text-right">Rs. {{ number_format($stats['revenue'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($stats['revenue'] / max($stats['count'], 1), 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="empty">No services found for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards (2 per row) -->
    <div class="report-cards">
        @forelse($serviceStats as $serviceName => $stats)
        <div class="report-card">
            <div class="card-top">
                <strong>{{ $serviceName }}</strong>
            </div>

            <div class="card-body">
                <div class="row">
                    <span class="label">Count</span>
                    <span>{{ $stats['count'] }}</span>
                </div>
                <div class="row">
                    <span class="label">Revenue</span>
                    <span class="text-green">Rs. {{ number_format($stats['revenue'], 2) }}</span>
                </div>
                <div class="row highlight">
                    <span class="label">Avg. Price</span>
                    <span>Rs. {{ number_format($stats['revenue'] / max($stats['count'], 1), 2) }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">No services found for this period.</div>
        @endforelse
    </div>

    <div class="report-footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <!-- Back button -->
    <div class="report-back no-print">
        <a href="{{ route('reports') }}" class="back-button">← Back</a>
    </div>
</div>

<style>
.page-head-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Filters */
.filters {
    margin-bottom: 24px;
}

.filter-form {
    display: flex;
    gap: 12px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-form label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}

.filter-form input[type="date"] {
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

/* Report Title */
.report-title {
    text-align: center;
    margin-bottom: 28px;
}

.report-title h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
}

.report-title p {
    margin: 0;
    color: #6b7280;
}

/* Desktop Table */
.table-wrap {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.report-table th {
    padding: 12px;
    text-align: left;
    background: #f3f4f6;
    border-bottom: 2px solid #e5e7eb;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.report-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
    color: #374151;
}

.report-table .text-right {
    text-align: right;
}

.report-table .empty {
    text-align: center;
    color: #9ca3af;
    padding: 30px;
}

/* Mobile Cards - 2 per row */
.report-cards {
    display: none;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.report-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.report-card .card-top {
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f3f4f6;
}

.report-card .card-top strong {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    line-height: 1.3;
}

.report-card .card-body .row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    font-size: 13px;
}

.report-card .label {
    color: #6b7280;
    font-size: 12.5px;
    flex-shrink: 0;
    margin-right: 8px;
}

.report-card .card-body .row span:last-child {
    font-weight: 500;
    color: #111827;
    text-align: right;
}

.report-card .highlight {
    margin-top: 6px;
    padding-top: 8px;
    border-top: 1px solid #f3f4f6;
}

.report-card .text-green {
    color: #059669 !important;
    font-weight: 600 !important;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 30px;
    color: #9ca3af;
    font-size: 14px;
}

.report-footer {
    margin-top: 24px;
    text-align: center;
    color: #6b7280;
    font-size: 12px;
}

/* Back button */
.report-back {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    background: #e5e7eb;
    color: #374151;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s;
}

.back-button:hover {
    background: #d1d5db;
    color: #111827;
}

/* ========== MOBILE ========== */
@media (max-width: 768px) {
    .page-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .page-head-actions {
        width: 100%;
    }

    .page-head-actions a,
    .page-head-actions button {
        flex: 1;
        text-align: center;
    }

    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-form > div {
        width: 100%;
    }

    .table-wrap {
        display: none;
    }

    .report-cards {
        display: grid;
    }

    .report-back {
        justify-content: center;
    }
}
</style>

<script>
function printThermal() {
    const printWindow = window.open(
        '',
        '_blank',
        'width=420,height=800'
    );

    if (!printWindow) {
        alert('Please allow pop-ups in your browser to print the thermal report.');
        return;
    }

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Services Report</title>
            <style>
                @page {
                    size: 80mm auto;
                    margin: 0;
                }

                * {
                    box-sizing: border-box;
                }

                html,
                body {
                    width: 80mm;
                    margin: 0;
                    padding: 0;
                    background: #fff;
                }

                body {
                    font-family: Arial, Helvetica, sans-serif;
                    color: #000;
                    font-size: 12px;
                    line-height: 1.4;
                }

                .thermal {
                    width: 72mm;
                    margin: 0 auto;
                    padding: 5mm 0 8mm;
                }

                .header {
                    text-align: center;
                    margin-bottom: 4mm;
                }

                .company {
                    font-size: 20px;
                    font-weight: 800;
                }

                .title {
                    font-size: 13px;
                    font-weight: 700;
                    margin-top: 2mm;
                }

                .period {
                    font-size: 11px;
                    margin-top: 1.5mm;
                }

                .divider {
                    border-top: 1px dashed #000;
                    margin: 3mm 0;
                }

                .service {
                    padding: 2mm 0;
                    border-bottom: 1px dashed #aaa;
                }

                .service-name {
                    font-size: 12px;
                    font-weight: 700;
                    margin-bottom: 1mm;
                    word-break: break-word;
                }

                .service-row {
                    display: flex;
                    justify-content: space-between;
                    gap: 5px;
                    font-size: 11px;
                }

                .revenue {
                    font-weight: 700;
                    white-space: nowrap;
                }

                .footer {
                    text-align: center;
                    font-size: 10px;
                    margin-top: 4mm;
                }
            </style>
        </head>
        <body>
            <div class="thermal">
                <div class="header">
                    <div class="company">
                        AUTOCARE PRO
                    </div>
                    <div class="title">
                        SERVICES REPORT
                    </div>
                    <div class="period">
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                        -
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </div>
                </div>

                <div class="divider"></div>

                @foreach($serviceStats as $serviceName => $stats)
                    <div class="service">
                        <div class="service-name">
                            {{ $serviceName }}
                        </div>
                        <div class="service-row">
                            <span>
                                Qty: {{ $stats['count'] }}
                            </span>
                            <span class="revenue">
                                Rs. {{ number_format($stats['revenue'], 2) }}
                            </span>
                        </div>
                    </div>
                @endforeach

                <div class="divider"></div>

                <div class="footer">
                    AUTOCARE PRO
                    <br>
                    Generated:
                    {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </body>
        </html>
    `);

    printWindow.document.close();

    setTimeout(function () {
        printWindow.focus();
        printWindow.print();
    }, 500);
}
</script>
@endsection