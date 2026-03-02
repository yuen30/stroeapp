<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Roles;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id'),
                TextInput::make('branch_id'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('username'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('profile_photo_path'),
                Select::make('role')
                    ->options(Roles::class)
                    ->default('guest'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
