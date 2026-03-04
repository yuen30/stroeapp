<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProductCondition: string implements HasLabel, HasColor, HasIcon
{
    case Good = 'good';
    case Damaged = 'damaged';
    case Defective = 'defective';

    public function getLabel(): string
    {
        return match ($this) {
            self::Good => 'ดี',
            self::Damaged => 'ชำรุด',
            self::Defective => 'บกพร่อง',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Good => 'success',
            self::Damaged => 'warning',
            self::Defective => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Good => 'heroicon-o-check-circle',
            self::Damaged => 'heroicon-o-exclamation-triangle',
            self::Defective => 'heroicon-o-x-circle',
        };
    }
}
