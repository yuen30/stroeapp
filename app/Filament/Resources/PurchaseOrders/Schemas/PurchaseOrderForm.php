<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขใบสั่งซื้อจะส่งผลต่อสต็อกสินค้าและรายการรับสินค้า')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของใบสั่งซื้อ')
                    ->icon(Heroicon::DocumentText)
                    ->collapsible()
                    ->schema([
                        DatePicker::make('order_date')
                            ->label('วันที่สั่งซื้อ')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                        DatePicker::make('expected_date')
                            ->label('วันที่คาดว่าจะได้รับ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('วันที่คาดว่าจะได้รับสินค้า')
                            ->columnSpan(1),
                        Select::make('supplier_id')
                            ->label('ผู้จำหน่าย')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกผู้จำหน่าย')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('ชื่อผู้จำหน่าย')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label('รหัสผู้จำหน่าย')
                                    ->maxLength(50)
                                    ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ')
                                    ->helperText('ถ้าไม่ระบุ ระบบจะสร้างรหัสอัตโนมัติให้'),
                                TextInput::make('contact_name')
                                    ->label('ชื่อผู้ติดต่อ')
                                    ->maxLength(255),
                                TextInput::make('tel')
                                    ->label('เบอร์โทรศัพท์')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('tax_id')
                                    ->label('เลขประจำตัวผู้เสียภาษี')
                                    ->maxLength(13),
                                Textarea::make('address_0')
                                    ->label('ที่อยู่')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->createOptionModalHeading('เพิ่มผู้จำหน่ายใหม่')
                            ->columnSpan(1),
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกบริษัท')
                            ->default(fn() => auth()->user()?->company_id)
                            ->reactive()
                            ->columnSpan(1)
                            ->columnStart(1),
                        Select::make('branch_id')
                            ->label('สาขา')
                            ->relationship('branch', 'name', fn($query, $get) =>
                                $get('company_id') ? $query->where('company_id', $get('company_id')) : $query)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกสาขา')
                            ->default(fn() => auth()->user()?->branch_id)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('ข้อมูลเพิ่มเติม')
                    ->description('ข้อมูลการติดต่อและการจัดส่ง')
                    ->icon(Heroicon::InformationCircle)
                    ->collapsible()
                    ->schema([
                        TextInput::make('payment_terms')
                            ->label('เงื่อนไขการชำระเงิน')
                            ->placeholder('เช่น เครดิต 30 วัน, เงินสด')
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('reference_number')
                            ->label('เลขที่อ้างอิง')
                            ->placeholder('เลขที่เอกสารอ้างอิง')
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('contact_person')
                            ->label('ผู้ติดต่อ')
                            ->placeholder('ชื่อผู้ติดต่อ')
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('contact_phone')
                            ->label('เบอร์โทรผู้ติดต่อ')
                            ->tel()
                            ->placeholder('เบอร์โทรศัพท์')
                            ->maxLength(20)
                            ->columnSpan(1),
                        Textarea::make('delivery_address')
                            ->label('ที่อยู่จัดส่ง')
                            ->rows(3)
                            ->placeholder('ที่อยู่สำหรับจัดส่งสินค้า')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\FileUpload::make('attachments')
                            ->label('ไฟล์แนบ')
                            ->multiple()
                            ->disk('public')
                            ->directory('purchase-orders/attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240)
                            ->helperText('รองรับไฟล์: PDF, รูปภาพ, Word, Excel (สูงสุด 10MB)')
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->reorderable()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('หมายเหตุ')
                            ->rows(3)
                            ->placeholder('บันทึกเพิ่มเติมเกี่ยวกับใบสั่งซื้อนี้')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
