<?php

namespace App\Filament\Resources\TaxInvoices\Schemas;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('🧾 ข้อมูลใบกำกับภาษี')
                    ->description('กรอกข้อมูลใบกำกับภาษีเต็มรูปแบบตามกฎหมายภาษีมูลค่าเพิ่ม')
                    ->info()
                    ->columnSpanFull(),
                Section::make('ข้อมูลองค์กร')
                    ->description('เลือกบริษัทและสาขาที่ออกใบกำกับภาษี')
                    ->icon('heroicon-o-building-office')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('เลือกบริษัทที่ออกใบกำกับภาษี')
                            ->placeholder('เลือกบริษัท'),
                        Select::make('branch_id')
                            ->label('สาขา')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('เลือกสาขาที่ออกใบกำกับภาษี (ถ้ามี)')
                            ->placeholder('เลือกสาขา'),
                    ]),
                Section::make('ข้อมูลลูกค้าและใบสั่งขาย')
                    ->description('ระบุลูกค้าและใบสั่งขายอ้างอิง')
                    ->icon('heroicon-o-user-group')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('ลูกค้า')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('เลือกลูกค้าที่ซื้อสินค้า')
                            ->placeholder('เลือกลูกค้า'),
                        Select::make('sale_order_id')
                            ->label('ใบสั่งขายอ้างอิง')
                            ->relationship('saleOrder', 'id')
                            ->searchable()
                            ->preload()
                            ->helperText('เลือกใบสั่งขายที่เกี่ยวข้อง (ถ้ามี)')
                            ->placeholder('เลือกใบสั่งขาย'),
                    ]),
                Section::make('รายละเอียดเอกสาร')
                    ->description('ข้อมูลใบกำกับภาษีและวันที่')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('tax_invoice_number')
                            ->label('เลขที่ใบกำกับภาษี')
                            ->required()
                            ->maxLength(255)
                            ->helperText('ระบบจะสร้างเลขที่อัตโนมัติ')
                            ->placeholder('TAX-XXXXXX'),
                        DatePicker::make('document_date')
                            ->label('วันที่เอกสาร')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('วันที่ออกใบกำกับภาษี')
                            ->default(now()),
                    ]),
                Section::make('ข้อมูลลูกค้าในใบกำกับภาษี')
                    ->description('ข้อมูลลูกค้าที่จะแสดงในใบกำกับภาษี')
                    ->icon('heroicon-o-identification')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('ชื่อลูกค้า')
                            ->required()
                            ->maxLength(255)
                            ->helperText('ชื่อลูกค้าที่แสดงในใบกำกับภาษี')
                            ->placeholder('กรอกชื่อลูกค้า'),
                        TextInput::make('customer_tax_id')
                            ->label('เลขประจำตัวผู้เสียภาษี')
                            ->maxLength(13)
                            ->helperText('เลขประจำตัวผู้เสียภาษี 13 หลัก')
                            ->placeholder('X-XXXX-XXXXX-XX-X'),
                        TextInput::make('customer_address')
                            ->label('ที่อยู่ลูกค้า')
                            ->maxLength(500)
                            ->helperText('ที่อยู่ลูกค้าที่แสดงในใบกำกับภาษี')
                            ->placeholder('กรอกที่อยู่ลูกค้า')
                            ->columnSpanFull(),
                        Toggle::make('customer_is_head_office')
                            ->label('สำนักงานใหญ่')
                            ->required()
                            ->helperText('เลือกถ้าเป็นสำนักงานใหญ่')
                            ->default(true),
                        TextInput::make('customer_branch_no')
                            ->label('รหัสสาขา')
                            ->maxLength(10)
                            ->helperText('กรอกรหัสสาขา (ถ้าไม่ใช่สำนักงานใหญ่)')
                            ->placeholder('00000'),
                    ]),
                Section::make('ข้อมูลการคำนวณ')
                    ->description('ยอดเงินและภาษีมูลค่าเพิ่ม')
                    ->icon('heroicon-o-calculator')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('ยอดรวมก่อนหักส่วนลด')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('฿')
                            ->helperText('ยอดรวมก่อนหักส่วนลด')
                            ->placeholder('0.00'),
                        TextInput::make('discount_amount')
                            ->label('ส่วนลด')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('฿')
                            ->helperText('จำนวนเงินส่วนลด')
                            ->placeholder('0.00'),
                        TextInput::make('vat_rate')
                            ->label('อัตราภาษีมูลค่าเพิ่ม (%)')
                            ->required()
                            ->numeric()
                            ->default(7)
                            ->suffix('%')
                            ->helperText('อัตราภาษีมูลค่าเพิ่ม')
                            ->placeholder('7'),
                        TextInput::make('vat_amount')
                            ->label('ภาษีมูลค่าเพิ่ม')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('฿')
                            ->helperText('จำนวนภาษีมูลค่าเพิ่ม')
                            ->placeholder('0.00'),
                        TextInput::make('total_amount')
                            ->label('จำนวนเงินรวมทั้งสิ้น')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('฿')
                            ->helperText('จำนวนเงินรวมทั้งสิ้น')
                            ->placeholder('0.00')
                            ->columnSpanFull(),
                    ]),
                Section::make('สถานะการชำระเงิน')
                    ->description('สถานะการชำระเงินของใบกำกับภาษี')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible()
                    ->schema([
                        Select::make('payment_status')
                            ->label('สถานะการชำระเงิน')
                            ->options(PaymentStatus::class)
                            ->default('unpaid')
                            ->required()
                            ->helperText('สถานะการชำระเงิน')
                            ->placeholder('เลือกสถานะ'),
                    ]),
                Section::make('หมายเหตุ')
                    ->description('บันทึกข้อมูลเพิ่มเติม')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('หมายเหตุ')
                            ->rows(3)
                            ->helperText('บันทึกข้อมูลเพิ่มเติมเกี่ยวกับใบกำกับภาษี')
                            ->placeholder('กรอกหมายเหตุ (ถ้ามี)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
