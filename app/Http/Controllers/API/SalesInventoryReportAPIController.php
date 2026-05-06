<?php

namespace App\Http\Controllers\API;

use App\Exports\SalesInventoryReportMultiSheetExport;
use App\Http\Controllers\AppBaseController;
use App\Models\Warehouse;
use App\Services\Reports\SalesInventoryReportService;
use Barryvdh\DomPDF\Facade\Pdf as CPDF;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SalesInventoryReportAPIController extends AppBaseController
{
    public function index(Request $request): JsonResponse
    {
        $period = (string) $request->get('period', 'daily');
        $anchor = $request->get('anchor_date');
        $warehouseId = $this->nullableWarehouseId($request->get('warehouse_id'));

        [$start, $end] = SalesInventoryReportService::resolveDateRange($period, $anchor ? (string) $anchor : null);

        $salesCollection = SalesInventoryReportService::salesLines($start, $end, $warehouseId);
        $salesLines = $salesCollection->map(fn ($r) => [
            'date' => $r->date,
            'customer_name' => $r->customer_name ?? '',
            'product_name' => $r->product_name ?? '',
            'quantity' => (float) $r->quantity,
            'unit_price' => (float) $r->unit_price,
            'total_revenue' => (float) $r->total_revenue,
            'reference_code' => $r->reference_code ?? '',
        ])->all();

        $inventory = SalesInventoryReportService::inventorySnapshot($warehouseId);

        return $this->sendResponse([
            'period' => $period,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'warehouse_id' => $warehouseId,
            'sales_lines' => $salesLines,
            'inventory' => $inventory,
        ], 'Sales and inventory report retrieved successfully.');
    }

    public function excel(Request $request): JsonResponse
    {
        $period = (string) $request->get('period', 'daily');
        $anchor = $request->get('anchor_date');
        $warehouseId = $this->nullableWarehouseId($request->get('warehouse_id'));

        [$start, $end] = SalesInventoryReportService::resolveDateRange($period, $anchor ? (string) $anchor : null);

        $salesCollection = SalesInventoryReportService::salesLines($start, $end, $warehouseId);
        $salesLines = $salesCollection->map(fn ($r) => [
            'date' => $r->date,
            'customer_name' => $r->customer_name ?? '',
            'product_name' => $r->product_name ?? '',
            'quantity' => (float) $r->quantity,
            'unit_price' => (float) $r->unit_price,
            'total_revenue' => (float) $r->total_revenue,
            'reference_code' => $r->reference_code ?? '',
        ])->all();

        $inventory = SalesInventoryReportService::inventorySnapshot($warehouseId);

        $meta = [
            'period' => $period,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'warehouse_label' => $this->warehouseLabel($warehouseId),
        ];

        $path = 'excel/sales-inventory-report.xlsx';
        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        Excel::store(
            new SalesInventoryReportMultiSheetExport($salesLines, $inventory, $meta),
            $path,
            config('app.media_disc')
        );

        return $this->sendResponse([
            'sales_inventory_excel_url' => Storage::url($path),
        ], 'Sales and inventory Excel generated successfully.');
    }

    public function pdf(Request $request): JsonResponse
    {
        $period = (string) $request->get('period', 'daily');
        $anchor = $request->get('anchor_date');
        $warehouseId = $this->nullableWarehouseId($request->get('warehouse_id'));

        [$start, $end] = SalesInventoryReportService::resolveDateRange($period, $anchor ? (string) $anchor : null);

        $salesCollection = SalesInventoryReportService::salesLines($start, $end, $warehouseId);
        $salesLines = $salesCollection->map(fn ($r) => [
            'date' => $r->date,
            'customer_name' => $r->customer_name ?? '',
            'product_name' => $r->product_name ?? '',
            'quantity' => (float) $r->quantity,
            'unit_price' => (float) $r->unit_price,
            'total_revenue' => (float) $r->total_revenue,
            'reference_code' => $r->reference_code ?? '',
        ])->all();

        $inventory = SalesInventoryReportService::inventorySnapshot($warehouseId);

        $meta = [
            'period' => $period,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'warehouse_label' => $this->warehouseLabel($warehouseId),
        ];

        $path = 'pdf/sales-inventory-report.pdf';
        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        $pdf = CPDF::loadView('pdf.sales-inventory-report', [
            'salesLines' => $salesLines,
            'inventory' => $inventory,
            'meta' => $meta,
        ])->setPaper('a4', 'landscape');

        Storage::disk(config('app.media_disc'))->put($path, $pdf->output());

        return $this->sendResponse([
            'sales_inventory_pdf_url' => Storage::url($path),
        ], 'Sales and inventory PDF generated successfully.');
    }

    private function nullableWarehouseId(mixed $warehouseId): ?int
    {
        if ($warehouseId === null || $warehouseId === '' || $warehouseId === 'null' || $warehouseId === 'all') {
            return null;
        }

        return (int) $warehouseId;
    }

    private function warehouseLabel(?int $warehouseId): string
    {
        if (! $warehouseId) {
            return 'All warehouses';
        }

        return Warehouse::query()->whereKey($warehouseId)->value('name') ?? (string) $warehouseId;
    }
}
