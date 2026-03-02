<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrderStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'แบบร่าง',
            self::Confirmed => 'ยืนยันแล้ว',
            self::Completed => 'เสร็จสิ้น',
            self::Cancelled => 'ยกเลิก',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Confirmed => 'info',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): string|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Confirmed => 'heroicon-o-check-circle',
            self::Completed => 'heroicon-o-check-badge',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
