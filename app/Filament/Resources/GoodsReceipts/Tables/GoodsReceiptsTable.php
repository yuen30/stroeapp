<?php

namespace App\Filament\Resources\GoodsReceipts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GoodsReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')->label('รหัสอ้างอิง')
                    ->label('ID')
                    ->rowIndex(),
                TextColumn::make('company.name')->label('บริษัท')
                    ->searchable(),
                TextColumn::make('branch.name')->label('สาขา')
                    ->searchable(),
                TextColumn::make('supplier.name')->label('ผู้จัดจำหน่าย')
                    ->searchable(),
                TextColumn::make('purchaseOrder.id')->label('ใบสั่งซื้อ')
                    ->searchable(),
                TextColumn::make('created_by')->label('ผู้สร้าง')
                    ->searchable(),
                TextColumn::make('receipt_number')->label('เลขที่ใบรับสินค้า')
                    ->searchable(),
                TextColumn::make('supplier_delivery_no')->label('เลขที่ใบส่งของ')
                    ->searchable(),
                TextColumn::make('document_date')->label('วันที่เอกสาร')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')->label('สถานะ')
                    ->badge()
                    ->searchable(),
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
