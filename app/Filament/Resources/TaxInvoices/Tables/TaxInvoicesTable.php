<?php

namespace App\Filament\Resources\TaxInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TaxInvoicesTable
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
                TextColumn::make('saleOrder.id')->label('ใบสั่งขาย')
                    ->searchable(),
                TextColumn::make('created_by')->label('ผู้สร้าง')
                    ->searchable(),
                TextColumn::make('tax_invoice_number')->label('เลขที่ใบกำกับภาษี')
                    ->searchable(),
                TextColumn::make('document_date')->label('วันที่เอกสาร')
                    ->date()
                    ->sortable(),
                TextColumn::make('customer_name')->label('ชื่อลูกค้า')
                    ->searchable(),
                TextColumn::make('customer_tax_id')->label('เลขผู้เสียภาษีลูกค้า')
                    ->searchable(),
                TextColumn::make('customer_address')->label('ที่อยู่ลูกค้า')
                    ->searchable(),
                IconColumn::make('customer_is_head_office')->label('ลูกค้าสำนักงานใหญ่')
                    ->boolean(),
                TextColumn::make('customer_branch_no')->label('รหัสสาขาลูกค้า')
                    ->searchable(),
                TextColumn::make('subtotal')->label('ยอดรวม')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')->label('ส่วนลด')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vat_rate')->label('อัตราภาษี')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vat_amount')->label('ภาษีมูลค่าเพิ่ม')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')->label('จำนวนเงินรวม')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_status')->label('สถานะการชำระเงิน')
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
