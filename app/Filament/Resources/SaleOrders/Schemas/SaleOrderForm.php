<?php

namespace App\Filament\Resources\SaleOrders\Schemas;

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleOrderForm
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
                    ->relationship('customer', 'name'),
                TextInput::make('created_by')
                    ->required(),
                Select::make('salesman_id')
                    ->relationship('salesman', 'name'),
                Select::make('document_type')
                    ->options(DocumentType::class)
                    ->default('tax_invoice')
                    ->required(),
                TextInput::make('invoice_number')
                    ->required(),
                DatePicker::make('order_date')
                    ->required(),
                DatePicker::make('due_date'),
                TextInput::make('term_of_payment'),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default('draft')
                    ->required(),
                Select::make('payment_status')
                    ->options(PaymentStatus::class)
                    ->default('unpaid')
                    ->required(),
                Select::make('payment_method')
                    ->options(PaymentMethod::class),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('vat_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
