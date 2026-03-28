<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Company;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขข้อมูลลูกค้าจะส่งผลต่อใบสั่งขายและเอกสารที่เกี่ยวข้องทั้งหมด')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn ($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของลูกค้า')
                    ->icon(Heroicon::UserGroup)
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
                            ->default(fn () => Company::first()?->id)
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('ชื่อลูกค้า')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ชื่อบริษัทหรือชื่อลูกค้า')
                            ->autocomplete(false)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('รหัสลูกค้า')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('รหัสลูกค้าที่ไม่ซ้ำกัน')
                            ->autocomplete(false)
                            ->helperText('รหัสลูกค้าต้องไม่ซ้ำกัน')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เปิดใช้งานลูกค้านี้ในระบบ')
                            ->columnSpanFull(),
                    ]),
                Section::make('ที่อยู่')
                    ->description('ที่อยู่สำหรับจัดส่งและออกเอกสาร')
                    ->icon(Heroicon::MapPin)
                    ->collapsible()
                    ->schema([
                        TextInput::make('address_0')
                            ->label('ที่อยู่ บรรทัดที่ 1')
                            ->maxLength(255)
                            ->placeholder('เลขที่, ชื่ออาคาร, ถนน')
                            ->columnSpanFull(),
                        TextInput::make('address_1')
                            ->label('ที่อยู่ บรรทัดที่ 2')
                            ->maxLength(255)
                            ->placeholder('แขวง/ตำบล')
                            ->columnSpanFull(),
                        TextInput::make('amphoe')
                            ->label('เขต/อำเภอ')
                            ->maxLength(100)
                            ->placeholder('เขต/อำเภอ')
                            ->columnSpan(1),
                        TextInput::make('province')
                            ->label('จังหวัด')
                            ->maxLength(100)
                            ->placeholder('จังหวัด')
                            ->columnSpan(1),
                        TextInput::make('postal_code')
                            ->label('รหัสไปรษณีย์')
                            ->maxLength(10)
                            ->placeholder('10100')
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make('ข้อมูลติดต่อ')
                    ->description('ช่องทางการติดต่อและข้อมูลภาษี')
                    ->icon(Heroicon::Phone)
                    ->collapsible()
                    ->schema([
                        TextInput::make('tel')
                            ->label('เบอร์โทรศัพท์')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('02-xxx-xxxx')
                            ->columnSpan(1),
                        TextInput::make('fax')
                            ->label('แฟกซ์')
                            ->maxLength(20)
                            ->placeholder('02-xxx-xxxx')
                            ->columnSpan(1),
                        TextInput::make('tax_id')
                            ->label('เลขประจำตัวผู้เสียภาษี')
                            ->maxLength(0)
                            ->placeholder('0-0000-00000-00-0')
                            ->helperText('เลขประจำตัวผู้เสียภาษี 13 หลัก')
                            ->columnSpan(1),
                        Toggle::make('is_head_office')
                            ->label('สำนักงานใหญ่')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เลือกถ้าเป็นสำนักงานใหญ่')
                            ->reactive()
                            ->columnSpan(1),
                        TextInput::make('branch_no')
                            ->label('รหัสสาขา')
                            ->maxLength(10)
                            ->placeholder('00001')
                            ->helperText('ระบุรหัสสาขา (ถ้าไม่ใช่สำนักงานใหญ่)')
                            ->hidden(fn ($get) => $get('is_head_office'))
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make('เงื่อนไขการชำระเงิน')
                    ->description('วงเงินเครดิตและเงื่อนไขการชำระเงิน')
                    ->icon(Heroicon::CreditCard)
                    ->collapsible()
                    ->schema([
                        TextInput::make('credit_limit')
                            ->label('วงเงินเครดิต (บาท)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefix('฿')
                            ->placeholder('0')
                            ->helperText('วงเงินเครดิตสูงสุดที่อนุมัติ')
                            ->columnSpan(1),
                        TextInput::make('credit_days')
                            ->label('ระยะเวลาเครดิต (วัน)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('วัน')
                            ->placeholder('0')
                            ->helperText('จำนวนวันที่อนุมัติให้ชำระเงิน')
                            ->columnSpan(1),
                        TextInput::make('vat_rate')
                            ->label('อัตราภาษีมูลค่าเพิ่ม (%)')
                            ->required()
                            ->numeric()
                            ->default(7)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->placeholder('7')
                            ->helperText('อัตราภาษีมูลค่าเพิ่มที่ใช้')
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make('รูปภาพ')
                    ->description('อัพโหลดโลโก้หรือรูปภาพของลูกค้า')
                    ->icon(Heroicon::Photo)
                    ->collapsible()
                    ->collapsed()
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
                            ->directory('customers')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('ขนาดไฟล์สูงสุด 2MB, รองรับ JPG, PNG, WebP')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
