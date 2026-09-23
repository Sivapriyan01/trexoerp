<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class BillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'SHIRT 1', 'brand' => 'RX', 'mrp' => 1249, 'size' => 'XL', 'color' => 'Red'],
            ['name' => 'SHIRT 2', 'brand' => 'SQUARE', 'mrp' => 1304, 'size' => 'XL', 'color' => 'White'],
            ['name' => 'PANT 1', 'brand' => 'RX', 'mrp' => 914, 'size' => 'L', 'color' => 'Blue'],
            ['name' => 'T-SHIRT 1', 'brand' => 'RX', 'mrp' => 626, 'size' => 'M', 'color' => 'Red'],
            ['name' => 'INNERS 1', 'brand' => 'SQUARE', 'mrp' => 679, 'size' => 'S', 'color' => 'Blue'],
        ];

        foreach ($products as $p) {
            Category::create([
                'barcode'        => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                'product_name'   => $p['name'],
                'brand'          => $p['brand'],
                'product_type'   => 'Clothing',
                'mrp'            => $p['mrp'],
                'dealer_price'   => $p['mrp'] * 0.7,
                'size'           => $p['size'],
                'color'          => $p['color'],
                'stock'          => rand(20, 100),
                'is_active'      => true,
            ]);
        }
    }
}
