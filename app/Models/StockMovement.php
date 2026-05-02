<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'moved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
