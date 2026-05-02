<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Product;

use App\Models\Product;
use App\MoonShine\Resources\ProductResource;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<ProductResource, Product>
 */
class ProductFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Название', 'name')->required(),
                Text::make('SKU', 'sku'),
                Number::make('Остаток на складе', 'current_stock')
                    ->readonly()
                    ->hint(
                        'Считается из приходов, продаж, возвратов и корректировок. '
                        .'Изменить количество: «Движения склада» или «Продажи».'
                    ),
                Number::make('Закупочная цена', 'purchase_price'),
                Number::make('Цена продажи', 'sale_price'),
                Image::make('Фото', 'photo_path')
                    ->disk('public')
                    ->dir('products')
                    ->allowedExtensions(['jpg', 'jpeg', 'png', 'webp']),
                Json::make('Атрибуты', 'attributes')->keyValue('Ключ', 'Значение'),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($item->getOriginal()),
            ],
            'current_stock' => ['sometimes', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'attributes' => ['nullable', 'array'],
        ];
    }
}
