<?php

namespace App\Filament\Resources\SaleOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SaleOrdersTable
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
                TextColumn::make('customer.name')->label('ลูกค้า')
                    ->searchable(),
                TextColumn::make('created_by')->label('ผู้สร้าง')
                    ->searchable(),
                TextColumn::make('salesman.name')->label('พนักงานขาย')
                    ->searchable(),
                TextColumn::make('document_type')->label('ประเภทเอกสาร')
                    ->badge()
                    ->searchable(),
                TextColumn::make('invoice_number')->label('เลขที่ใบกำกับภาษี')
                    ->searchable(),
                TextColumn::make('order_date')->label('วันที่สั่งซื้อ')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')->label('วันครบกำหนด')
                    ->date()
                    ->sortable(),
                TextColumn::make('term_of_payment')->label('เงื่อนไขชำระเงิน')
                    ->searchable(),
                TextColumn::make('status')->label('สถานะ')
                    ->badge()
                    ->searchable(),
                TextColumn::make('payment_status')->label('สถานะการชำระเงิน')
                    ->badge()
                    ->searchable(),
                TextColumn::make('payment_method')->label('วิธีชำระเงิน')
                    ->badge()
                    ->searchable(),
                TextColumn::make('subtotal')->label('ยอดรวม')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')->label('ส่วนลด')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vat_amount')->label('ภาษีมูลค่าเพิ่ม')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')->label('จำนวนเงินรวม')
                    ->numeric()
                    ->sortable(),
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
