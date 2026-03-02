<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
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
                TextInput::make('contact_name'),
                TextInput::make('address_0'),
                TextInput::make('address_1'),
                TextInput::make('amphoe'),
                TextInput::make('province'),
                TextInput::make('postal_code'),
                TextInput::make('tel')
                    ->tel(),
                TextInput::make('fax'),
                TextInput::make('tax_id'),
                TextInput::make('photo_path'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
