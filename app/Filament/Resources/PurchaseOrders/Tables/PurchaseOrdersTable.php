<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('order_number')
                    ->label('เลขที่ใบสั่งซื้อ')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->iconColor('primary'),
                TextColumn::make('supplier.name')
                    ->label('ผู้จัดจำหน่าย')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-truck')
                    ->iconColor('info'),
                TextColumn::make('order_date')
                    ->label('วันที่สั่งซื้อ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                TextColumn::make('expected_date')
                    ->label('วันที่กำหนดส่ง')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_amount')
                    ->label('ยอดรวมทั้งสิ้น')
                    ->money('THB')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold'),
                TextColumn::make('company.name')
                    ->label('บริษัท')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('branch.name')
                    ->label('สาขา')
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('creator.name')
                    ->label('ผู้สร้าง')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('deleted_at')
                    ->label('วันที่ลบ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('ผู้จัดจำหน่าย')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('ทุกผู้จัดจำหน่าย')
                    ->native(false),
                SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options(\App\Enums\OrderStatus::class)
                    ->placeholder('ทุกสถานะ')
                    ->native(false),
                TrashedFilter::make()
                    ->label('รายการที่ถูกลบ')
                    ->placeholder('ไม่รวมรายการที่ลบ')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->label('ดู')->icon('heroicon-o-eye')->color('info'),
                EditAction::make()->label('แก้ไข')->icon('heroicon-o-pencil-square')->color('warning'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('ลบรายการที่เลือก')->icon('heroicon-o-trash')->color('danger'),
                    ForceDeleteBulkAction::make()->label('ลบถาวร')->icon('heroicon-o-trash')->color('danger'),
                    RestoreBulkAction::make()->label('กู้คืนรายการที่เลือก')->icon('heroicon-o-arrow-uturn-left')->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('ยังไม่มีใบสั่งซื้อในระบบ')
            ->emptyStateDescription('เริ่มต้นโดยการสร้างใบสั่งซื้อใหม่')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
