<?php

declare(strict_types=1);

namespace App\Enums;

use MoonShine\Support\Enums\Color;

enum StockMovementType: string
{
    case INBOUND = 'inbound';
    case SALE = 'sale';
    case ADJUSTMENT = 'adjustment';
    case RETURNED = 'returned';

    /**
     * Для формы создания движения вручную: без «продажи», она выполняется в разделе «Продажи».
     *
     * @return array<string, string>
     */
    public static function formOptions(): array
    {
        return [
            self::INBOUND->value => self::INBOUND->label(),
            self::RETURNED->value => self::RETURNED->label(),
            self::ADJUSTMENT->value => self::ADJUSTMENT->label(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::INBOUND->value => self::INBOUND->label(),
            self::RETURNED->value => self::RETURNED->label(),
            self::ADJUSTMENT->value => self::ADJUSTMENT->label(),
            self::SALE->value => self::SALE->label(),
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::INBOUND => 'Поступление',
            self::ADJUSTMENT => 'Корректировка',
            self::RETURNED => 'Возврат на склад',
            self::SALE => 'Продажа',
        };
    }

    public function badgeColor(): Color
    {
        return match ($this) {
            self::INBOUND => Color::SUCCESS,
            self::RETURNED => Color::INFO,
            self::ADJUSTMENT => Color::WARNING,
            self::SALE => Color::ERROR,
        };
    }
}
