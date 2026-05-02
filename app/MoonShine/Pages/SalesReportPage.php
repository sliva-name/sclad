<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

#[Icon('chart-bar')]
class SalesReportPage extends Page
{
    public function getTitle(): string
    {
        return 'Отчеты по продажам';
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now();

        try {
            if (filled(request()->input('from'))) {
                $from = Carbon::parse((string) request()->input('from'));
            }
        } catch (\Throwable) {
            $from = Carbon::now()->startOfMonth();
        }

        try {
            if (filled(request()->input('to'))) {
                $to = Carbon::parse((string) request()->input('to'));
            }
        } catch (\Throwable) {
            $to = Carbon::now();
        }

        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $salesQuery = Sale::query()
            ->with('product')
            ->whereBetween('sold_at', [$from, $to]);

        $totals = (clone $salesQuery)
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity_sum')
            ->selectRaw('COALESCE(SUM(total_price), 0) as revenue_sum')
            ->selectRaw('COALESCE(AVG(total_price), 0) as avg_check')
            ->first();

        $topProducts = (clone $salesQuery)
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->groupBy('sales.product_id', 'products.name')
            ->select(
                'products.name',
                DB::raw('SUM(sales.quantity) as sold_qty'),
                DB::raw('SUM(sales.total_price) as revenue')
            )
            ->orderByDesc('sold_qty')
            ->limit(10)
            ->get();

        $sales = $salesQuery
            ->latest('sold_at')
            ->limit(100)
            ->get();

        $topRows = $topProducts->map(static fn ($row): array => [
            'name' => (string) $row->name,
            'sold_qty' => (int) $row->sold_qty,
            'revenue' => (float) $row->revenue,
        ]);

        $salesRows = $sales->map(static fn (Sale $sale): array => [
            'sold_at' => optional($sale->sold_at)->toDateString(),
            'product_name' => (string) ($sale->product?->name ?? '-'),
            'quantity' => (int) $sale->quantity,
            'unit_price' => (float) $sale->unit_price,
            'total_price' => (float) $sale->total_price,
        ]);

        $outOfStockCount = Product::query()
            ->where('current_stock', '<=', 0)
            ->count();

        $lowStockCount = Product::query()
            ->whereBetween('current_stock', [1, 5])
            ->count();

        $outOfStockRows = Product::query()
            ->where('current_stock', '<=', 0)
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(static fn (Product $product): array => [
                'name' => (string) $product->name,
                'sku' => (string) ($product->sku ?: '-'),
                'current_stock' => (int) $product->current_stock,
                'status' => 'Нет в наличии',
            ]);

        $lowStockRows = Product::query()
            ->whereBetween('current_stock', [1, 5])
            ->orderBy('current_stock')
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(static fn (Product $product): array => [
                'name' => (string) $product->name,
                'sku' => (string) ($product->sku ?: '-'),
                'current_stock' => (int) $product->current_stock,
                'status' => 'Заканчивается',
            ]);

        return [
            Box::make('Период', [
                Flex::make([
                    Link::make(
                        fn (): string => $this->periodUrl('today'),
                        'Сегодня'
                    )->button(),
                    Link::make(
                        fn (): string => $this->periodUrl('week'),
                        'Неделя'
                    )->button(),
                    Link::make(
                        fn (): string => $this->periodUrl('month'),
                        'Месяц'
                    )->button()->filled(),
                ])->justifyAlign('start')->wrap(),
            ]),

            Grid::make([
                Column::make([
                    ValueMetric::make('Выручка')
                        ->value((float) ($totals->revenue_sum ?? 0))
                        ->columnSpan(12),
                ], colSpan: 3, adaptiveColSpan: 6),
                Column::make([
                    ValueMetric::make('Количество продаж')
                        ->value((int) ($totals->sales_count ?? 0))
                        ->columnSpan(12),
                ], colSpan: 3, adaptiveColSpan: 6),
                Column::make([
                    ValueMetric::make('Продано единиц')
                        ->value((int) ($totals->quantity_sum ?? 0))
                        ->columnSpan(12),
                ], colSpan: 3, adaptiveColSpan: 6),
                Column::make([
                    ValueMetric::make('Средний чек')
                        ->value((float) ($totals->avg_check ?? 0))
                        ->columnSpan(12),
                ], colSpan: 3, adaptiveColSpan: 6),
            ]),

            Grid::make([
                Column::make([
                    ValueMetric::make('Закончились товары')
                        ->value($outOfStockCount)
                        ->columnSpan(12),
                ], colSpan: 6, adaptiveColSpan: 12),
                Column::make([
                    ValueMetric::make('Критический остаток (<=5)')
                        ->value($lowStockCount)
                        ->columnSpan(12),
                ], colSpan: 6, adaptiveColSpan: 12),
            ]),

            Grid::make([
                Column::make([
                    Box::make('Топ товаров', [
                        TableBuilder::make(
                            fields: [
                                Text::make('Товар', 'name'),
                                Number::make('Продано', 'sold_qty'),
                                Number::make('Выручка', 'revenue'),
                            ],
                            items: $this->fallbackTopRows($topRows),
                        )->simple(),
                    ]),
                ], colSpan: 4, adaptiveColSpan: 12),
                Column::make([
                    Box::make('Последние продажи', [
                        TableBuilder::make(
                            fields: [
                                Text::make('Дата', 'sold_at'),
                                Text::make('Товар', 'product_name'),
                                Number::make('Кол-во', 'quantity'),
                                Number::make('Цена', 'unit_price'),
                                Number::make('Сумма', 'total_price'),
                            ],
                            items: $this->fallbackSalesRows($salesRows),
                        )->simple(),
                    ]),
                ], colSpan: 8, adaptiveColSpan: 12),
            ]),

            Grid::make([
                Column::make([
                    Box::make('Товары, которых нет в наличии', [
                        TableBuilder::make(
                            fields: [
                                Text::make('Товар', 'name'),
                                Text::make('SKU', 'sku'),
                                Number::make('Остаток', 'current_stock'),
                                Text::make('Статус', 'status')
                                    ->badge(static fn (): Color => Color::ERROR),
                            ],
                            items: $this->fallbackOutOfStockRows($outOfStockRows),
                        )->simple(),
                    ]),
                ], colSpan: 6, adaptiveColSpan: 12),
                Column::make([
                    Box::make('Товары с низким остатком', [
                        TableBuilder::make(
                            fields: [
                                Text::make('Товар', 'name'),
                                Text::make('SKU', 'sku'),
                                Number::make('Остаток', 'current_stock'),
                                Text::make('Статус', 'status')
                                    ->badge(static fn (): Color => Color::WARNING),
                            ],
                            items: $this->fallbackLowStockRows($lowStockRows),
                        )->simple(),
                    ]),
                ], colSpan: 6, adaptiveColSpan: 12),
            ]),
        ];
    }

