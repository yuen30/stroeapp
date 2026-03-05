<?php

namespace App\Filament\Resources\TaxInvoices\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TaxInvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make('ข้อมูลใบกำกับภาษี')
                        ->icon('heroicon-o-document-text')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('tax_invoice_number')
                                ->label('เลขที่ใบกำกับภาษี')
                                ->weight('bold')
                                ->size('lg')
                                ->color('primary'),
                            TextEntry::make('document_date')
                                ->label('วันที่เอกสาร')
                                ->date('d/m/Y'),
                            TextEntry::make('company.name')
                                ->label('บริษัทผู้ออก'),
                            TextEntry::make('branch.name')
                                ->label('สาขาผู้ออก'),
                            TextEntry::make('saleOrder.document_no')
                                ->label('อ้างอิงใบสั่งขาย')
                                ->badge()
                                ->color('info'),
                        ]),

                    Section::make('ข้อมูลลูกค้าผู้เอาประกัน/ผู้เสียภาษี')
                        ->icon('heroicon-o-identification')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('customer_name')
                                ->label('ชื่อลูกค้า')
                                ->weight('bold')
                                ->columnSpan(2),
                            TextEntry::make('customer_tax_id')
                                ->label('เลขประจำตัวผู้เสียภาษี'),
                            TextEntry::make('customer_is_head_office')
                                ->label('ประเภทสาขา')
                                ->formatStateUsing(fn ($record) => $record->customer_is_head_office ? 'สำนักงานใหญ่' : 'สาขา: ' . ($record->customer_branch_no ?: '-')),
                            TextEntry::make('customer_address')
                                ->label('ที่อยู่ลูกค้า')
                                ->columnSpan(2),
                        ]),
                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('ยอดเงินและภาษี')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            TextEntry::make('subtotal')
                                ->label('ยอดรวมก่อนหักส่วนลด')
                                ->money('thb'),
                            TextEntry::make('discount_amount')
                                ->label('ส่วนลด')
                                ->money('thb'),
                            TextEntry::make('vat_rate')
                                ->label('อัตราภาษี (%)')
                                ->suffix('%'),
                            TextEntry::make('vat_amount')
                                ->label('ภาษีมูลค่าเพิ่ม')
                                ->money('thb'),
                            TextEntry::make('total_amount')
                                ->label('จำนวนเงินรวมทั้งสิ้น')
                                ->money('thb')
                                ->size('lg')
                                ->weight('bold')
                                ->color('success'),
                        ]),

                    Section::make('สถานะ')
                        ->schema([
                            TextEntry::make('payment_status')
                                ->label('สถานะการชำระเงิน')
                                ->badge(),
                            TextEntry::make('notes')
                                ->label('หมายเหตุ')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
