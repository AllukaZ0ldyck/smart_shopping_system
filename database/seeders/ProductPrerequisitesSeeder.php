<?php

namespace Database\Seeders;

use App\Models\BaseUnit;
use App\Models\Brand;
use App\Models\MultiTenant;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductPrerequisitesSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = $this->resolveTenantId();

        if ($this->command) {
            $this->command->info(
                $tenantId
                    ? "Product prerequisites (tenant_id set for API/POS visibility)."
                    : 'Product prerequisites (tenant_id null until StoreSeeder assigns a store/tenant).'
            );
        }

        $baseUnits = [
            'Piece',
            'Pack',
            'Bottle',
            'Can',
            'Sachet',
            'Box',
            'Kilogram',
            'Liter',
        ];

        $baseUnitIds = [];
        foreach ($baseUnits as $index => $name) {
            $baseUnit = BaseUnit::query()->firstOrCreate(
                ['name' => $name],
                ['is_default' => $index === 0]
            );
            if ($index === 0 && empty($baseUnit->is_default)) {
                $baseUnit->update(['is_default' => true]);
            }
            $this->attachTenant($baseUnit, $tenantId);
            $baseUnitIds[$name] = $baseUnit->id;
        }

        $units = [
            ['name' => 'Piece', 'short_name' => 'pc', 'base_unit' => 'Piece'],
            ['name' => 'Pack', 'short_name' => 'pack', 'base_unit' => 'Pack'],
            ['name' => 'Bottle', 'short_name' => 'btl', 'base_unit' => 'Bottle'],
            ['name' => 'Can', 'short_name' => 'can', 'base_unit' => 'Can'],
            ['name' => 'Sachet', 'short_name' => 'scht', 'base_unit' => 'Sachet'],
            ['name' => 'Box', 'short_name' => 'box', 'base_unit' => 'Box'],
            ['name' => 'Kilogram', 'short_name' => 'kg', 'base_unit' => 'Kilogram'],
            ['name' => 'Liter', 'short_name' => 'L', 'base_unit' => 'Liter'],
        ];

        foreach ($units as $unit) {
            $unitModel = Unit::query()->firstOrCreate(
                ['name' => $unit['name']],
                [
                    'short_name' => $unit['short_name'],
                    'base_unit' => $baseUnitIds[$unit['base_unit']],
                ]
            );
            $this->attachTenant($unitModel, $tenantId);
        }

        foreach ([
            'Filipino Snacks',
            'Drinks',
            'Groceries',
            'Personal Care',
            'Household Essentials',
        ] as $categoryName) {
            $category = ProductCategory::query()->firstOrCreate(['name' => $categoryName]);
            $this->attachTenant($category, $tenantId);
        }

        $brands = [
            'Jack n Jill',
            'Oishi',
            'Coca-Cola',
            'URC',
            'Monde Nissin',
            'Rebisco',
            'Nestle',
            'Alaska',
            'Datu Puti',
            'Silver Swan',
        ];
        foreach ($brands as $brandName) {
            $brand = Brand::query()->firstOrCreate(
                ['name' => $brandName],
                ['description' => $brandName . ' demo brand']
            );
            $this->attachTenant($brand, $tenantId);
        }

        $suppliers = [
            [
                'name' => 'Metro Grocery Supply',
                'email' => 'metro.supply@example.com',
                'phone' => '09170000001',
                'country' => 'PH',
                'city' => 'Quezon City',
                'address' => '123 Aurora Blvd',
            ],
            [
                'name' => 'Pacific Snacks Trading',
                'email' => 'pacific.snacks@example.com',
                'phone' => '09170000002',
                'country' => 'PH',
                'city' => 'Pasig',
                'address' => '45 Ortigas Ave',
            ],
            [
                'name' => 'Island Beverages Distribution',
                'email' => 'island.beverages@example.com',
                'phone' => '09170000003',
                'country' => 'PH',
                'city' => 'Makati',
                'address' => '78 Ayala Ave',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->updateOrInsert(
                ['email' => $supplier['email']],
                array_merge($supplier, array_filter([
                    'tenant_id' => $tenantId,
                ]), ['updated_at' => now(), 'created_at' => now()])
            );
        }

        $warehouse = Warehouse::query()->firstOrCreate(
            ['name' => 'warehouse'],
            [
                'phone' => '123456789',
                'country' => 'PH',
                'city' => 'Quezon City',
                'email' => 'warehouse@warehouse.local',
                'zip_code' => '1100',
            ]
        );
        $this->attachTenant($warehouse, $tenantId);

        $mainWarehouse = Warehouse::query()->firstOrCreate(
            ['name' => 'Main Warehouse'],
            [
                'phone' => '09170000000',
                'country' => 'PH',
                'city' => 'Lucena City',
                'email' => 'main.warehouse@example.com',
                'zip_code' => '4301',
            ]
        );
        $this->attachTenant($mainWarehouse, $tenantId);

        DB::table('variations')->updateOrInsert(
            ['name' => 'Size'],
            array_merge(
                array_filter(['tenant_id' => $tenantId]),
                ['updated_at' => now(), 'created_at' => now()]
            )
        );
        $variationId = DB::table('variations')->where('name', 'Size')->value('id');
        if (! empty($variationId)) {
            foreach (['Small', 'Medium', 'Large'] as $name) {
                DB::table('variation_types')->updateOrInsert(
                    ['variation_id' => $variationId, 'name' => $name],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }

    /**
     * When running from the CLI there is no auth user; Multitenantable hides rows where
     * tenant_id is null once you log into the POS/API. Prefer the tenant used by seeded users/stores.
     */
    private function resolveTenantId(): ?string
    {
        return User::withoutGlobalScopes()
            ->whereNotNull('tenant_id')
            ->orderBy('id')
            ->value('tenant_id')
            ?? MultiTenant::query()->orderBy('id')->value('id');
    }

    private function attachTenant(Model $model, ?string $tenantId): void
    {
        if ($tenantId !== null && (string) ($model->getAttribute('tenant_id') ?? '') !== (string) $tenantId) {
            $model->update(['tenant_id' => $tenantId]);
        }
    }
}
