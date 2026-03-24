<?php

namespace Database\Seeders;

use App\Models\BaseUnit;
use App\Models\Brand;
use App\Models\MainProduct;
use App\Models\ManageStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class FilipinoProductSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::query()->firstOrCreate(
            ['name' => 'Main Warehouse'],
            [
                'email' => 'warehouse@example.com',
                'phone' => '09170000000',
                'country' => 'PH',
                'city' => 'Lucena City',
            ]
        );

        $baseUnits = [
            'Piece',
            'Bottle',
            'Pack',
            'Can',
            'Sachet',
            'Jar',
        ];

        $baseUnitIds = [];
        foreach ($baseUnits as $index => $name) {
            $baseUnit = BaseUnit::query()->firstOrCreate(
                ['name' => $name],
                ['is_default' => $index === 0]
            );
            $baseUnitIds[$name] = $baseUnit->id;
        }

        $units = [
            ['name' => 'Piece', 'short_name' => 'pc', 'base_unit' => 'Piece'],
            ['name' => 'Bottle', 'short_name' => 'btl', 'base_unit' => 'Bottle'],
            ['name' => 'Pack', 'short_name' => 'pack', 'base_unit' => 'Pack'],
            ['name' => 'Can', 'short_name' => 'can', 'base_unit' => 'Can'],
            ['name' => 'Sachet', 'short_name' => 'scht', 'base_unit' => 'Sachet'],
            ['name' => 'Jar', 'short_name' => 'jar', 'base_unit' => 'Jar'],
        ];

        $unitIds = [];
        foreach ($units as $unit) {
            $record = Unit::query()->firstOrCreate(
                ['name' => $unit['name']],
                [
                    'short_name' => $unit['short_name'],
                    'base_unit' => $baseUnitIds[$unit['base_unit']],
                ]
            );
            $unitIds[$unit['name']] = $record->id;
        }

        $categoryIds = [];
        foreach (['Filipino Snacks', 'Drinks', 'Groceries'] as $categoryName) {
            $categoryIds[$categoryName] = ProductCategory::query()->firstOrCreate([
                'name' => $categoryName,
            ])->id;
        }

        $brandDescriptions = [
            'Jack n Jill' => 'Classic Filipino snack favorites.',
            'Oishi' => 'Popular snack brand for chips and crackers.',
            'Leslie\'s' => 'Well-loved local savory snacks.',
            'Rebisco' => 'Filipino biscuits and crackers.',
            'Liwayway' => 'Traditional Filipino sweet snacks.',
            'Coca-Cola' => 'Carbonated drinks and refreshments.',
            'URC' => 'Universal Robina beverage and grocery items.',
            'Kopiko' => 'Ready-to-drink coffee favorites.',
            'Monde Nissin' => 'Noodles and pantry staples.',
            '555' => 'Affordable canned sardines.',
            'Century' => 'Trusted canned tuna products.',
            'Argentina' => 'Classic canned meat products.',
            'Bear Brand' => 'Milk and nutrition staples.',
            'Alaska' => 'Milk and dairy grocery essentials.',
            'Datu Puti' => 'Filipino condiments and pantry goods.',
            'Silver Swan' => 'Soy sauce and vinegar staples.',
        ];

        $brandIds = [];
        foreach ($brandDescriptions as $brandName => $description) {
            $brandIds[$brandName] = Brand::query()->firstOrCreate(
                ['name' => $brandName],
                ['description' => $description]
            )->id;
        }

        $products = [
            ['name' => 'Piattos Cheese', 'code' => 'PINOY001', 'product_code' => 'MP001', 'category' => 'Filipino Snacks', 'brand' => 'Jack n Jill', 'cost' => 15.00, 'price' => 22.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 80, 'stock_alert' => 10, 'notes' => 'Classic cheese-flavored potato crisps.'],
            ['name' => 'Nova Country Cheddar', 'code' => 'PINOY002', 'product_code' => 'MP002', 'category' => 'Filipino Snacks', 'brand' => 'Jack n Jill', 'cost' => 14.00, 'price' => 21.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 75, 'stock_alert' => 10, 'notes' => 'Crunchy corn snack with cheddar flavor.'],
            ['name' => 'Chippy BBQ', 'code' => 'PINOY003', 'product_code' => 'MP003', 'category' => 'Filipino Snacks', 'brand' => 'Jack n Jill', 'cost' => 13.00, 'price' => 20.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 90, 'stock_alert' => 12, 'notes' => 'Barbecue-flavored corn chips.'],
            ['name' => 'Clover Chips BBQ', 'code' => 'PINOY004', 'product_code' => 'MP004', 'category' => 'Filipino Snacks', 'brand' => 'Leslie\'s', 'cost' => 14.50, 'price' => 22.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 85, 'stock_alert' => 12, 'notes' => 'Corn snack with Filipino barbecue taste.'],
            ['name' => 'Boy Bawang Garlic', 'code' => 'PINOY005', 'product_code' => 'MP005', 'category' => 'Filipino Snacks', 'brand' => 'Leslie\'s', 'cost' => 16.00, 'price' => 24.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 70, 'stock_alert' => 10, 'notes' => 'Crunchy cornick with garlic flavor.'],
            ['name' => 'Oishi Prawn Crackers', 'code' => 'PINOY006', 'product_code' => 'MP006', 'category' => 'Filipino Snacks', 'brand' => 'Oishi', 'cost' => 12.50, 'price' => 19.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 95, 'stock_alert' => 12, 'notes' => 'Light and crispy prawn crackers.'],
            ['name' => 'Oishi Ribbed Cracklings', 'code' => 'PINOY007', 'product_code' => 'MP007', 'category' => 'Filipino Snacks', 'brand' => 'Oishi', 'cost' => 13.50, 'price' => 20.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 85, 'stock_alert' => 12, 'notes' => 'Savory ribbed-style cracklings.'],
            ['name' => 'SkyFlakes Crackers', 'code' => 'PINOY008', 'product_code' => 'MP008', 'category' => 'Filipino Snacks', 'brand' => 'Rebisco', 'cost' => 10.00, 'price' => 15.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 120, 'stock_alert' => 15, 'notes' => 'Classic crackers for merienda and pantry use.'],
            ['name' => 'Rebisco Sandwich Strawberry', 'code' => 'PINOY009', 'product_code' => 'MP009', 'category' => 'Filipino Snacks', 'brand' => 'Rebisco', 'cost' => 11.50, 'price' => 17.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 100, 'stock_alert' => 15, 'notes' => 'Sweet sandwich crackers with strawberry cream.'],
            ['name' => 'Chocnut Mini Bar', 'code' => 'PINOY010', 'product_code' => 'MP010', 'category' => 'Filipino Snacks', 'brand' => 'Liwayway', 'cost' => 8.00, 'price' => 12.00, 'base_unit' => 'Piece', 'unit' => 'Piece', 'stock' => 150, 'stock_alert' => 20, 'notes' => 'Classic peanut-milk chocolate snack.'],
            ['name' => 'Coca-Cola Mismo 290ml', 'code' => 'PINOY011', 'product_code' => 'MP011', 'category' => 'Drinks', 'brand' => 'Coca-Cola', 'cost' => 16.00, 'price' => 24.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 90, 'stock_alert' => 12, 'notes' => 'Regular Coke in small PET bottle.'],
            ['name' => 'Sprite Mismo 290ml', 'code' => 'PINOY012', 'product_code' => 'MP012', 'category' => 'Drinks', 'brand' => 'Coca-Cola', 'cost' => 16.00, 'price' => 24.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 85, 'stock_alert' => 12, 'notes' => 'Lemon-lime soda in small PET bottle.'],
            ['name' => 'Royal Tru-Orange 290ml', 'code' => 'PINOY013', 'product_code' => 'MP013', 'category' => 'Drinks', 'brand' => 'Coca-Cola', 'cost' => 16.00, 'price' => 24.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 80, 'stock_alert' => 12, 'notes' => 'Orange soda favorite.'],
            ['name' => 'C2 Apple Green Tea 500ml', 'code' => 'PINOY014', 'product_code' => 'MP014', 'category' => 'Drinks', 'brand' => 'URC', 'cost' => 18.00, 'price' => 28.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 70, 'stock_alert' => 10, 'notes' => 'Sweet bottled green tea with apple flavor.'],
            ['name' => 'C2 Lemon Green Tea 500ml', 'code' => 'PINOY015', 'product_code' => 'MP015', 'category' => 'Drinks', 'brand' => 'URC', 'cost' => 18.00, 'price' => 28.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 70, 'stock_alert' => 10, 'notes' => 'Sweet bottled green tea with lemon flavor.'],
            ['name' => 'Kopiko 78 Coffee 240ml', 'code' => 'PINOY016', 'product_code' => 'MP016', 'category' => 'Drinks', 'brand' => 'Kopiko', 'cost' => 20.00, 'price' => 32.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 60, 'stock_alert' => 8, 'notes' => 'Ready-to-drink strong coffee.'],
            ['name' => 'Sting Energy Drink 330ml', 'code' => 'PINOY017', 'product_code' => 'MP017', 'category' => 'Drinks', 'brand' => 'URC', 'cost' => 18.00, 'price' => 27.00, 'base_unit' => 'Can', 'unit' => 'Can', 'stock' => 65, 'stock_alert' => 8, 'notes' => 'Popular energy drink for convenience stores.'],
            ['name' => 'Summit Natural Water 500ml', 'code' => 'PINOY018', 'product_code' => 'MP018', 'category' => 'Drinks', 'brand' => 'URC', 'cost' => 10.00, 'price' => 15.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 110, 'stock_alert' => 15, 'notes' => 'Bottled drinking water.'],
            ['name' => 'Lucky Me Pancit Canton Original', 'code' => 'PINOY019', 'product_code' => 'MP019', 'category' => 'Groceries', 'brand' => 'Monde Nissin', 'cost' => 14.00, 'price' => 20.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 120, 'stock_alert' => 15, 'notes' => 'Instant dry noodles, original flavor.'],
            ['name' => 'Lucky Me Beef Mami', 'code' => 'PINOY020', 'product_code' => 'MP020', 'category' => 'Groceries', 'brand' => 'Monde Nissin', 'cost' => 13.00, 'price' => 19.00, 'base_unit' => 'Pack', 'unit' => 'Pack', 'stock' => 110, 'stock_alert' => 15, 'notes' => 'Instant noodle soup, beef flavor.'],
            ['name' => '555 Sardines Tomato Sauce', 'code' => 'PINOY021', 'product_code' => 'MP021', 'category' => 'Groceries', 'brand' => '555', 'cost' => 20.00, 'price' => 28.00, 'base_unit' => 'Can', 'unit' => 'Can', 'stock' => 95, 'stock_alert' => 12, 'notes' => 'Canned sardines in tomato sauce.'],
            ['name' => 'Century Tuna Flakes in Oil', 'code' => 'PINOY022', 'product_code' => 'MP022', 'category' => 'Groceries', 'brand' => 'Century', 'cost' => 28.00, 'price' => 39.00, 'base_unit' => 'Can', 'unit' => 'Can', 'stock' => 80, 'stock_alert' => 10, 'notes' => 'Easy-open canned tuna flakes.'],
            ['name' => 'Argentina Corned Beef', 'code' => 'PINOY023', 'product_code' => 'MP023', 'category' => 'Groceries', 'brand' => 'Argentina', 'cost' => 34.00, 'price' => 46.00, 'base_unit' => 'Can', 'unit' => 'Can', 'stock' => 70, 'stock_alert' => 10, 'notes' => 'Filipino canned corned beef.'],
            ['name' => 'Bear Brand Powdered Milk 33g', 'code' => 'PINOY024', 'product_code' => 'MP024', 'category' => 'Groceries', 'brand' => 'Bear Brand', 'cost' => 12.00, 'price' => 18.00, 'base_unit' => 'Sachet', 'unit' => 'Sachet', 'stock' => 130, 'stock_alert' => 20, 'notes' => 'Single-serve powdered milk sachet.'],
            ['name' => 'Alaska Evaporated Milk 370ml', 'code' => 'PINOY025', 'product_code' => 'MP025', 'category' => 'Groceries', 'brand' => 'Alaska', 'cost' => 32.00, 'price' => 44.00, 'base_unit' => 'Can', 'unit' => 'Can', 'stock' => 75, 'stock_alert' => 10, 'notes' => 'Evaporated filled milk for desserts and coffee.'],
            ['name' => 'Datu Puti Vinegar 350ml', 'code' => 'PINOY026', 'product_code' => 'MP026', 'category' => 'Groceries', 'brand' => 'Datu Puti', 'cost' => 20.00, 'price' => 29.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 60, 'stock_alert' => 8, 'notes' => 'Classic Filipino cane vinegar.'],
            ['name' => 'Silver Swan Soy Sauce 385ml', 'code' => 'PINOY027', 'product_code' => 'MP027', 'category' => 'Groceries', 'brand' => 'Silver Swan', 'cost' => 24.00, 'price' => 34.00, 'base_unit' => 'Bottle', 'unit' => 'Bottle', 'stock' => 60, 'stock_alert' => 8, 'notes' => 'Everyday soy sauce pantry staple.'],
        ];

        foreach ($products as $item) {
            $mainProduct = MainProduct::query()->firstOrCreate(
                ['code' => $item['product_code']],
                [
                    'name' => $item['name'],
                    'product_unit' => (string) $baseUnitIds[$item['base_unit']],
                    'product_type' => MainProduct::SINGLE_PRODUCT,
                ]
            );

            $mainProduct->update([
                'name' => $item['name'],
                'product_unit' => (string) $baseUnitIds[$item['base_unit']],
                'product_type' => MainProduct::SINGLE_PRODUCT,
            ]);

            $product = Product::query()->firstOrCreate(
                ['code' => $item['code']],
                [
                    'main_product_id' => $mainProduct->id,
                    'name' => $item['name'],
                    'product_code' => $item['product_code'],
                    'barcode_symbol' => Product::CODE128,
                    'product_category_id' => $categoryIds[$item['category']],
                    'brand_id' => $brandIds[$item['brand']],
                    'product_cost' => $item['cost'],
                    'product_price' => $item['price'],
                    'product_unit' => (string) $baseUnitIds[$item['base_unit']],
                    'sale_unit' => (string) $unitIds[$item['unit']],
                    'purchase_unit' => (string) $unitIds[$item['unit']],
                    'stock_alert' => (string) $item['stock_alert'],
                    'quantity_limit' => null,
                    'order_tax' => 0,
                    'tax_type' => 1,
                    'notes' => $item['notes'],
                ]
            );

            $product->update([
                'main_product_id' => $mainProduct->id,
                'name' => $item['name'],
                'product_code' => $item['product_code'],
                'barcode_symbol' => Product::CODE128,
                'product_category_id' => $categoryIds[$item['category']],
                'brand_id' => $brandIds[$item['brand']],
                'product_cost' => $item['cost'],
                'product_price' => $item['price'],
                'product_unit' => (string) $baseUnitIds[$item['base_unit']],
                'sale_unit' => (string) $unitIds[$item['unit']],
                'purchase_unit' => (string) $unitIds[$item['unit']],
                'stock_alert' => (string) $item['stock_alert'],
                'order_tax' => 0,
                'tax_type' => 1,
                'notes' => $item['notes'],
            ]);

            ManageStock::query()->updateOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $item['stock'],
                    'alert' => 0,
                ]
            );
        }
    }
}
