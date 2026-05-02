<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\MoonShine\Pages\Product\ProductFormPage;
use App\MoonShine\Pages\Product\ProductIndexPage;
use App\Models\Product;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\Support\Attributes\Icon;

/**
 * @extends ModelResource<Product, ProductIndexPage, ProductFormPage, null>
 */
#[Icon('archive-box')]
#[Group('Склад')]
class ProductResource extends ModelResource
{
    protected string $model = Product::class;
    protected string $title = 'Товары';

    protected string $column = 'name';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ProductIndexPage::class,
            ProductFormPage::class,
        ];
    }

    protected function search(): array
    {
        return ['id', 'name', 'sku'];
    }
}
