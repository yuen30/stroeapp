<?php

namespace App\Filament\Resources\TaxInvoices\Tables;

use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TaxInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('tax_invoice_number')
                    ->label('เลขที่ใบกำกับภาษี')
                    ->icon('heroicon-o-document-text')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('คลิกเพื่อคัดลอก'),
                TextColumn::make('customer.name')
                    ->label('ลูกค้า')
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn($record) => $record->customer?->name),
                TextColumn::make('customer_name')
                    ->label('ชื่อในใบกำกับภาษี')
                    ->icon('heroicon-o-identification')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer_tax_id')
                    ->label('เลขผู้เสียภาษี')
                    ->icon('heroicon-o-hashtag')
                    ->searchable()
                    ->placeholder('ไม่ระบุ')
                    ->toggleable(),
                IconColumn::make('customer_is_head_office')
                    ->label('สำนักงานใหญ่')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-building-office')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->customer_is_head_office ? 'สำนักงานใหญ่' : 'สาขา')
                    ->toggleable(),
                TextColumn::make('document_date')
                    ->label('วันที่เอกสาร')
                    ->icon('heroicon-o-calendar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->tooltip(fn($record) => $record->document_date?->format('d F Y')),
                TextColumn::make('total_amount')
                    ->label('จำนวนเงินรวม')
                    ->icon('heroicon-o-banknotes')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('฿')
                    ->sortable()
                    ->tooltip('จำนวนเงินรวมทั้งสิ้น'),
                TextColumn::make('payment_status')
                    ->label('สถานะการชำระเงิน')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('saleOrder.id')
                    ->label('ใบสั่งขายอ้างอิง')
                    ->icon('heroicon-o-shopping-bag')
                    ->searchable()
                    ->sortable()
                    ->placeholder('ไม่มี')
                    ->toggleable(),
                TextColumn::make('company.name')
                    ->label('บริษัท')
                    ->icon('heroicon-o-building-office')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('branch.name')
                    ->label('สาขา')
                    ->icon('heroicon-o-building-storefront')
                    ->searchable()
                    ->sortable()
                    ->placeholder('สำนักงานใหญ่')
                    ->toggleable(),
                TextColumn::make('subtotal')
                    ->label('ยอดรวมก่อนภาษี')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('฿')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount_amount')
                    ->label('ส่วนลด')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('฿')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('vat_amount')
                    ->label('ภาษีมูลค่าเพิ่ม')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('฿')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('payment_status')
                    ->label('สถานะการชำระเงิน')
                    ->options(PaymentStatus::class)
                    ->native(false),
                SelectFilter::make('company_id')
                    ->label('บริษัท')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('customer_id')
                    ->label('ลูกค้า')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                TrashedFilter::make()
                    ->label('ที่ถูกลบไปแล้ว')
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
                        ->modalHeading('ลบใบกำกับภาษี')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบใบกำกับภาษีที่เลือก?')
                        ->modalSubmitActionLabel('ยืนยันการลบ'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('ลบใบกำกับภาษีถาวร')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบใบกำกับภาษีถาวร? การกระทำนี้ไม่สามารถย้อนกลับได้')
                        ->modalSubmitActionLabel('ยืนยันการลบถาวร'),
                    RestoreBulkAction::make()
                        ->label('กู้คืน')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->modalHeading('กู้คืนใบกำกับภาษี')
                        ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการกู้คืนใบกำกับภาษีที่เลือก?')
                        ->modalSubmitActionLabel('ยืนยันการกู้คืน'),
                ]),
            ])
            ->emptyStateHeading('ไม่มีใบกำกับภาษี')
            ->emptyStateDescription('เริ่มต้นสร้างใบกำกับภาษีแรกของคุณ')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
