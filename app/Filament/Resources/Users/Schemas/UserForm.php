<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Roles;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organization Details')->schema([
                    Select::make('company_id')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('branch_id')
                        ->relationship('branch', 'name')
                        ->searchable()
                        ->preload(),
                ])->columns(2),
                Section::make('Profile Information')->schema([
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
                        ->required()
                        ->hiddenOn('edit'),
                    FileUpload::make('profile_photo_path')->avatar()->directory('avatars'),
                    Select::make('role')
                    ->options(Roles::class)
                    ->default('guest'),
                    Toggle::make('is_active')
                        ->required(),
                ])->columns(2),
            ]);
    }
}
