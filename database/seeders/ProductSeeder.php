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
                'name' => 'Смартфон Nova X',
                'sku' => 'RU-NOVA-X-001',
                'purchase_price' => 25990.00,
                'sale_price' => 31990.00,
                'attributes' => [
                    'цвет' => 'Черный',
                    'память' => '256 ГБ',
                ],
                'current_stock' => 14,
            ],
            [
                'name' => 'Беспроводные наушники AirBeat',
                'sku' => 'RU-AIRBEAT-002',
                'purchase_price' => 3990.00,
                'sale_price' => 5990.00,
                'attributes' => [
                    'цвет' => 'Белый',
                    'шумоподавление' => 'Да',
                ],
                'current_stock' => 25,
            ],
            [
                'name' => 'Умная колонка Домик Mini',
                'sku' => 'RU-DOMIK-003',
                'purchase_price' => 4490.00,
                'sale_price' => 6990.00,
                'attributes' => [
                    'цвет' => 'Серый',
                    'голосовой помощник' => 'Да',
                ],
                'current_stock' => 10,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
