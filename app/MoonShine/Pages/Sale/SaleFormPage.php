<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Sale;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Sale;
use App\Models\Product;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\SaleResource;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<SaleResource, Sale>
 */
class SaleFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
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
                    ->asyncOnInit()
                    ->reactive(
                        static function (FieldsContract $fields, mixed $value, BelongsTo $field, array $values): FieldsContract {
                            $product = self::resolveProduct($value)
                                ?? self::resolveProduct(data_get($values, 'product'))
                                ?? self::resolveProduct(data_get($values, 'product.value'))
                                ?? self::resolveProduct(data_get($values, 'product_id'));

                            if ($product === null) {
                                $fields->findByColumn('unit_price')?->setValue(null);
                                $fields->findByColumn('product_attributes_preview')?->setValue('');

                                return $fields;
                            }

                            $fields->findByColumn('unit_price')?->setValue((float) ($product->sale_price ?? 0));
                            $fields->findByColumn('product_attributes_preview')?->setValue(
                                self::productAttributesPreview($product)
                            );

                            return $fields;
                        },
                        debounce: 200
                    ),
                Textarea::make('Характеристики товара', 'product_attributes_preview')
                    ->changeFill(static function (mixed $data): string {
                        $product = data_get($data, 'product');

                        if (! $product instanceof Product) {
                            $productId = data_get($data, 'product_id');
                            $product = $productId ? Product::query()->find($productId) : null;
                        }

                        return $product instanceof Product
                            ? self::productAttributesPreview($product)
                            : '';
                    })
                    ->canApply(static fn (): bool => false)
                    ->disabled()
                    ->readonly()
                    ->reactive(),
                Number::make('Количество', 'quantity')->required(),
                Number::make('Цена за единицу', 'unit_price')->required()->reactive(),
                Date::make('Дата продажи', 'sold_at')->default(now()->toDateString())->required(),
                Textarea::make('Комментарий', 'notes'),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'product_id' => ['required', 'exists:' . Product::class . ',id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'sold_at' => ['required', 'date'],
        ];
    }

    private static function resolveProduct(mixed $value): ?Product
    {
        if ($value instanceof Product) {
            $productId = $value->getKey();

            if (blank($productId)) {
                return $value;
            }

            return Product::query()->find($productId) ?? $value;
        }

        $productId = match (true) {
            \is_array($value) => data_get($value, 'value', data_get($value, 'id')),
            \is_scalar($value) => $value,
            default => null,
        };

        if (blank($productId)) {
            return null;
        }

        return Product::query()->find($productId);
    }

    private static function productLabel(Product $product): string
    {
        $sku = $product->sku ?: '-';
        $stock = (int) ($product->current_stock ?? 0);
        $price = (float) ($product->sale_price ?? 0);

        return "{$product->name} | SKU: {$sku} | Остаток: {$stock} | Цена: {$price}";
    }

    private static function productAttributesPreview(Product $product): string
    {
        $lines = [
            "Название: {$product->name}",
            'SKU: ' . ($product->sku ?: '-'),
            'Остаток: ' . (int) ($product->current_stock ?? 0),
            'Цена продажи: ' . (float) ($product->sale_price ?? 0),
        ];

        $attributes = $product->getAttribute('attributes');

        if (\is_array($attributes) && $attributes !== []) {
            $lines[] = 'Характеристики:';

            foreach ($attributes as $key => $value) {
                $keyString = (string) $key;
                $valueString = Arr::join(
                    Arr::flatten([$value]),
                    ', '
                );

                $lines[] = "- {$keyString}: {$valueString}";
            }
        } else {
            $lines[] = 'Характеристики: не заполнены';
        }

        return implode(PHP_EOL, $lines);
    }
}
