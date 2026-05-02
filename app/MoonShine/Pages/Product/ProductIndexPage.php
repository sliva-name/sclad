<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Product;

use App\MoonShine\Resources\ProductResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<ProductResource>
 */
class ProductIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'name')->sortable(),
            Text::make('SKU', 'sku')->sortable(),
            Number::make('Остаток', 'current_stock')->sortable(),
            Number::make('Закупочная цена', 'purchase_price'),
            Number::make('Цена продажи', 'sale_price'),
            Image::make('Фото', 'photo_path'),
            Text::make('Удален', 'deleted_at'),
        ];
    }
}
