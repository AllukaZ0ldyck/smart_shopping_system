<?php

namespace App\Services\Reports;

use App\Models\ManageStock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesInventoryReportService
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function resolveDateRange(string $period, ?string $anchorDate): array
    {
        $period = in_array($period, ['daily', 'weekly', 'monthly'], true) ? $period : 'daily';
        $anchor = $anchorDate ? Carbon::parse($anchorDate)->startOfDay() : Carbon::today();

        return match ($period) {
            'weekly' => [
                $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $anchor->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay(),
            ],
            'monthly' => [
                $anchor->copy()->startOfMonth()->startOfDay(),
                $anchor->copy()->endOfMonth()->endOfDay(),
            ],
            default => [
                $anchor->copy()->startOfDay(),
                $anchor->copy()->endOfDay(),
            ],
        };
    }

    /**
     * @return Collection<int, object>
     */
    public static function salesLines(Carbon $start, Carbon $end, ?int $warehouseId): Collection
    {
        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.date', [$start->toDateString(), $end->toDateString()]);

        if ($warehouseId) {
            $query->where('sales.warehouse_id', $warehouseId);
        }

        return $query->orderBy('sales.date')
            ->orderBy('sales.id')
            ->select([
                'sales.date',
                'customers.name as customer_name',
                'products.name as product_name',
                'sale_items.quantity',
                DB::raw('COALESCE(sale_items.net_unit_price, sale_items.product_price) as unit_price'),
                'sale_items.sub_total as total_revenue',
                'sales.reference_code',
            ])
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function inventorySnapshot(?int $warehouseId): array
    {
        $query = ManageStock::query()->with(['product', 'warehouse']);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $rows = [];
        foreach ($query->orderBy('warehouse_id')->orderBy('product_id')->get() as $stock) {
            $product = $stock->product;
            if (! $product) {
                continue;
            }
            $cost = (float) $product->product_cost;
            $qty = (float) $stock->quantity;
            $rows[] = [
                'product' => $product->name,
                'code' => $product->code,
                'warehouse' => $stock->warehouse?->name ?? '',
                'current_stock' => $qty,
                'unit_cost' => $cost,
                'total_value' => round($qty * $cost, 2),
            ];
        }

        return $rows;
    }
}
