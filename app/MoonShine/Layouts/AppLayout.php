<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Pages\SalesReportPage;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\SaleResource;
use App\MoonShine\Resources\StockMovementResource;
use MoonShine\Contracts\MenuManager\MenuElementContract;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use Rwsite\MoonShinePolarisTheme\Layouts\PolarisThemeLayout;

class AppLayout extends PolarisThemeLayout
{
    /**
     * @return list<MenuElementContract>
     */
    protected function menu(): array
    {
        return [
            /*
            MenuGroup::make(static fn () => __('moonshine::ui.resource.system'), [
                MenuItem::make(MoonShineUserResource::class),
                MenuItem::make(MoonShineUserRoleResource::class),
            ]),
            */

            MenuGroup::make('Склад', [
                MenuItem::make(ProductResource::class),
                MenuItem::make(StockMovementResource::class),
                MenuItem::make(SaleResource::class),
                MenuItem::make(SalesReportPage::class),
            ]),
        ];
    }
}
