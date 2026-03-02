<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')->label('รหัสอ้างอิง')
                    ->label('ID')
                    ->rowIndex(),
                TextColumn::make('name')->label('ชื่อ')
                    ->searchable(),
                TextColumn::make('code')->label('รหัส')
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
