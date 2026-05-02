<?php

namespace Database\Seeders;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()->where('email', 'seller@example.ru')->first();

        $movements = [
            [
                'product_sku' => 'RU-NOVA-X-001',
                'type' => StockMovementType::INBOUND,
                'quantity' => 20,
                'unit_price' => 25990.00,
                'notes' => 'Первичная поставка от поставщика',
                'moved_at' => Carbon::parse('2026-03-19 10:00:00'),
            ],
            [
                'product_sku' => 'RU-NOVA-X-001',
                'type' => StockMovementType::SALE,
                'quantity' => -6,
                'unit_price' => 31990.00,
                'notes' => 'Продажи за неделю',
                'moved_at' => Carbon::parse('2026-03-24 14:30:00'),
            ],
            [
                'product_sku' => 'RU-AIRBEAT-002',
                'type' => StockMovementType::INBOUND,
                'quantity' => 30,
                'unit_price' => 3990.00,
                'notes' => 'Поставка наушников',
                'moved_at' => Carbon::parse('2026-03-20 11:00:00'),
            ],
            [
                'product_sku' => 'RU-AIRBEAT-002',
                'type' => StockMovementType::SALE,
                'quantity' => -5,
                'unit_price' => 5990.00,
                'notes' => 'Реализация клиентам',
                'moved_at' => Carbon::parse('2026-03-25 16:15:00'),
            ],
            [
                'product_sku' => 'RU-DOMIK-003',
                'type' => StockMovementType::INBOUND,
                'quantity' => 12,
                'unit_price' => 4490.00,
                'notes' => 'Стартовое поступление',
                'moved_at' => Carbon::parse('2026-03-21 09:30:00'),
            ],
            [
                'product_sku' => 'RU-DOMIK-003',
                'type' => StockMovementType::ADJUSTMENT,
                'quantity' => -2,
                'unit_price' => 4490.00,
                'notes' => 'Корректировка после инвентаризации',
                'moved_at' => Carbon::parse('2026-03-25 18:40:00'),
            ],
        ];

        foreach ($movements as $movement) {
            $product = Product::query()->where('sku', $movement['product_sku'])->first();

            if (! $product) {
                continue;
            }

            $unitPrice = (float) $movement['unit_price'];
            $quantity = (int) $movement['quantity'];

            StockMovement::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => $movement['type']->value,
                    'quantity' => $quantity,
                    'moved_at' => $movement['moved_at'],
                ],
                [
                    'user_id' => $seller?->id,
                    'unit_price' => $unitPrice,
                    'total_price' => abs($quantity) * $unitPrice,
                    'notes' => $movement['notes'],
                ]
            );
        }
    }
}
