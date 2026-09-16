<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode Labels</title>
    <style>
        @page {
            size: 50mm 25mm;
            margin: 0;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            color: #111827;
        }

        .toolbar {
            position: sticky;
            top: 0;
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            background: #111827;
            color: #fff;
            font-size: 13px;
        }

        .toolbar button {
            font: inherit;
            font-weight: 600;
            padding: .4rem .9rem;
            border: 0;
            border-radius: 6px;
            background: #f59e0b;
            color: #111827;
            cursor: pointer;
        }

        .toolbar .muted { opacity: .75; }

        .sheet {
            display: block;
            padding: 0;
        }

        .label {
            width: 50mm;
            height: 25mm;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            background: #fff;
            outline: 1px dashed #d1d5db;
            outline-offset: -1px;
            break-after: page;
            page-break-after: always;
        }

        .label:last-child { break-after: auto; page-break-after: auto; }

        .label .name {
            width: 100%;
            text-align: center;
            font-weight: 700;
            font-size: 7.5pt;
            line-height: 1.1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .label .price {
            font-weight: 800;
            font-size: 9pt;
            line-height: 1;
        }

        .label .code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 6pt;
            letter-spacing: .04em;
        }

        .label .barcode {
            width: 100%;
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media print {
            html, body { background: #fff; }
            .toolbar { display: none; }
            .sheet { padding: 0; }
            .label { outline: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <span class="muted">
            {{ $labels->count() }} {{ Str::plural('label', $labels->count()) }}
            &times; {{ $copies }} {{ Str::plural('copy', $copies) }}
            &mdash; 50 × 25 mm roll
        </span>
        <span class="muted">Set your printer's paper to 50 × 25 mm, and scale to 100%.</span>
    </div>

    <div class="sheet">
        @foreach ($labels as $label)
            @for ($copy = 0; $copy < $copies; $copy++)
                <div class="label">
                    <div class="name">{{ $label->name ?? $label->product->name }}</div>
                    <div class="price">Rs. {{ number_format($label->price, 2) }}</div>
                    <div class="barcode">
                        <div style="font-family: monospace; font-size: 18px; letter-spacing: 3px; font-weight: bold; text-align: center;">
                            {{ strtoupper($label->code) }}
                        </div>
                    </div>
                    <div class="code">{{ $label->code }}</div>
                </div>
            @endfor
        @endforeach
    </div>

    <script>
        window.addEventListener('load', () => requestAnimationFrame(() => window.print()));
    </script>
</body>
</html>
