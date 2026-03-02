<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StockMovementType: string implements HasLabel, HasColor, HasIcon
{
    case In = 'in';
    case Out = 'out';
    case Adjust = 'adjust';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::In => 'รับเข้า',
            self::Out => 'ตัดออก',
            self::Adjust => 'ปรับปรุง',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::In => 'success',
            self::Out => 'danger',
            self::Adjust => 'warning',
        };
    }

    public function getIcon(): string|null
    {
        return match ($this) {
            self::In => 'heroicon-o-arrow-down-tray',
            self::Out => 'heroicon-o-arrow-up-tray',
            self::Adjust => 'heroicon-o-adjustments-horizontal',
        };
    }
}
