<table>
    <thead>
        <tr>
            <th colspan="6">Sales ({{ $meta['period'] ?? '' }} — {{ $meta['start_date'] ?? '' }} to {{ $meta['end_date'] ?? '' }})</th>
        </tr>
        <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit price</th>
            <th>Total revenue</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
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
