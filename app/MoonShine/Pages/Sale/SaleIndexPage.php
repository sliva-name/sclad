<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Sale;

use App\MoonShine\Resources\SaleResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<SaleResource>
 */
class SaleIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Товар', 'product.name'),
            Number::make('Количество', 'quantity'),
            Number::make('Цена за единицу', 'unit_price'),
            Number::make('Сумма', 'total_price'),
            Date::make('Дата продажи', 'sold_at')->sortable(),
            Text::make('Комментарий', 'notes'),
        ];
    }
}
