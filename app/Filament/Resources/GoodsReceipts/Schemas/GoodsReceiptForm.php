<?php

namespace App\Filament\Resources\GoodsReceipts\Schemas;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PurchaseOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GoodsReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของใบรับสินค้า')
                    ->icon(Heroicon::DocumentText)
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Select::make('purchase_order_id')
                            ->label('ใบสั่งซื้ออ้างอิง (ไม่บังคับ)')
                            ->relationship(
                                'purchaseOrder',
                                'order_number',
                                fn ($query) => $query->whereIn('status', ['confirmed', 'partially_received'])
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->disabled(fn (callable $get) => ! empty($get('is_standalone')))
                            ->dehydrated(fn (callable $get) => empty($get('is_standalone')))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $po = PurchaseOrder::with(['company', 'branch', 'supplier'])->find($state);
                                    if ($po) {
                                        $set('company_id', $po->company_id);
                                        $set('branch_id', $po->branch_id);
                                        $set('supplier_id', $po->supplier_id);
                                    }
                                }
                            })
                            ->columnSpan(2),
                        Toggle::make('is_standalone')
                            ->label('รับสินค้าแยก (ไม่อ้างอิงใบสั่งซื้อ)')
                            ->default(true)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('purchase_order_id', null);
                                }
                            })
                            ->columnSpan(2),
                        DatePicker::make('document_date')
                            ->label('วันที่รับสินค้า')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->columnSpan(2),
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกบริษัท')
                            ->default(fn () => Company::first()?->id)
                            ->disabled(fn (callable $get) => ! empty($get('purchase_order_id')))
                            ->dehydrated(fn (callable $get) => empty($get('purchase_order_id')))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('ชื่อบริษัท')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label('รหัสบริษัท')
                                    ->maxLength(50)
                                    ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ (COMP-XXXXXX)')
                                    ->helperText('ถ้าไม่ระบุ ระบบจะสร้างรหัสอัตโนมัติให้'),
                                Textarea::make('address_0')
                                    ->label('ที่อยู่')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('tel')
                                    ->label('โทรศัพท์')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('tax_id')
                                    ->label('เลขประจำตัวผู้เสียภาษี')
                                    ->maxLength(13),
                                Toggle::make('is_active')
                                    ->label('สถานะการใช้งาน')
                                    ->default(true),
                            ])
                            ->createOptionModalHeading('เพิ่มบริษัทใหม่')
                            ->columnSpan(2),
                        Select::make('branch_id')
                            ->label('สาขา')
                            ->relationship('branch', 'name', fn ($query, $get) => $get('company_id') ? $query->where('company_id', $get('company_id')) : $query)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกสาขา')
                            ->default(fn () => Branch::where('is_headquarter', true)->first()?->id)
                            ->disabled(fn (callable $get) => ! empty($get('purchase_order_id')))
                            ->dehydrated(fn (callable $get) => empty($get('purchase_order_id')))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('ชื่อสาขา')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label('รหัสสาขา')
                                    ->maxLength(50)
                                    ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ (BR-XXXXXX)')
                                    ->helperText('ถ้าไม่ระบุ ระบบจะสร้างรหัสอัตโนมัติให้'),
                                Toggle::make('is_headquarter')
                                    ->label('สำนักงานใหญ่')
                                    ->default(false),
                                Textarea::make('address_0')
                                    ->label('ที่อยู่')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('tel')
                                    ->label('โทรศัพท์')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('tax_id')
                                    ->label('เลขประจำตัวผู้เสียภาษี')
                                    ->maxLength(13),
                                Toggle::make('is_active')
                                    ->label('สถานะการใช้งาน')
                                    ->default(true),
                            ])
                            ->createOptionModalHeading('เพิ่มสาขาใหม่')
                            ->columnSpan(2),
                        Select::make('supplier_id')
                            ->label('ผู้จำหน่าย')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกผู้จำหน่าย')
                            ->disabled(fn (callable $get) => ! empty($get('purchase_order_id')))
                            ->dehydrated(fn (callable $get) => empty($get('purchase_order_id')))
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
                                Textarea::make('address_0')
                                    ->label('ที่อยู่')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('tax_id')
                                    ->label('เลขประจำตัวผู้เสียภาษี')
                                    ->maxLength(13),
                                Toggle::make('is_active')
                                    ->label('สถานะการใช้งาน')
                                    ->default(true),
                            ])
                            ->createOptionModalHeading('เพิ่มผู้จำหน่ายใหม่')
                            ->columnSpan(2),
                        TextInput::make('supplier_delivery_no')
                            ->label('เลขที่ใบส่งของผู้จำหน่าย')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => $state ? strtoupper($state) : $state)
                            ->columnSpan(2),
                    ]),
                Section::make('หมายเหตุและไฟล์แนบ')
                    ->schema([
                        Textarea::make('notes')
                            ->label('หมายเหตุ')
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('attachments')
                            ->label('ไฟล์แนบ')
                            ->multiple()
                            ->maxFiles(10)
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->directory('goods-receipts/attachments')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
