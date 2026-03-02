<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')->label('รหัสอ้างอิง')
                    ->label('ID')
                    ->rowIndex(),
                TextColumn::make('company_id')->label('รหัสบริษัท')
                    ->searchable(),
                TextColumn::make('branch_id')->label('รหัสสาขา')
                    ->searchable(),
                TextColumn::make('name')->label('ชื่อ')
                    ->searchable(),
                TextColumn::make('username')->label('ชื่อผู้ใช้')
                    ->searchable(),
                TextColumn::make('email')->label('อีเมล')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')->label('ยืนยันอีเมลเมื่อ')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('profile_photo_path')->label('รูปภาพโปรไฟล์')
                    ->searchable(),
                TextColumn::make('role')->label('สิทธิ์การใช้งาน')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_active')->label('สถานะใช้งาน')
                    ->boolean(),
                TextColumn::make('deleted_at')->label('วันที่ลบ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('วันที่สร้าง')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('วันที่แก้ไข')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()->label('ที่ถูกลบไปแล้ว'),
            ])
            ->recordActions([
                EditAction::make()->label('แก้ไข')->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('ลบ')->icon('heroicon-o-trash'),
                    ForceDeleteBulkAction::make()->label('ลบถาวร')->icon('heroicon-o-trash'),
                    RestoreBulkAction::make()->label('กู้คืน')->icon('heroicon-o-arrow-uturn-left'),
                ]),
            ]);
    }
}
