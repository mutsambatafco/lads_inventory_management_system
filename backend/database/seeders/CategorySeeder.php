<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Electronics
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and components',
                'is_active' => true
            ],
            [
                'name' => 'Computers & Laptops',
                'description' => 'Desktop computers, laptops, and accessories',
                'is_active' => true
            ],
            [
                'name' => 'Smartphones & Tablets',
                'description' => 'Mobile phones, tablets, and accessories',
                'is_active' => true
            ],
            [
                'name' => 'Audio & Video',
                'description' => 'Speakers, headphones, TVs, and audio equipment',
                'is_active' => true
            ],
            [
                'name' => 'Computer Accessories',
                'description' => 'Keyboards, mice, monitors, and computer peripherals',
                'is_active' => true
            ],

            // Office Supplies
            [
                'name' => 'Office Supplies',
                'description' => 'General office stationery and supplies',
                'is_active' => true
            ],
            [
                'name' => 'Paper Products',
                'description' => 'Printing paper, notebooks, and paper supplies',
                'is_active' => true
            ],
            [
                'name' => 'Writing Instruments',
                'description' => 'Pens, pencils, markers, and writing tools',
                'is_active' => true
            ],
            [
                'name' => 'Filing & Storage',
                'description' => 'Folders, binders, and storage solutions',
                'is_active' => true
            ],

            // Furniture
            [
                'name' => 'Office Furniture',
                'description' => 'Desks, chairs, and office furniture',
                'is_active' => true
            ],
            [
                'name' => 'Storage Furniture',
                'description' => 'Cabinets, shelves, and storage units',
                'is_active' => true
            ],
            [
                'name' => 'Meeting Room Furniture',
                'description' => 'Conference tables and meeting chairs',
                'is_active' => true
            ],

            // IT Equipment
            [
                'name' => 'Networking Equipment',
                'description' => 'Routers, switches, and network devices',
                'is_active' => true
            ],
            [
                'name' => 'Servers & Storage',
                'description' => 'Server hardware and storage devices',
                'is_active' => true
            ],
            [
                'name' => 'Printers & Scanners',
                'description' => 'Printing and scanning equipment',
                'is_active' => true
            ],

            // Peripherals
            [
                'name' => 'Cables & Adapters',
                'description' => 'Various cables, connectors, and adapters',
                'is_active' => true
            ],
            [
                'name' => 'Power Supplies',
                'description' => 'UPS, batteries, and power equipment',
                'is_active' => true
            ],
            [
                'name' => 'Tools & Equipment',
                'description' => 'IT tools and maintenance equipment',
                'is_active' => true
            ],

            // Software & Licenses
            [
                'name' => 'Software',
                'description' => 'Software applications and programs',
                'is_active' => true
            ],
            [
                'name' => 'Licenses & Subscriptions',
                'description' => 'Software licenses and subscriptions',
                'is_active' => true
            ],

            // Miscellaneous
            [
                'name' => 'Cleaning Supplies',
                'description' => 'Cleaning products and maintenance supplies',
                'is_active' => true
            ],
            [
                'name' => 'Safety Equipment',
                'description' => 'Safety gear and emergency equipment',
                'is_active' => true
            ],
            [
                'name' => 'Miscellaneous',
                'description' => 'Other uncategorized items',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✓ ' . count($categories) . ' categories seeded successfully!');
    }
}