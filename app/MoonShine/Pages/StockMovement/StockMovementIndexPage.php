<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\StockMovement;

use App\Enums\StockMovementType;
use App\MoonShine\Resources\StockMovementResource;
use App\Models\StockMovement;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<StockMovementResource>
 */
class StockMovementIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Товар', 'product.name'),
            Text::make('Тип', 'type')
                ->changeFill(static fn (StockMovement $item): string => $item->type?->label() ?? '-')
                ->badge(static function (mixed $value, Text $field) {
                    $movement = $field->getData()?->getOriginal();

                    if (! $movement instanceof StockMovement) {
                        return 'gray';
                    }

                    return ($movement->type instanceof StockMovementType
                        ? $movement->type->badgeColor()
                        : null)?->value ?? 'gray';
                })
                ->sortable(),
            Number::make('Количество', 'quantity'),
            Number::make('Цена за единицу', 'unit_price'),
            Number::make('Сумма', 'total_price'),
            Date::make('Дата движения', 'moved_at')->sortable(),
            Text::make('Комментарий', 'notes'),
        ];
    }
}