    private function periodUrl(string $period): string
    {
        $start = match ($period) {
            'today' => Carbon::now()->startOfDay(),
            'week' => Carbon::now()->startOfWeek(),
            default => Carbon::now()->startOfMonth(),
        };

        return $this->getUrl() . '?' . http_build_query([
            'from' => $start->toDateString(),
            'to' => Carbon::now()->toDateString(),
        ]);
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function fallbackTopRows(Collection $rows): Collection
    {
        if ($rows->isNotEmpty()) {
            return $rows;
        }

        return collect([[
            'name' => 'Нет данных',
            'sold_qty' => 0,
            'revenue' => 0,
        ]]);
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function fallbackSalesRows(Collection $rows): Collection
    {
        if ($rows->isNotEmpty()) {
            return $rows;
        }

        return collect([[
            'sold_at' => '-',
            'product_name' => 'Нет данных',
            'quantity' => 0,
            'unit_price' => 0,
            'total_price' => 0,
        ]]);
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function fallbackOutOfStockRows(Collection $rows): Collection
    {
        if ($rows->isNotEmpty()) {
            return $rows;
        }

        return collect([[
            'name' => 'Нет данных',
            'sku' => '-',
            'current_stock' => 0,
            'status' => 'Нет в наличии',
        ]]);
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function fallbackLowStockRows(Collection $rows): Collection
    {
        if ($rows->isNotEmpty()) {
            return $rows;
        }

        return collect([[
            'name' => 'Нет данных',
            'sku' => '-',
            'current_stock' => 0,
            'status' => 'Заканчивается',
        ]]);
    }
}
