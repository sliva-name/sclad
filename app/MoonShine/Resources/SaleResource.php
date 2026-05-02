<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Sale;
use App\MoonShine\Pages\Sale\SaleFormPage;
use App\MoonShine\Pages\Sale\SaleIndexPage;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Enums\PageType;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<Sale, SaleIndexPage, SaleFormPage, null>
 */
#[Icon('banknotes')]
#[Group('Склад')]
class SaleResource extends ModelResource
{
    protected string $model = Sale::class;

    protected string $title = 'Продажи (клиенту)';

    protected ?PageType $redirectAfterSave = PageType::INDEX;

    public array $with = ['product'];

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            SaleIndexPage::class,
            SaleFormPage::class,
        ];
    }

    public function save(DataWrapperContract $item, ?FieldsContract $fields = null): DataWrapperContract
    {
        return DB::transaction(function () use ($item, $fields): DataWrapperContract {
            $model = $item->getOriginal();

            if ($model->sold_at === null) {
                $model->sold_at = now();
            }

            if ($model->user_id === null) {
                $model->user_id = auth('moonshine')->id();
            }

            // Must be set before insert because sales.total_price is NOT NULL.
            // Use request values here because model fields are applied inside parent::save().
            $quantity = (int) request()->integer('quantity', (int) ($model->quantity ?? 0));
            $unitPrice = (float) request()->input('unit_price', (float) ($model->unit_price ?? 0));
            $model->total_price = $quantity * $unitPrice;

            $saved = parent::save($item, $fields);

            $sale = $saved->getOriginal();
            app(InventoryService::class)->registerSale($sale);

            return $saved;
        });
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(
            Action::VIEW,
            Action::UPDATE,
            Action::DELETE,
            Action::MASS_DELETE
        );
    }

    protected function search(): array
    {
        return ['id', 'notes'];
    }
}
