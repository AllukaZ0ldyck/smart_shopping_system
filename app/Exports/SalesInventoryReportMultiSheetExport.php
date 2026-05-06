<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesInventoryReportMultiSheetExport implements WithMultipleSheets
{
    /**
     * @param  array<int, array<string, mixed>>  $salesRows
     * @param  array<int, array<string, mixed>>  $inventoryRows
     */
    public function __construct(
        private array $salesRows,
        private array $inventoryRows,
        private array $meta
    ) {
    }

    public function sheets(): array
    {
        return [
            new SalesInventorySalesSheetExport($this->salesRows, 'Sales', $this->meta),
            new SalesInventoryStockSheetExport($this->inventoryRows, 'Inventory', $this->meta),
        ];
    }
}
