<?php

namespace App\Filament\Resources\TaxInvoices\Schemas;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxInvoiceForm
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
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('sale_order_id')
                    ->relationship('saleOrder', 'id'),

                TextInput::make('tax_invoice_number')
                    ->required(),
                DatePicker::make('document_date')
                    ->required(),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_tax_id'),
                TextInput::make('customer_address'),
                Toggle::make('customer_is_head_office')
                    ->required(),
                TextInput::make('customer_branch_no'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('vat_rate')
                    ->required()
                    ->numeric()
                    ->default(7),
                TextInput::make('vat_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('payment_status')
                    ->options(PaymentStatus::class)
                    ->default('unpaid')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
