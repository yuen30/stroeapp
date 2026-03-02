<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('address_0'),
                TextInput::make('address_1'),
                TextInput::make('amphoe'),
                TextInput::make('province'),
                TextInput::make('postal_code'),
                TextInput::make('tel')
                    ->tel(),
                TextInput::make('fax'),
                TextInput::make('tax_id'),
                TextInput::make('credit_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('credit_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('vat_rate')
                    ->required()
                    ->numeric()
                    ->default(7),
                Toggle::make('is_head_office')
                    ->required(),
                TextInput::make('branch_no'),
                TextInput::make('photo_path'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
