<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentStatus: string implements HasLabel, HasColor, HasIcon
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Unpaid => 'ยังไม่ชำระ',
            self::Partial => 'ชำระบางส่วน',
            self::Paid => 'ชำระแล้ว',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Unpaid => 'danger',
            self::Partial => 'warning',
            self::Paid => 'success',
        };
    }

    public function getIcon(): string|null
    {
        return match ($this) {
            self::Unpaid => 'heroicon-o-clock',
            self::Partial => 'heroicon-o-banknotes',
            self::Paid => 'heroicon-o-check-circle',
        };
    }
}
