<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\MoonShine\Pages\StockMovement\StockMovementFormPage;
use App\MoonShine\Pages\StockMovement\StockMovementIndexPage;
use App\Models\StockMovement;
use App\Services\InventoryService;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use Illuminate\Support\Facades\DB;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<StockMovement, StockMovementIndexPage, StockMovementFormPage, null>
 */
#[Icon('arrows-up-down')]
#[Group('Склад')]
class StockMovementResource extends ModelResource
{
    protected string $model = StockMovement::class;
    protected string $title = 'Движения склада';

    public array $with = ['product'];

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            StockMovementIndexPage::class,
            StockMovementFormPage::class,
        ];
    }

    public function save(DataWrapperContract $item, ?FieldsContract $fields = null): DataWrapperContract
    {
        return DB::transaction(function () use ($item, $fields): DataWrapperContract {
            $model = $item->getOriginal();

            if ($model->moved_at === null) {
                $model->moved_at = now();
            }

            if ($model->user_id === null) {
                $model->user_id = auth('moonshine')->id();
            }

            $saved = parent::save($item, $fields);

            app(InventoryService::class)->applyMovement($saved->getOriginal());

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
        return ['id', 'type', 'notes'];
    }
}
