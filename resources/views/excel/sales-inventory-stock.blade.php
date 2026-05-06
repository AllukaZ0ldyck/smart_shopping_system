<table>
    <thead>
        <tr>
            <th colspan="6">Inventory snapshot</th>
        </tr>
        <tr>
            <th>Code</th>
            <th>Product</th>
            <th>Warehouse</th>
            <th>Current stock</th>
            <th>Unit cost</th>
            <th>Total value</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
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
