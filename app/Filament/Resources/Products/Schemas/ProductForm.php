<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Branch;
use App\Models\Company;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขข้อมูลสินค้าจะส่งผลต่อใบสั่งซื้อ ใบส่งสินค้า และคลังสินค้าทั้งหมด')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของสินค้า')
                    ->icon(Heroicon::Cube)
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->label('ชื่อสินค้า')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ชื่อสินค้า')
                            ->autocomplete(false)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('รหัสสินค้า')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ (PROD-XXXXXX)')
                            ->autocomplete(false)
                            ->helperText('ถ้าไม่ระบุ ระบบจะสร้างรหัสอัตโนมัติให้')
                            ->columnSpan(1),
                        TextInput::make('barcode')
                            ->label('บาร์โค้ด')
                            ->maxLength(50)
                            ->placeholder('บาร์โค้ดสินค้า')
                            ->columnSpan(1),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(50)
                            ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ')
                            ->helperText('ถ้าไม่ระบุ ระบบจะสร้าง SKU อัตโนมัติให้')
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->label('รายละเอียด')
                            ->rows(3)
                            ->placeholder('รายละเอียดเพิ่มเติมของสินค้า')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เปิดใช้งานสินค้านี้ในระบบ')
                            ->columnSpanFull(),
                        Section::make('ราคาและต้นทุน')
                            ->description('ราคาต้นทุนและราคาขาย')
                            ->icon(Heroicon::CurrencyDollar)
                            ->collapsible()
                            ->schema([
                                TextInput::make('cost_price')
                                    ->label('ราคาต้นทุน (บาท)')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->prefix('฿')
                                    ->placeholder('0.00')
                                    ->helperText('ราคาต้นทุนต่อหน่วย')
                                    ->columnSpan(1),
                                TextInput::make('selling_price')
                                    ->label('ราคาขาย (บาท)')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->prefix('฿')
                                    ->placeholder('0.00')
                                    ->helperText('ราคาขายต่อหน่วย')
                                    ->columnSpan(1),
                                TextInput::make('stock_quantity')
                                    ->label('จำนวนสต๊อก')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->placeholder('0')
                                    ->helperText('จำนวนสินค้าคงเหลือในคลัง')
                                    ->columnSpan(1),
                                TextInput::make('min_stock')
                                    ->label('ขั้นต่ำ')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->placeholder('0')
                                    ->helperText('เตือนเมื่อสต๊อกต่ำกว่าค่านี้')
                                    ->columnSpan(1),
                                TextInput::make('max_stock')
                                    ->label('ขั้นสูง')
                                    ->numeric()
                                    ->default(1000)
                                    ->minValue(0)
                                    ->placeholder('1000')
                                    ->helperText('เตือนเมื่อสต๊อกเกินค่านี้')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                        Section::make('หมวดหมู่และข้อมูลอ้างอิง')
                            ->description('หมวดหมู่ ยี่ห้อ และหน่วยนับ')
                            ->icon(Heroicon::Squares2x2)
                            ->collapsible()
                            ->schema([
                                Select::make('category_id')
                                    ->label('หมวดหมู่')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->placeholder('เลือกหมวดหมู่')
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('ชื่อหมวดหมู่')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('code')
                                            ->label('รหัสหมวดหมู่')
                                            ->maxLength(50)
                                            ->unique()
                                            ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ (CAT-XXXXXX)')
                                            ->helperText('ถ้าไม่ระบุ ระบบจะสร้างรหัสอัตโนมัติให้'),
                                        Toggle::make('is_active')
                                            ->label('สถานะการใช้งาน')
                                            ->default(true),
                                    ])
                                    ->createOptionModalHeading('เพิ่มหมวดหมู่ใหม่')
                                    ->columnSpan(1),
                                Select::make('brand_id')
                                    ->label('ยี่ห้อ')
                                    ->relationship('brand', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->placeholder('เลือกยี่ห้อ')
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('ชื่อยี่ห้อ')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('code')
                                            ->label('รหัสยี่ห้อ')
                                            ->maxLength(50)
                                            ->unique()
                                            ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ (BRD-XXXXXX)')
                                            ->helperText('ถ้าไม่ระบุ ระบบจะสร้างรหัสอัตโนมัติให้'),
                                        Toggle::make('is_active')
                                            ->label('สถานะการใช้งาน')
                                            ->default(true),
                                    ])
                                    ->createOptionModalHeading('เพิ่มยี่ห้อใหม่')
                                    ->columnSpan(1),
                                Select::make('unit_id')
                                    ->label('หน่วยนับ')
                                    ->relationship('unit', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->placeholder('เลือกหน่วยนับ')
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('ชื่อหน่วยนับ')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('code')
                                            ->label('รหัสหน่วยนับ')
                                            ->maxLength(50)
                                            ->unique()
                                            ->placeholder('ถ้าไม่ระบุจะสร้างอัตโนมัติ (UNIT-XXXXXX)')
                                            ->helperText('ถ้าไม่ระบุ ระบบจะสร้างรหัสอัตโนมัติให้'),
                                        Toggle::make('is_active')
                                            ->label('สถานะการใช้งาน')
                                            ->default(true),
                                    ])
                                    ->createOptionModalHeading('เพิ่มหน่วยนับใหม่')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('สาขาและบริษัท')
                    ->description('ข้อมูลบริษัทและสาขาที่เป็นเจ้าของสินค้า')
                    ->icon(Heroicon::BuildingOffice2)
                    ->collapsible()
                    ->schema([
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกบริษัท')
                            ->reactive()
                            ->default(fn() => Company::first()?->id)
                            ->columnSpan(1),
                        Select::make('branch_id')
                            ->label('สาขา')
                            ->relationship('branch', 'name', fn($query, $get) => $get('company_id') ? $query->where('company_id', $get('company_id')) : $query)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('เลือกสาขา')
                            ->default(fn() => Branch::where('is_headquarter', true)->first()?->id)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('รูปภาพสินค้า')
                    ->description('อัพโหลดรูปภาพของสินค้า')
                    ->icon(Heroicon::Photo)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('รูปภาพ')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '16:9',
                                '4:3',
                            ])
                            ->directory('products')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('ขนาดไฟล์สูงสุด 2MB, รองรับ JPG, PNG, WebP')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
