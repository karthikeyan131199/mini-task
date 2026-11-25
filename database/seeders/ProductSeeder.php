<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Colgate Toothpaste',
                'code' => 'CLG-101',
                'price_per_unit' => 50.00,
                'tax_percentage' => 8.00, // 50 * 0.08 = 4 tax per unit -> 54 total
                'stock_on_hand' => 15,
            ],
            [
                'name' => 'Parle-G Biscuit',
                'code' => 'PRL-102',
                'price_per_unit' => 10.00,
                'tax_percentage' => 8.00, // 10 * 0.08 = 0.8 tax per unit -> 10.8 total
                'stock_on_hand' => 25,
            ],
            [
                'name' => 'Bread (White / Whole Wheat)',
                'code' => 'BRD-201',
                'price_per_unit' => 40.00,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 4, // low stock alert wireframe
            ],
            [
                'name' => 'Milk 1L Pouch',
                'code' => 'MLK-301',
                'price_per_unit' => 60.00,
                'tax_percentage' => 0.00,
                'stock_on_hand' => 9, // low stock alert wireframe
            ],
            [
                'name' => 'Farm Fresh Eggs (12 pk)',
                'code' => 'EGG-401',
                'price_per_unit' => 85.00,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 2, // low stock alert wireframe
            ],
            [
                'name' => 'Tata Tea Gold 250g',
                'code' => 'TEA-501',
                'price_per_unit' => 135.00,
                'tax_percentage' => 5.00,
                'stock_on_hand' => 12,
            ],
            [
                'name' => 'Nescafe Classic Coffee 50g',
                'code' => 'COF-601',
                'price_per_unit' => 190.00,
                'tax_percentage' => 18.00,
                'stock_on_hand' => 8,
            ],
            [
                'name' => 'Basmati Rice 1kg',
                'code' => 'RIC-701',
                'price_per_unit' => 110.00,
                'tax_percentage' => 0.00,
                'stock_on_hand' => 20,
            ],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(['code' => $item['code']], $item);
        }
    }
}
