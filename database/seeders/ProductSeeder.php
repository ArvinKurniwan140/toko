<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $premiumProducts = [
            [
                'name' => 'Pink Sunglasses',
                'price' => 319.50,
                'image' => '1.jpg',
                'is_premium' => true,
                'category' => 'best'
            ],
            [
                'name' => 'Black Nighty',
                'price' => 319.50,
                'image' => '2.jpg',
                'is_premium' => true,
                'category' => 'best'
            ],
            [
                'name' => 'Yellow Shoulder Bag',
                'price' => 319.50,
                'image' => '3.jpg',
                'is_premium' => true,
                'is_new' => true,
                'category' => 'new'
            ],
            [
                'name' => 'Yellow Sunglasses',
                'price' => 319.50,
                'image' => '4.jpg',
                'is_premium' => true,
                'category' => 'best'
            ],
            [
                'name' => 'Black Shoulder Bag',
                'price' => 319.50,
                'image' => '5.jpg',
                'is_premium' => true,
                'category' => 'best'
            ],
        ];

        // Products for product section
        $products = [
            [
                'name' => 'Long Red Shirt',
                'price' => 39.90,
                'image' => '1.jpg',
                'category' => 'best'
            ],
            [
                'name' => 'Hype Grey Shirt',
                'price' => 19.50,
                'image' => '2.jpg',
                'is_new' => true,
                'category' => 'new'
            ],
            [
                'name' => 'Long Sleeve Jacket',
                'price' => 59.90,
                'image' => '3.jpg',
                'category' => 'best'
            ],
            [
                'name' => 'Denim Men Shirt',
                'price' => 64.40,
                'sale_price' => 32.20,
                'image' => '4.jpg',
                'is_sale' => true,
                'is_new' => true,
                'category' => 'new best'
            ],
            [
                'name' => 'Long Red Shirt',
                'price' => 39.90,
                'image' => '5.jpg',
                'category' => 'best'
            ],
            [
                'name' => 'Hype Grey Shirt',
                'price' => 19.50,
                'image' => '6.jpg',
                'is_new' => true,
                'category' => 'new'
            ],
            [
                'name' => 'Long Sleeve Jacket',
                'price' => 59.90,
                'image' => '7.jpg',
                'category' => 'best'
            ],
            [
                'name' => 'Denim Men Shirt',
                'price' => 64.40,
                'sale_price' => 32.20,
                'image' => '8.jpg',
                'category' => 'best'
            ],
        ];

        foreach (array_merge($premiumProducts, $products) as $product) {
            Product::create($product);
        }
    }
}
