<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\SalesReportPage;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\SaleResource;
use App\MoonShine\Resources\StockMovementResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param CoreContract<MoonShineConfigurator> $core
     */
    public function boot(CoreContract $core): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                ProductResource::class,
                SaleResource::class,
                StockMovementResource::class,
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
                SalesReportPage::class,
            ]);
    }
}
