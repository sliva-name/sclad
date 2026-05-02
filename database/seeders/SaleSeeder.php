<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()->where('email', 'seller@example.ru')->first();

        $sales = [
            [
                'product_sku' => 'RU-NOVA-X-001',
                'quantity' => 2,
                'unit_price' => 31990.00,
                'notes' => 'Продажа через сайт',
                'sold_at' => Carbon::parse('2026-03-24 14:35:00'),
            ],
            [
                'product_sku' => 'RU-AIRBEAT-002',
                'quantity' => 3,
                'unit_price' => 5990.00,
                'notes' => 'Продажа в розничной точке',
                'sold_at' => Carbon::parse('2026-03-25 16:20:00'),
            ],
            [
                'product_sku' => 'RU-DOMIK-003',
                'quantity' => 2,
                'unit_price' => 6990.00,
                'notes' => 'Корпоративный заказ',
                'sold_at' => Carbon::parse('2026-03-25 19:00:00'),
            ],
        ];

        foreach ($sales as $sale) {
            $product = Product::query()->where('sku', $sale['product_sku'])->first();

            if (! $product) {
                continue;
            }

            $unitPrice = (float) $sale['unit_price'];
            $quantity = (int) $sale['quantity'];

            Sale::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'sold_at' => $sale['sold_at'],
                ],
                [
                    'user_id' => $seller?->id,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                    'notes' => $sale['notes'],
                ]
            );
        }
    }
}
