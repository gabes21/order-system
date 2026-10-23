<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories first.
        $categories = [
            [
                'name' => 'Mystical Items',
                'slug' => 'mystical-items',
                'description' => 'Objects with supernatural abilities.',
            ],
            [
                'name' => 'Sealed Artifacts',
                'slug' => 'sealed-artifacts',
                'description' => 'Dangerous artifacts with extraordinary powers.',
            ],
            [
                'name' => 'Mystical Weapons',
                'slug' => 'mystical-weapons',
                'description' => 'Weapons and tools infused with supernatural abilities.',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                $category
            );
        }

        // Create products after their categories exist.
        $products = [
            [
                'category_slug' => 'mystical-items',
                'name' => "Azik's Copper Whistle",
                'slug' => 'aziks-copper-whistle',
                'description' => "Summons one of Azik's messengers but may attract undead creatures.",
                'price' => 800.00,
                'stock_quantity' => 5,
            ],
            [
                'category_slug' => 'sealed-artifacts',
                'name' => 'Creeping Hunger',
                'slug' => 'creeping-hunger',
                'description' => 'A sinister glove that uses supernatural abilities stored within consumed souls.',
                'price' => 15000.00,
                'stock_quantity' => 1,
            ],
            [
                'category_slug' => 'sealed-artifacts',
                'name' => 'Arrodes',
                'slug' => 'arrodes',
                'description' => 'A sentient silver mirror with powerful divination abilities and unusual demands.',
                'price' => 12000.00,
                'stock_quantity' => 1,
            ],
            [
                'category_slug' => 'sealed-artifacts',
                'name' => 'Sun Brooch',
                'slug' => 'sun-brooch',
                'description' => 'A golden brooch that grants Sun Pathway spells, with an oppressive heat drawback.',
                'price' => 2000.00,
                'stock_quantity' => 2,
            ],
            [
                'category_slug' => 'sealed-artifacts',
                'name' => 'Master Key',
                'slug' => 'master-key',
                'description' => 'A mysterious key associated with Apprentice pathway abilities.',
                'price' => 3500.00,
                'stock_quantity' => 2,
            ],
            [
                'category_slug' => 'mystical-items',
                'name' => 'Language of Foulness',
                'slug' => 'language-of-foulness',
                'description' => 'A mystical item associated with the Black Ear and supernatural effects.',
                'price' => 1500.00,
                'stock_quantity' => 4,
            ],
        ];

        foreach ($products as $product) {
            $categoryId = DB::table('categories')
                ->where('slug', $product['category_slug'])
                ->value('id');

            DB::table('products')->updateOrInsert(
                ['slug' => $product['slug']],
                [
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'stock_quantity' => $product['stock_quantity'],
                    'image_path' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
