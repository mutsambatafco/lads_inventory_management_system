<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        $products = [
            // Electronics
            [
                'name' => 'Dell XPS 13 Laptop',
                'description' => '13-inch laptop with Intel i7 processor, 16GB RAM, 512GB SSD',
                'sku' => 'DLXPS13-001',
                'barcode' => '1234567890123',
                'unit_price' => 1299.99,
                'cost_price' => 950.00,
                'current_quantity' => 15,
                'min_stock_level' => 5,
                'max_stock_level' => 50,
                'category_id' => $categories->where('name', 'Computers & Laptops')->first()->id,
                'supplier_name' => 'Dell Technologies',
                'storage_location' => 'Warehouse A-1',
                'status' => 'active',
                'specifications' => [
                    'processor' => 'Intel Core i7',
                    'ram' => '16GB',
                    'storage' => '512GB SSD',
                    'screen_size' => '13.4 inches'
                ]
            ],
            [
                'name' => 'Logitech MX Master 3 Mouse',
                'description' => 'Wireless ergonomic mouse with advanced tracking',
                'sku' => 'LGMX3-002',
                'barcode' => '1234567890124',
                'unit_price' => 99.99,
                'cost_price' => 65.00,
                'current_quantity' => 45,
                'min_stock_level' => 10,
                'max_stock_level' => 100,
                'category_id' => $categories->where('name', 'Computer Accessories')->first()->id,
                'supplier_name' => 'Logitech',
                'storage_location' => 'Warehouse B-2',
                'status' => 'active',
                'specifications' => [
                    'connectivity' => 'Wireless',
                    'battery_life' => '70 days',
                    'dpi' => '4000'
                ]
            ],

            // Office Supplies
            [
                'name' => 'A4 Copy Paper (500 sheets)',
                'description' => 'Premium A4 copy paper, 80gsm, 500 sheets per ream',
                'sku' => 'PAP-A4-500',
                'barcode' => '1234567890125',
                'unit_price' => 4.99,
                'cost_price' => 2.50,
                'current_quantity' => 200,
                'min_stock_level' => 50,
                'max_stock_level' => 500,
                'category_id' => $categories->where('name', 'Paper Products')->first()->id,
                'supplier_name' => 'PaperCo',
                'storage_location' => 'Warehouse C-3',
                'status' => 'active',
                'specifications' => [
                    'size' => 'A4',
                    'weight' => '80gsm',
                    'sheets' => '500'
                ]
            ],
            [
                'name' => 'Black Ballpoint Pens (Box of 12)',
                'description' => 'Smooth writing ballpoint pens, black ink, box of 12',
                'sku' => 'PEN-BP12',
                'barcode' => '1234567890126',
                'unit_price' => 3.49,
                'cost_price' => 1.80,
                'current_quantity' => 150,
                'min_stock_level' => 30,
                'max_stock_level' => 300,
                'category_id' => $categories->where('name', 'Writing Instruments')->first()->id,
                'supplier_name' => 'WriteRight',
                'storage_location' => 'Warehouse D-4',
                'status' => 'active',
                'specifications' => [
                    'color' => 'Black',
                    'type' => 'Ballpoint',
                    'quantity' => '12'
                ]
            ],

            // Networking
            [
                'name' => 'TP-Link WiFi 6 Router',
                'description' => 'AX1800 Dual-Band WiFi 6 Router',
                'sku' => 'TPL-W6-003',
                'barcode' => '1234567890127',
                'unit_price' => 89.99,
                'cost_price' => 60.00,
                'current_quantity' => 25,
                'min_stock_level' => 5,
                'max_stock_level' => 40,
                'category_id' => $categories->where('name', 'Networking Equipment')->first()->id,
                'supplier_name' => 'TP-Link',
                'storage_location' => 'Warehouse E-5',
                'status' => 'active',
                'specifications' => [
                    'wifi_standard' => 'WiFi 6',
                    'speed' => 'AX1800',
                    'ports' => '4 LAN + 1 WAN'
                ]
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('✓ ' . count($products) . ' products seeded successfully!');
    }
}