<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
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
                TextColumn::make('company.name')->label('บริษัท')
                    ->searchable(),
                TextColumn::make('branch.name')->label('สาขา')
                    ->searchable(),
                TextColumn::make('unit.name')->label('หน่วยนับ')
                    ->searchable(),
                TextColumn::make('brand.name')->label('ยี่ห้อ')
                    ->searchable(),
                TextColumn::make('category.name')->label('หมวดหมู่')
                    ->searchable(),
                TextColumn::make('cost_price')->label('ราคาต้นทุน')
                    ->money()
                    ->sortable(),
                TextColumn::make('selling_price')->label('ราคาขาย')
                    ->money()
                    ->sortable(),
                TextColumn::make('stock_quantity')->label('จำนวนสต๊อก')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('barcode')->label('บาร์โค้ด')
                    ->searchable(),
                TextColumn::make('photo_path')->label('รูปภาพ')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
