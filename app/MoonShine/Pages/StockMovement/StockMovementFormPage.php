<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\StockMovement;

use App\Enums\StockMovementType;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<StockMovementResource, StockMovement>
 */
class StockMovementFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                BelongsTo::make('Товар', 'product', resource: ProductResource::class)->required(),
                Select::make('Тип', 'type')
                    ->options(StockMovementType::options())
                    ->required(),
                Number::make('Количество', 'quantity')->required(),
                Number::make('Цена за единицу', 'unit_price'),
                Date::make('Дата движения', 'moved_at')->default(now()->toDateString())->required(),
                Textarea::make('Комментарий', 'notes'),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'product_id' => ['required', 'exists:' . Product::class . ',id'],
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'quantity' => ['required', 'integer'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'moved_at' => ['required', 'date'],
        ];
    }
}
