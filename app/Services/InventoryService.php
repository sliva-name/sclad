<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function applyMovement(StockMovement $movement): void
    {
        $movementType = $movement->type instanceof StockMovementType
            ? $movement->type
            : StockMovementType::from((string) $movement->type);

        $product = Product::query()
            ->lockForUpdate()
            ->findOrFail($movement->product_id);

        $quantity = (int) $movement->quantity;

        if ($movementType !== StockMovementType::ADJUSTMENT && $quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than 0',
            ]);
        }

        $delta = match ($movementType) {
            StockMovementType::INBOUND => $quantity,
            StockMovementType::RETURNED => $quantity,
            StockMovementType::SALE => -1 * $quantity,
            StockMovementType::ADJUSTMENT => $quantity,
            default => throw ValidationException::withMessages([
                'type' => 'Unknown movement type',
            ]),
        };

        $nextStock = $product->current_stock + $delta;

        if ($nextStock < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock',
            ]);
        }

        $product->update([
            'current_stock' => $nextStock,
        ]);

        if ($movement->total_price === null && $movement->unit_price !== null) {
            $movement->total_price = abs($quantity) * (float) $movement->unit_price;
        }

        if ($movement->moved_at === null) {
            $movement->moved_at = now();
        }

        $movement->saveQuietly();
    }

    public function registerSale(Sale $sale): void
    {
        $product = Product::query()
            ->lockForUpdate()
            ->findOrFail($sale->product_id);

        if ($sale->quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than 0',
            ]);
        }

        if ($product->current_stock < $sale->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock',
            ]);
        }

        $product->update([
            'current_stock' => $product->current_stock - $sale->quantity,
        ]);

        StockMovement::query()->create([
            'product_id' => $sale->product_id,
            'user_id' => $sale->user_id,
            'type' => StockMovementType::SALE,
            'quantity' => $sale->quantity,
            'unit_price' => $sale->unit_price,
            'total_price' => $sale->total_price,
            'notes' => $sale->notes,
            'moved_at' => $sale->sold_at ?? now(),
        ]);
    }
}
