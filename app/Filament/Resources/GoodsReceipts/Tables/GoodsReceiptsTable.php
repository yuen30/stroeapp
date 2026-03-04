<?php

namespace App\Filament\Resources\GoodsReceipts\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GoodsReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('receipt_number')
                    ->label('เลขที่ใบรับสินค้า')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('ผู้จัดจำหน่าย')
                    ->icon('heroicon-o-truck')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn($record) => $record->supplier?->name),
                TextColumn::make('purchaseOrder.order_number')
                    ->label('ใบสั่งซื้ออ้างอิง')
                    ->icon('heroicon-o-shopping-cart')
                    ->searchable()
                    ->sortable()
                    ->placeholder('ไม่มี')
                    ->tooltip('ใบสั่งซื้อที่เกี่ยวข้อง'),
                TextColumn::make('supplier_delivery_no')
                    ->label('เลขที่ใบส่งของ')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->searchable()
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                TextColumn::make('document_date')
                    ->label('วันที่เอกสาร')
                    ->icon('heroicon-o-calendar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->tooltip(fn($record) => $record->document_date?->format('d F Y')),
                TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->label('ผู้สร้าง')
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TextColumn::make('deleted_at')
                    ->label('วันที่ลบ')
                    ->icon('heroicon-o-trash')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options(OrderStatus::class)
                    ->native(false),
                SelectFilter::make('company_id')
                    ->label('บริษัท')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('supplier_id')
                    ->label('ผู้จัดจำหน่าย')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                TrashedFilter::make()
                    ->label('ที่ถูกลบไปแล้ว')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->label('ดู')->icon('heroicon-o-eye')->color('info'),
                EditAction::make()->label('แก้ไข')->icon('heroicon-o-pencil-square')->color('warning'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('ลบ')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบใบรับสินค้า')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบใบรับสินค้าที่เลือก?')
                        ->modalSubmitActionLabel('ยืนยันการลบ'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบใบรับสินค้าถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบใบรับสินค้าถาวร? การกระทำนี้ไม่สามารถย้อนกลับได้')
                        ->modalSubmitActionLabel('ยืนยันการลบถาวร'),
                    RestoreBulkAction::make()
                        ->label('กู้คืน')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->modalHeading('กู้คืนใบรับสินค้า')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการกู้คืนใบรับสินค้าที่เลือก?')
                        ->modalSubmitActionLabel('ยืนยันการกู้คืน'),
                ]),
            ])
            ->emptyStateHeading('ไม่มีใบรับสินค้า')
            ->emptyStateDescription('เริ่มต้นสร้างใบรับสินค้าแรกของคุณ')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
