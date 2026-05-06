<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales &amp; Inventory Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        h2 { font-size: 13px; margin-top: 16px; margin-bottom: 6px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Sales &amp; inventory report</h1>
    <div class="meta">
        Period: {{ $meta['period'] ?? '' }} |
        Range: {{ $meta['start_date'] ?? '' }} — {{ $meta['end_date'] ?? '' }}
        @if(!empty($meta['warehouse_label']))
            | Warehouse: {{ $meta['warehouse_label'] }}
        @endif
    </div>

    <h2>Warehouse sale lines</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit price</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salesLines as $row)
                <tr>
                    <td>{{ $row['date'] ?? '' }}</td>
                    <td>{{ $row['customer_name'] ?? '' }}</td>
                    <td>{{ $row['product_name'] ?? '' }}</td>
                    <td>{{ $row['quantity'] ?? '' }}</td>
                    <td>{{ isset($row['unit_price']) ? number_format((float) $row['unit_price'], 2, '.', '') : '' }}</td>
                    <td>{{ isset($row['total_revenue']) ? number_format((float) $row['total_revenue'], 2, '.', '') : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Warehouse inventory</h2>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Product</th>
                <th>Warehouse</th>
                <th>Stock</th>
                <th>Unit cost</th>
                <th>Total value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventory as $row)
                <tr>
                    <td>{{ $row['code'] ?? '' }}</td>
                    <td>{{ $row['product'] ?? '' }}</td>
                    <td>{{ $row['warehouse'] ?? '' }}</td>
                    <td>{{ $row['current_stock'] ?? '' }}</td>
                    <td>{{ isset($row['unit_cost']) ? number_format((float) $row['unit_cost'], 2, '.', '') : '' }}</td>
                    <td>{{ isset($row['total_value']) ? number_format((float) $row['total_value'], 2, '.', '') : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
