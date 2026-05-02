<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\StockMovement;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\StockMovementResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Heading;
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
                Heading::make(
                    'Здесь — только поступление, возврат на склад после отказа покупателя и ручная '
                    .'корректировка (можно указать знаком минус). Продажи оформляйте '
                    .'в разделе «Продажи».'
                )->h(4, false),
                ID::make(),
                BelongsTo::make('Товар', 'product', resource: ProductResource::class)
                    ->required()
                    ->withImage('photo_path', 'public', 'products')
                    ->asyncSearch(
                        'name',
                        searchQuery: static function (Builder $query, ?string $term): Builder {
                            $term = trim((string) $term);

                            if ($term === '') {
                                return $query->orderBy('name');
                            }

                            return $query->where(
                                static function (Builder $sub) use ($term): Builder {
                                    return $sub
                                        ->where('name', 'ilike', "%{$term}%")
                                        ->orWhere('sku', 'ilike', "%{$term}%");
                                }
                            );
                        },
                        formatted: static function (mixed $product): string {
                            return $product instanceof Product
                                ? self::productLabel($product)
                                : '';
                        },
                        limit: 30,
                    )
                    ->asyncOnInit(),
                Select::make('Тип', 'type')
                    ->options(StockMovementType::formOptions())
                    ->required()
                    ->hint(
                        'Поступление и возврат — количество больше нуля; корректировка — '
                        .'можно увеличить или уменьшить остаток произвольным целым.'
                    ),
                Number::make('Количество', 'quantity')->required(),
                Number::make('Цена за единицу', 'unit_price')->hint(
                    'Для корректировки без стоимости можно оставить пустым.'
                ),
                Date::make('Дата движения', 'moved_at')->default(now()->toDateString())->required(),
                Textarea::make('Комментарий', 'notes'),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'product_id' => ['required', 'exists:'.Product::class.',id'],
            'type' => ['required', Rule::in(array_keys(StockMovementType::formOptions()))],
            'quantity' => ['required', 'integer'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'moved_at' => ['required', 'date'],
        ];
    }

    private static function productLabel(Product $product): string
    {
        $sku = $product->sku ?: '-';
        $stock = (int) ($product->current_stock ?? 0);

        return "{$product->name} | SKU: {$sku} | Остаток: {$stock}";
    }
}
