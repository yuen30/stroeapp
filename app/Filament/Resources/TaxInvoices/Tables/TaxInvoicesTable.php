<?php

namespace App\Filament\Resources\TaxInvoices\Tables;

use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
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
                TextColumn::make('tax_invoice_number')
                    ->label('เลขที่ใบกำกับภาษี')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('document_date')
                    ->label('วันที่')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn($record) => $record->document_date?->diffForHumans()),
                TextColumn::make('customer.name')
                    ->label('ลูกค้า')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->customer_tax_id
                        ? "เลขผู้เสียภาษี: {$record->customer_tax_id}"
                        : null)
                    ->wrap(),
                TextColumn::make('saleOrder.invoice_number')
                    ->label('ใบสั่งขาย')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-shopping-bag')
                    ->searchable()
                    ->placeholder('-')
                    ->tooltip('ใบสั่งขายอ้างอิง'),
                TextColumn::make('total_amount')
                    ->label('ยอดเงินรวม')
                    ->numeric(decimalPlaces: 2)
                    ->money('THB', locale: 'th')
                    ->sortable()
                    ->weight('semibold')
                    ->color('success')
                    ->alignEnd()
                    ->description(fn($record) =>
                        'ก่อนภาษี: ฿' . number_format($record->subtotal - $record->discount_amount, 2)
                        . ' | VAT: ฿' . number_format($record->vat_amount, 2)),
                TextColumn::make('payment_status')
                    ->label('สถานะ')
                    ->badge()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('company.name')
                    ->label('บริษัท')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->description(fn($record) => $record->branch?->name),
                TextColumn::make('creator.name')
                    ->label('ผู้สร้าง')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn($record) => $record->created_at?->format('d/m/Y H:i')),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('สถานะการชำระเงิน')
                    ->options(PaymentStatus::class)
                    ->multiple()
                    ->native(false),
                SelectFilter::make('company_id')
                    ->label('บริษัท')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->native(false),
                SelectFilter::make('customer_id')
                    ->label('ลูกค้า')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->native(false),
                SelectFilter::make('has_sale_order')
                    ->label('ใบสั่งขาย')
                    ->options([
                        'with' => 'มีใบสั่งขายอ้างอิง',
                        'without' => 'ไม่มีใบสั่งขายอ้างอิง',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'with') {
                            return $query->whereNotNull('sale_order_id');
                        }
                        if ($data['value'] === 'without') {
                            return $query->whereNull('sale_order_id');
                        }
                    })
                    ->native(false),
                TrashedFilter::make()
                    ->label('รายการที่ถูกลบ')
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('ดู')
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label('แก้ไข')
                    ->icon('heroicon-o-pencil-square'),
                \Filament\Actions\Action::make('print')
                    ->label('พิมพ์')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->action(function ($record) {
                        return response()->streamDownload(function () use ($record) {
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.tax-invoice', [
                                'taxInvoice' => $record->load([
                                    'company',
                                    'branch',
                                    'customer',
                                    'saleOrder.items.product.unit',
                                    'creator',
                                ]),
                            ]);
                            echo $pdf->stream();
                        }, 'TAX-INV-' . $record->tax_invoice_number . '.pdf');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('ลบที่เลือก')
                        ->icon('heroicon-o-trash'),
                    ForceDeleteBulkAction::make()
                        ->label('ลบถาวร')
                        ->icon('heroicon-o-x-circle'),
                    RestoreBulkAction::make()
                        ->label('กู้คืน')
                        ->icon('heroicon-o-arrow-uturn-left'),
                ]),
            ])
            ->defaultSort('document_date', 'desc')
            ->striped()
            ->emptyStateHeading('ยังไม่มีใบกำกับภาษี')
            ->emptyStateDescription('เริ่มต้นสร้างใบกำกับภาษีแรกของคุณได้เลย')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
