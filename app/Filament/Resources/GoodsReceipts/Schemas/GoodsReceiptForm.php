<?php

namespace App\Filament\Resources\GoodsReceipts\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class GoodsReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Select::make('branch_id')
                    ->relationship('branch', 'name'),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->required(),
                Select::make('purchase_order_id')
                    ->relationship('purchaseOrder', 'id'),

                TextInput::make('receipt_number')
                    ->required(),
                TextInput::make('supplier_delivery_no'),
                DatePicker::make('document_date')
                    ->required(),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default('draft')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
