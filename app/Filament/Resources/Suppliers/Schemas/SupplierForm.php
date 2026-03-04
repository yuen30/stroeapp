<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขข้อมูลผู้จัดจำหน่ายจะส่งผลต่อใบสั่งซื้อและเอกสารที่เกี่ยวข้องทั้งหมด')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของผู้จัดจำหน่าย')
                    ->icon(Heroicon::Truck)
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
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('ชื่อผู้จัดจำหน่าย')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ชื่อบริษัทหรือชื่อผู้จัดจำหน่าย')
                            ->autocomplete(false)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('รหัสผู้จัดจำหน่าย')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('รหัสผู้จัดจำหน่ายที่ไม่ซ้ำกัน')
                            ->autocomplete(false)
                            ->helperText('รหัสผู้จัดจำหน่ายต้องไม่ซ้ำกัน')
                            ->columnSpanFull(),
                        TextInput::make('contact_name')
                            ->label('ชื่อผู้ติดต่อ')
                            ->maxLength(255)
                            ->placeholder('ชื่อผู้ติดต่อหลัก')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เปิดใช้งานผู้จัดจำหน่ายนี้ในระบบ')
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
                            ->maxLength(13)
                            ->placeholder('0-0000-00000-00-0')
                            ->helperText('เลขประจำตัวผู้เสียภาษี 13 หลัก')
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                Section::make('รูปภาพ')
                    ->description('อัพโหลดโลโก้หรือรูปภาพของผู้จัดจำหน่าย')
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
                            ->directory('suppliers')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('ขนาดไฟล์สูงสุด 2MB, รองรับ JPG, PNG, WebP')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
