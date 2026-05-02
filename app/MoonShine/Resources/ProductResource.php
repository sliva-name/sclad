<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Product;
use App\MoonShine\Pages\Product\ProductFormPage;
use App\MoonShine\Pages\Product\ProductIndexPage;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
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

    public function save(DataWrapperContract $item, ?FieldsContract $fields = null): DataWrapperContract
    {
        $model = $item->getOriginal();
        $storedStock = 0;

        if ($model->exists) {
            $storedStock = (int) Product::query()
                ->whereKey($model->getKey())
                ->value('current_stock');
        }

        $saved = parent::save($item, $fields);

        $fresh = $saved->getOriginal();

        if ((int) $fresh->current_stock !== $storedStock) {
            Product::query()->whereKey($fresh->getKey())->update(['current_stock' => $storedStock]);
            $fresh->refresh();
        }

        return $saved;
    }

    protected function search(): array
    {
        return ['id', 'name', 'sku'];
    }
}
