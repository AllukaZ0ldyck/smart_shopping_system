<?php

namespace Database\Seeders;

use App\Models\BaseUnit;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductPrerequisitesSeeder extends Seeder
{
    public function run(): void
    {
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
            Unit::query()->firstOrCreate(
                ['name' => $unit['name']],
                [
                    'short_name' => $unit['short_name'],
                    'base_unit' => $baseUnitIds[$unit['base_unit']],
                ]
            );
        }

        foreach ([
            'Filipino Snacks',
            'Drinks',
            'Groceries',
            'Personal Care',
            'Household Essentials',
        ] as $categoryName) {
            ProductCategory::query()->firstOrCreate(['name' => $categoryName]);
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
            Brand::query()->firstOrCreate(
                ['name' => $brandName],
                ['description' => $brandName . ' demo brand']
            );
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
                array_merge($supplier, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        Warehouse::query()->firstOrCreate(
            ['name' => 'warehouse'],
            [
                'phone' => '123456789',
                'country' => 'PH',
                'city' => 'Quezon City',
                'email' => 'warehouse@warehouse.local',
                'zip_code' => '1100',
            ]
        );

        Warehouse::query()->firstOrCreate(
            ['name' => 'Main Warehouse'],
            [
                'phone' => '09170000000',
                'country' => 'PH',
                'city' => 'Lucena City',
                'email' => 'main.warehouse@example.com',
                'zip_code' => '4301',
            ]
        );

        DB::table('variations')->updateOrInsert(
            ['name' => 'Size'],
            ['updated_at' => now(), 'created_at' => now()]
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
}
