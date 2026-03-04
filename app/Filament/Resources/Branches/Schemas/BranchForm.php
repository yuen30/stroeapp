<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // คำเตือนสำหรับการแก้ไขข้อมูลสาขา
                Callout::make('warning_branch_data')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->description('การแก้ไขข้อมูลสาขาจะส่งผลต่อเอกสารและรายงานทั้งหมดในระบบ กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก')
                    ->color(null)
                    ->visible(fn($operation) => $operation === 'edit')
                    ->columnSpanFull(),
                // ข้อมูลทั่วไป
                Section::make('ข้อมูลทั่วไป')
                    ->description('ข้อมูลพื้นฐานของสาขา')
                    ->icon(Heroicon::BuildingStorefront)
                    ->schema([
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('เลือกบริษัทที่สาขานี้สังกัด')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('ชื่อสาขา')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('สาขากรุงเทพฯ')
                            ->columnSpanFull()
                            ->autofocus(),
                        TextInput::make('code')
                            ->label('รหัสสาขา')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('BR001')
                            ->helperText('รหัสอ้างอิงภายในระบบ (ไม่ซ้ำกัน)')
                            ->alphaDash(),
                        Toggle::make('is_headquarter')
                            ->label('สำนักงานใหญ่')
                            ->default(false)
                            ->helperText('กำหนดว่าสาขานี้เป็นสำนักงานใหญ่หรือไม่')
                            ->inline(false),
                        TextInput::make('tax_id')
                            ->label('เลขประจำตัวผู้เสียภาษี')
                            ->maxLength(13)
                            ->placeholder('0123456789012')
                            ->helperText('เลขประจำตัวผู้เสียภาษี 13 หลัก (ถ้ามี)')
                            ->tel()
                            ->telRegex('/^[0-9]{13}$/'),
                        Toggle::make('is_active')
                            ->label('สถานะใช้งาน')
                            ->default(true)
                            ->helperText('เปิด/ปิดการใช้งานสาขาในระบบ')
                            ->inline(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                // ที่อยู่
                Section::make('ที่อยู่')
                    ->description('ที่อยู่สาขา')
                    ->icon(Heroicon::MapPin)
                    ->schema([
                        TextInput::make('address_0')
                            ->label('ที่อยู่ บรรทัดที่ 1')
                            ->maxLength(255)
                            ->placeholder('เลขที่ 123 ถนนสุขุมวิท')
                            ->columnSpanFull(),
                        TextInput::make('address_1')
                            ->label('ที่อยู่ บรรทัดที่ 2')
                            ->maxLength(255)
                            ->placeholder('แขวงคลองเตย')
                            ->columnSpanFull(),
                        TextInput::make('amphoe')
                            ->label('เขต/อำเภอ')
                            ->maxLength(100)
                            ->placeholder('เขตคลองเตย'),
                        TextInput::make('province')
                            ->label('จังหวัด')
                            ->maxLength(100)
                            ->placeholder('กรุงเทพมหานคร'),
                        TextInput::make('postal_code')
                            ->label('รหัสไปรษณีย์')
                            ->maxLength(5)
                            ->placeholder('10110')
                            ->numeric()
                            ->minLength(5)
                            ->maxLength(5),
                    ])
                    ->columns(2)
                    ->collapsible(),
                // ข้อมูลติดต่อ
                Section::make('ข้อมูลติดต่อ')
                    ->description('ช่องทางการติดต่อ')
                    ->icon(Heroicon::Phone)
                    ->schema([
                        TextInput::make('tel')
                            ->label('เบอร์โทรศัพท์')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('02-123-4567')
                            ->helperText('เบอร์โทรศัพท์สาขา')
                            ->prefixIcon(Heroicon::Phone),
                        TextInput::make('fax')
                            ->label('เบอร์แฟกซ์')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('02-123-4568')
                            ->helperText('เบอร์แฟกซ์ (ถ้ามี)')
                            ->prefixIcon(Heroicon::Printer),
                    ])
                    ->columns(2)
                    ->collapsible(),
                // รูปภาพ
                Section::make('รูปภาพสาขา')
                    ->description('อัปโหลดรูปภาพสาขาสำหรับแสดงในเอกสาร')
                    ->icon(Heroicon::Photo)
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('รูปภาพสาขา')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->helperText('ไฟล์รูปภาพ PNG, JPG หรือ WEBP (สูงสุด 2MB)')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                            ->directory('branches/photos')
                            ->visibility('public')
                            ->imagePreviewHeight('200')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
