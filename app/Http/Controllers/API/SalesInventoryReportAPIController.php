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
    /**
     * Path under the web root (public/uploads). Returned in JSON so each browser uses
     * window.location.origin + path — fixes LAN/iPad when .env APP_URL points at localhost
     * or another host than the address the device used to open the app.
     */
    private function mediaDownloadPath(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        return '/uploads/'.$relativePath;
    }

    /** Disk storing exports (see config/filesystems.php → public uploads). */
    private function mediaDisk(): string
    {
        return config('app.media_disc');
    }

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
        $disk = $this->mediaDisk();
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        Excel::store(
            new SalesInventoryReportMultiSheetExport($salesLines, $inventory, $meta),
            $path,
            $disk
        );

        return $this->sendResponse([
            'sales_inventory_excel_url' => $this->mediaDownloadPath($path),
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
        $disk = $this->mediaDisk();
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        $pdf = CPDF::loadView('pdf.sales-inventory-report', [
            'salesLines' => $salesLines,
            'inventory' => $inventory,
            'meta' => $meta,
        ])->setPaper('a4', 'landscape');

        Storage::disk($disk)->put($path, $pdf->output());

        return $this->sendResponse([
            'sales_inventory_pdf_url' => $this->mediaDownloadPath($path),
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
