<?php

namespace App\Filament\Resources\Stocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('product.name')
                    ->label('สินค้า')
                    ->icon('heroicon-o-cube')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn($record) => $record->product?->name),
                TextColumn::make('product.sku')
                    ->label('รหัสสินค้า')
                    ->icon('heroicon-o-hashtag')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label('จำนวนคงเหลือ')
                    ->icon('heroicon-o-archive-box')
                    ->numeric()
                    ->sortable()
                    ->suffix(' หน่วย')
                    ->color(fn($record) => match (true) {
                        $record->quantity <= 0 => 'danger',
                        $record->quantity <= 10 => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn($record) => match (true) {
                        $record->quantity <= 0 => 'สินค้าหมด',
                        $record->quantity <= 10 => 'สินค้าใกล้หมด',
                        default => 'สินค้าเพียงพอ',
                    }),
                TextColumn::make('cost_price')
                    ->label('ราคาต้นทุน')
                    ->icon('heroicon-o-currency-dollar')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('฿')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('selling_price')
                    ->label('ราคาขาย')
                    ->icon('heroicon-o-banknotes')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('฿')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('product.category.name')
                    ->label('หมวดหมู่')
                    ->icon('heroicon-o-tag')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('product.brand.name')
                    ->label('แบรนด์')
                    ->icon('heroicon-o-building-storefront')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->icon('heroicon-o-clock')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('วันที่แก้ไข')
                    ->icon('heroicon-o-pencil')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('สินค้า')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('แก้ไข')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('ลบ')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบสต็อกสินค้า')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบสต็อกสินค้าที่เลือก?')
                        ->modalSubmitActionLabel('ยืนยันการลบ'),
                ]),
            ])
            ->emptyStateHeading('ไม่มีสต็อกสินค้า')
            ->emptyStateDescription('เริ่มต้นเพิ่มสต็อกสินค้าแรกของคุณ')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
