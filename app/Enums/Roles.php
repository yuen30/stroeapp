<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Roles: string implements HasLabel, HasColor, HasIcon
{
    case Admin = 'admin';
    case User = 'user';
    case Staff = 'staff';
    case Guest = 'guest';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Admin => 'ผู้ดูแลระบบ',
            self::User => 'ผู้ใช้งาน',
            self::Staff => 'พนักงาน',
            self::Guest => 'ผู้เยี่ยมชม',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::User => 'success',
            self::Staff => 'warning',
            self::Guest => 'gray',
        };
    }

    public function getIcon(): string|null
    {
        return match ($this) {
            self::Admin => 'heroicon-o-shield-check',
            self::User => 'heroicon-o-user',
            self::Staff => 'heroicon-o-briefcase',
            self::Guest => 'heroicon-o-eye',
        };
    }
}
