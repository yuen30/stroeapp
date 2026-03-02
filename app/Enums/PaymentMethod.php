<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentMethod: string implements HasLabel, HasIcon
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case Credit = 'credit';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Cash => 'เงินสด',
            self::Transfer => 'โอนเงิน',
            self::Credit => 'เครดิต',
        };
    }

    public function getIcon(): string|null
    {
        return match ($this) {
            self::Cash => 'heroicon-o-banknotes',
            self::Transfer => 'heroicon-o-building-library',
            self::Credit => 'heroicon-o-credit-card',
        };
    }
}
