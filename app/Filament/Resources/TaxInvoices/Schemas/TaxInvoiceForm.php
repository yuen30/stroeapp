<?php

namespace App\Filament\Resources\TaxInvoices\Schemas;

use App\Enums\PaymentStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\SaleOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Callout คำเตือนสำหรับหน้า Edit
                Callout::make('edit_warning')
                    ->visible(fn ($livewire) => $livewire instanceof EditRecord)
                    ->warning()
                    ->icon('heroicon-o-exclamation-triangle')
                    ->heading('⚠️ คำเตือน')
                    ->description('การแก้ไขใบกำกับภาษีอาจส่งผลต่อการรายงานภาษี กรุณาตรวจสอบข้อมูลให้ถูกต้องตามกฎหมายก่อนบันทึก')
                    ->columnSpanFull(),
                // Callout แสดงข้อมูล Sale Order (ถ้ามี)
                Callout::make('sale_order_info')
                    ->visible(fn ($get) => ! empty($get('sale_order_id')))
                    ->success()
                    ->icon('heroicon-o-check-circle')
                    ->heading('✅ เชื่อมโยงกับใบสั่งขาย')
                    ->description(function ($get) {
                        $saleOrderId = $get('sale_order_id');
                        if (! $saleOrderId) {
                            return '';
                        }

                        $saleOrder = SaleOrder::find($saleOrderId);
                        if (! $saleOrder) {
                            return '';
                        }

                        return "ใบสั่งขายเลขที่: {$saleOrder->invoice_number} | ลูกค้า: {$saleOrder->customer->name} | ยอดรวม: ".number_format($saleOrder->total_amount, 2).' ฿';
                    })
                    ->columnSpanFull(),
                // Layout หลัก: Flex แบบ 2 คอลัมน์
                Flex::make([
                    // คอลัมน์ซ้าย: ข้อมูลหลัก
                    Grid::make(1)
                        ->schema([
                            // ข้อมูลเอกสาร
                            Section::make('ข้อมูลเอกสาร')
                                ->icon('heroicon-o-document-text')
                                ->description('เลขที่และวันที่ใบกำกับภาษี')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('tax_invoice_number')
                                        ->label('เลขที่ใบกำกับภาษี')
                                        ->placeholder('ถ้าไม่ระบุ ระบบจะสร้างอัตโนมัติ')
                                        ->maxLength(50)
                                        ->unique(ignoreRecord: true)
                                        ->helperText('สามารถระบุเลขที่เองได้ หรือปล่อยว่างให้ระบบสร้างอัตโนมัติ'),
                                    DatePicker::make('document_date')
                                        ->label('วันที่เอกสาร')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d/m/Y')
                                        ->default(now()),
                                ]),
                            // ข้อมูลองค์กรและลูกค้า
                            Section::make('ข้อมูลองค์กรและลูกค้า')
                                ->icon('heroicon-o-building-office-2')
                                ->description(fn ($get) => ! empty($get('sale_order_id'))
                                    ? '✅ ดึงข้อมูลจากใบสั่งขายอัตโนมัติ'
                                    : 'เลือกบริษัท สาขา และลูกค้า')
                                ->columns(2)
                                ->schema([
                                    Select::make('company_id')
                                        ->label('บริษัท')
                                        ->relationship('company', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->disabled(fn ($get) => ! empty($get('sale_order_id')))
                                        ->dehydrated()
                                        ->placeholder('เลือกบริษัท')
                                        ->default(fn () => Company::first()?->id)
                                        ->columnSpan(1),
                                    Select::make('branch_id')
                                        ->label('สาขา')
                                        ->relationship('branch', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->disabled(fn ($get) => ! empty($get('sale_order_id')))
                                        ->dehydrated()
                                        ->placeholder('เลือกสาขา')
                                        ->default(fn () => Branch::where('is_headquarter', true)->first()?->id)
                                        ->columnSpan(1),
                                    Select::make('customer_id')
                                        ->label('ลูกค้า')
                                        ->relationship('customer', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->disabled(fn ($get) => ! empty($get('sale_order_id')))
                                        ->dehydrated()
                                        ->placeholder('เลือกลูกค้า')
                                        ->columnSpan(1),
                                    Select::make('sale_order_id')
                                        ->label('ใบสั่งขายอ้างอิง')
                                        ->relationship('saleOrder', 'invoice_number')
                                        ->searchable()
                                        ->preload()
                                        ->disabled(fn ($get) => ! empty($get('sale_order_id')))
                                        ->dehydrated()
                                        ->placeholder('เลือกใบสั่งขาย')
                                        ->columnSpan(1),
                                ]),
                            // ข้อมูลลูกค้าในใบกำกับภาษี
                            Section::make('ข้อมูลลูกค้าในใบกำกับภาษี')
                                ->icon('heroicon-o-identification')
                                ->description('ข้อมูลที่จะแสดงในใบกำกับภาษี')
                                ->collapsible()
                                ->columns(2)
                                ->schema([
                                    TextInput::make('customer_name')
                                        ->label('ชื่อลูกค้า')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('กรอกชื่อลูกค้า')
                                        ->columnSpan(1),
                                    TextInput::make('customer_tax_id')
                                        ->label('เลขประจำตัวผู้เสียภาษี')
                                        ->maxLength(13)
                                        ->placeholder('X-XXXX-XXXXX-XX-X')
                                        ->columnSpan(1),
                                    TextInput::make('customer_address_line1')
                                        ->label('ที่อยู่ (บรรทัดที่ 1)')
                                        ->maxLength(500)
                                        ->placeholder('เลขที่ ถนน')
                                        ->columnSpanFull(),
                                    TextInput::make('customer_address_line2')
                                        ->label('ที่อยู่ (บรรทัดที่ 2)')
                                        ->maxLength(500)
                                        ->placeholder('ตำบล/แขวง')
                                        ->columnSpanFull(),
                                    TextInput::make('customer_amphoe')
                                        ->label('อำเภอ/เขต')
                                        ->maxLength(100)
                                        ->placeholder('กรอกอำเภอ/เขต')
                                        ->columnSpan(1),
                                    TextInput::make('customer_province')
                                        ->label('จังหวัด')
                                        ->maxLength(100)
                                        ->placeholder('กรอกจังหวัด')
                                        ->columnSpan(1),
                                    TextInput::make('customer_postal_code')
                                        ->label('รหัสไปรษณีย์')
                                        ->maxLength(5)
                                        ->placeholder('XXXXX')
                                        ->columnSpan(1),
                                    Toggle::make('customer_is_head_office')
                                        ->label('สำนักงานใหญ่')
                                        ->default(true)
                                        ->inline(false)
                                        ->columnSpan(1),
                                    TextInput::make('customer_branch_no')
                                        ->label('รหัสสาขา')
                                        ->maxLength(10)
                                        ->placeholder('00000')
                                        ->helperText('กรอกถ้าไม่ใช่สำนักงานใหญ่')
                                        ->columnSpan(1),
                                ]),
                            // หมายเหตุ
                            Section::make('หมายเหตุ')
                                ->icon('heroicon-o-pencil-square')
                                ->description('บันทึกข้อมูลเพิ่มเติม')
                                ->collapsible()
                                ->collapsed()
                                ->schema([
                                    Textarea::make('notes')
                                        ->label('หมายเหตุ')
                                        ->rows(3)
                                        ->placeholder('กรอกหมายเหตุ (ถ้ามี)')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    // คอลัมน์ขวา: ข้อมูลการเงิน (Sidebar)
                    Grid::make(1)
                        ->schema([
                            // ข้อมูลการคำนวณ
                            Section::make('ยอดเงิน')
                                ->icon('heroicon-o-calculator')
                                ->description(fn ($get) => ! empty($get('sale_order_id'))
                                    ? '✅ ดึงจากใบสั่งขาย (สามารถแก้ไขได้)'
                                    : 'กรอกยอดเงิน')
                                ->schema([
                                    TextInput::make('subtotal')
                                        ->label('ยอดรวมก่อนหักส่วนลด')
                                        ->required()
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->default(0)
                                        ->prefix('฿')
                                        ->placeholder('0.00')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::calculateTotals($set, $get))
                                        ->extraInputAttributes(['class' => 'text-right']),
                                    TextInput::make('discount_amount')
                                        ->label('ส่วนลด')
                                        ->required()
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->default(0)
                                        ->prefix('฿')
                                        ->placeholder('0.00')
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set, callable $get) => self::calculateTotals($set, $get))
                                        ->extraInputAttributes(['class' => 'text-right']),
                                    TextInput::make('vat_rate')
                                        ->label('อัตราภาษีมูลค่าเพิ่ม')
                                        ->required()
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->default(7)
                                        ->suffix('%')
                                        ->placeholder('7')
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set, callable $get) => self::calculateTotals($set, $get))
                                        ->extraInputAttributes(['class' => 'text-right']),
                                    TextInput::make('vat_amount')
                                        ->label('ภาษีมูลค่าเพิ่ม')
                                        ->required()
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->default(0)
                                        ->prefix('฿')
                                        ->placeholder('0.00')
                                        ->disabled()
                                        ->dehydrated()
                                        ->extraInputAttributes(['class' => 'text-right'])
                                        ->columnSpanFull(),
                                    TextInput::make('total_amount')
                                        ->label('จำนวนเงินรวมทั้งสิ้น')
                                        ->required()
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->default(0)
                                        ->prefix('฿')
                                        ->placeholder('0.00')
                                        ->disabled()
                                        ->dehydrated()
                                        ->extraInputAttributes(['class' => 'text-right text-2xl font-bold text-primary-600 dark:text-primary-400'])
                                        ->columnSpanFull()
                                        ->extraAttributes(['style' => 'margin-top: 1rem; padding-top: 1rem; border-top: 2px solid rgb(229 231 235 / 1);']),
                                ]),
                            // สถานะการชำระเงิน
                            Section::make('สถานะการชำระเงิน')
                                ->icon('heroicon-o-banknotes')
                                ->description('สถานะการชำระเงิน')
                                ->schema([
                                    Select::make('payment_status')
                                        ->label('สถานะการชำระเงิน')
                                        ->options(PaymentStatus::class)
                                        ->default('unpaid')
                                        ->required()
                                        ->native(false)
                                        ->placeholder('เลือกสถานะ'),
                                ]),
                        ])
                        ->grow(false),  // Sidebar ไม่ขยาย
                ])
                    ->from('lg')  // แยกเป็น 2 คอลัมน์ตั้งแต่ lg breakpoint
                    ->columnSpanFull(),
            ]);
    }

    private static function calculateTotals(callable $set, callable $get): void
    {
        $subtotal = (float) ($get('subtotal') ?: 0);
        $discountAmount = (float) ($get('discount_amount') ?: 0);
        $vatRate = (float) ($get('vat_rate') ?: 7);

        // คำนวณยอดหลังหักส่วนลด
        $afterDiscount = max(0, $subtotal - $discountAmount);

        // คำนวณ VAT
        $vatAmount = $afterDiscount * ($vatRate / 100);

        // คำนวณยอดรวมทั้งสิ้น
        $totalAmount = $afterDiscount + $vatAmount;

        $set('vat_amount', round($vatAmount, 2));
        $set('total_amount', round($totalAmount, 2));
    }
}
