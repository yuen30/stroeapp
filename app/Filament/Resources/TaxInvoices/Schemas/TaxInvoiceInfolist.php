<?php

namespace App\Filament\Resources\TaxInvoices\Schemas;

use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class TaxInvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Header: เลขที่เอกสารและสถานะ
                Callout::make('header')
                    ->success()
                    ->icon('heroicon-o-document-check')
                    ->heading(fn($record) => "ใบกำกับภาษีเลขที่: {$record->tax_invoice_number}")
                    ->description(fn($record) => "วันที่: {$record->document_date->format('d/m/Y')} | สถานะ: {$record->payment_status->getLabel()}")
                    ->columnSpanFull(),
                // Layout หลัก: Flex 2 คอลัมน์
                Flex::make([
                    // คอลัมน์ซ้าย: ข้อมูลหลัก
                    Grid::make(1)
                        ->schema([
                            // ข้อมูลเอกสาร
                            Section::make('ข้อมูลเอกสาร')
                                ->icon('heroicon-o-document-text')
                                ->description('รายละเอียดใบกำกับภาษี')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('tax_invoice_number')
                                        ->label('เลขที่ใบกำกับภาษี')
                                        ->icon('heroicon-o-hashtag')
                                        ->weight('bold')
                                        ->color('primary')
                                        ->copyable()
                                        ->copyMessage('คัดลอกแล้ว!')
                                        ->copyMessageDuration(1500),
                                    TextEntry::make('document_date')
                                        ->label('วันที่เอกสาร')
                                        ->icon('heroicon-o-calendar')
                                        ->date('d/m/Y'),
                                    TextEntry::make('company.name')
                                        ->label('บริษัทผู้ออก')
                                        ->icon('heroicon-o-building-office')
                                        ->weight('semibold'),
                                    TextEntry::make('branch.name')
                                        ->label('สาขาผู้ออก')
                                        ->icon('heroicon-o-building-storefront')
                                        ->placeholder('สำนักงานใหญ่')
                                        ->default('-'),
                                    TextEntry::make('saleOrder.invoice_number')
                                        ->label('ใบส่งสินค้าอ้างอิง')
                                        ->icon('heroicon-o-shopping-bag')
                                        ->badge()
                                        ->color('info')
                                        ->placeholder('ไม่มี')
                                        ->columnSpan(2),
                                ]),
                            // ข้อมูลลูกค้า
                            Section::make('ข้อมูลลูกค้า/ผู้เสียภาษี')
                                ->icon('heroicon-o-user-circle')
                                ->description('ข้อมูลที่แสดงในใบกำกับภาษี')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('customer.name')
                                        ->label('ลูกค้า (Master Data)')
                                        ->icon('heroicon-o-user')
                                        ->badge()
                                        ->color('gray')
                                        ->columnSpan(2),
                                    TextEntry::make('customer_name')
                                        ->label('ชื่อในใบกำกับภาษี')
                                        ->icon('heroicon-o-identification')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->columnSpan(2),
                                    TextEntry::make('customer_tax_id')
                                        ->label('เลขประจำตัวผู้เสียภาษี')
                                        ->icon('heroicon-o-hashtag')
                                        ->placeholder('ไม่ระบุ')
                                        ->copyable(),
                                    TextEntry::make('customer_is_head_office')
                                        ->label('ประเภทสาขา')
                                        ->badge()
                                        ->formatStateUsing(fn($record) => $record->customer_is_head_office
                                            ? 'สำนักงานใหญ่'
                                            : 'สาขา: ' . ($record->customer_branch_no ?: '-'))
                                        ->color(fn($record) => $record->customer_is_head_office ? 'success' : 'warning')
                                        ->icon(fn($record) => $record->customer_is_head_office
                                            ? 'heroicon-o-building-office'
                                            : 'heroicon-o-building-storefront'),
                                ]),
                            // ที่อยู่ลูกค้า
                            Fieldset::make('ที่อยู่ลูกค้า')
                                ->columns(1)
                                ->schema([
                                    TextEntry::make('customer_address_line1')
                                        ->label('ที่อยู่ (บรรทัดที่ 1)')
                                        ->icon('heroicon-o-map-pin')
                                        ->placeholder('-'),
                                    TextEntry::make('customer_address_line2')
                                        ->label('ที่อยู่ (บรรทัดที่ 2)')
                                        ->placeholder('-'),
                                    Grid::make(3)
                                        ->schema([
                                            TextEntry::make('customer_amphoe')
                                                ->label('อำเภอ/เขต')
                                                ->placeholder('-'),
                                            TextEntry::make('customer_province')
                                                ->label('จังหวัด')
                                                ->placeholder('-'),
                                            TextEntry::make('customer_postal_code')
                                                ->label('รหัสไปรษณีย์')
                                                ->placeholder('-'),
                                        ]),
                                ]),
                            // รายการสินค้า (ถ้ามี Sale Order)
                            Section::make('รายการสินค้า')
                                ->icon('heroicon-o-shopping-cart')
                                ->description('รายการสินค้าจากใบส่งสินค้า')
                                ->visible(fn($record) => $record->sale_order_id !== null)
                                ->schema([
                                    RepeatableEntry::make('saleOrder.items')
                                        ->hiddenLabel()
                                        ->table([
                                            TableColumn::make('สินค้า')
                                                ->width('40%'),
                                            TableColumn::make('จำนวน')
                                                ->alignment(Alignment::Center)
                                                ->width('15%'),
                                            TableColumn::make('ราคา/หน่วย')
                                                ->alignment(Alignment::End)
                                                ->width('20%'),
                                            TableColumn::make('รวม')
                                                ->alignment(Alignment::End)
                                                ->width('25%'),
                                        ])
                                        ->schema([
                                            TextEntry::make('product.name')
                                                ->weight('semibold'),
                                            TextEntry::make('quantity')
                                                ->badge()
                                                ->color('gray')
                                                ->formatStateUsing(fn($record) =>
                                                    $record->quantity . ' ' . ($record->product->unit->name ?? 'หน่วย')),
                                            TextEntry::make('unit_price')
                                                ->money('THB', locale: 'th'),
                                            TextEntry::make('total_price')
                                                ->money('THB', locale: 'th')
                                                ->weight('bold')
                                                ->color('success'),
                                        ]),
                                ]),
                        ]),
                    // คอลัมน์ขวา: Sidebar (ยอดเงินและสถานะ)
                    Grid::make(1)
                        ->schema([
                            // ยอดเงิน
                            Section::make('สรุปยอดเงิน')
                                ->icon('heroicon-o-calculator')
                                ->description('รายละเอียดการคำนวณ')
                                ->schema([
                                    TextEntry::make('subtotal')
                                        ->label('ยอดรวมก่อนหักส่วนลด')
                                        ->money('THB', locale: 'th')
                                        ->icon('heroicon-o-banknotes'),
                                    TextEntry::make('discount_amount')
                                        ->label('ส่วนลด')
                                        ->money('THB', locale: 'th')
                                        ->icon('heroicon-o-receipt-percent')
                                        ->color('danger'),
                                    TextEntry::make('after_discount')
                                        ->label('ยอดหลังหักส่วนลด')
                                        ->money('THB', locale: 'th')
                                        ->state(fn($record) => $record->subtotal - $record->discount_amount)
                                        ->weight('semibold'),
                                    TextEntry::make('vat_rate')
                                        ->label('อัตราภาษี')
                                        ->suffix('%')
                                        ->icon('heroicon-o-calculator'),
                                    TextEntry::make('vat_amount')
                                        ->label('ภาษีมูลค่าเพิ่ม')
                                        ->money('THB', locale: 'th')
                                        ->icon('heroicon-o-document-currency-dollar')
                                        ->color('warning'),
                                    TextEntry::make('total_amount')
                                        ->label('จำนวนเงินรวมทั้งสิ้น')
                                        ->money('THB', locale: 'th')
                                        ->size('xl')
                                        ->weight('bold')
                                        ->color('success')
                                        ->icon('heroicon-o-currency-dollar'),
                                ]),
                            // สถานะและข้อมูลเพิ่มเติม
                            Section::make('สถานะและข้อมูลเพิ่มเติม')
                                ->icon('heroicon-o-information-circle')
                                ->schema([
                                    TextEntry::make('payment_status')
                                        ->label('สถานะการชำระเงิน')
                                        ->badge()
                                        ->size('lg'),
                                    TextEntry::make('notes')
                                        ->label('หมายเหตุ')
                                        ->icon('heroicon-o-pencil-square')
                                        ->placeholder('ไม่มีหมายเหตุ')
                                        ->columnSpanFull(),
                                ]),
                            // ข้อมูลระบบ
                            Fieldset::make('ข้อมูลระบบ')
                                ->columns(1)
                                ->schema([
                                    TextEntry::make('creator.name')
                                        ->label('ผู้สร้าง')
                                        ->icon('heroicon-o-user')
                                        ->badge()
                                        ->color('gray'),
                                    TextEntry::make('created_at')
                                        ->label('วันที่สร้าง')
                                        ->dateTime('d/m/Y H:i')
                                        ->icon('heroicon-o-clock'),
                                    TextEntry::make('updated_at')
                                        ->label('แก้ไขล่าสุด')
                                        ->dateTime('d/m/Y H:i')
                                        ->icon('heroicon-o-pencil'),
                                ]),
                        ])
                        ->grow(false),
                ])
                    ->from('lg')
                    ->columnSpanFull(),
            ]);
    }
}
