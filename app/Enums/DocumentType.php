<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentType: string implements HasLabel, HasColor, HasIcon
{
    case TaxInvoice = 'tax_invoice';
    case Invoice = 'invoice';
    case DeliveryOrder = 'delivery_order';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::TaxInvoice => 'ใบกำกับภาษี',
            self::Invoice => 'ใบแจ้งหนี้',
            self::DeliveryOrder => 'ใบส่งสินค้า',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TaxInvoice => 'success',
            self::Invoice => 'info',
            self::DeliveryOrder => 'warning',
        };
    }

    public function getIcon(): string|null
    {
        return match ($this) {
            self::TaxInvoice => 'heroicon-o-document-check',
            self::Invoice => 'heroicon-o-document-text',
            self::DeliveryOrder => 'heroicon-o-truck',
        };
    }
}
