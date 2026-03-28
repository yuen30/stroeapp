<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Roles;
use App\Models\Branch;
use App\Models\Company;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // คำเตือนสำหรับการแก้ไขข้อมูลผู้ใช้
                Callout::make('Warning User Data')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->description('การแก้ไขข้อมูลผู้ใช้จะส่งผลต่อสิทธิ์การเข้าถึงระบบ กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก')
                    ->color(null)
                    ->visible(fn ($operation) => $operation === 'edit')
                    ->columnSpanFull(),
                // รูปโปรไฟล์
                FileUpload::make('profile_photo_path')
                    ->label('รูปโปรไฟล์')
                    ->avatar()
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '1:1',
                    ])
                    ->maxSize(2048)
                    ->helperText('ไฟล์รูปภาพ PNG, JPG หรือ WEBP (สูงสุด 2MB, แนะนำขนาด 1:1)')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                    ->directory('avatars')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->columnSpanFull()
                    ->alignCenter(),
                // ข้อมูลส่วนตัว
                Section::make('ข้อมูลส่วนตัว')
                    ->description('ข้อมูลพื้นฐานของผู้ใช้')
                    ->icon(Heroicon::User)
                    ->schema([
                        TextInput::make('name')
                            ->label('ชื่อ-นามสกุล')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('นายสมชาย ใจดี')
                            ->autofocus()
                            ->columnSpanFull(),
                        TextInput::make('username')
                            ->label('ชื่อผู้ใช้')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('somchai.j')
                            ->helperText('ชื่อผู้ใช้สำหรับเข้าสู่ระบบ (ไม่ซ้ำกัน)')
                            ->alphaDash(),
                        TextInput::make('email')
                            ->label('อีเมล')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('somchai@example.com')
                            ->helperText('อีเมลสำหรับเข้าสู่ระบบและรับการแจ้งเตือน')
                            ->prefixIcon(Heroicon::Envelope),
                        DateTimePicker::make('email_verified_at')
                            ->label('ยืนยันอีเมลเมื่อ')
                            ->helperText('วันที่และเวลาที่ยืนยันอีเมล')
                            ->displayFormat('d/m/Y H:i')
                            ->native(false),
                        // ข้อมูลองค์กร
                        Select::make('company_id')
                            ->label('บริษัท')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('เลือกบริษัทที่ผู้ใช้สังกัด')
                            ->live()
                            ->default(fn () => Company::first()?->id),
                        Select::make('branch_id')
                            ->label('สาขา')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('เลือกสาขาที่ผู้ใช้สังกัด (ถ้ามี)')
                            ->default(fn () => Branch::where('is_headquarter', true)->first()?->id),
                    ])
                    ->columns(2)
                    ->collapsible(),
                // ข้อมูลการเข้าสู่ระบบ
                Section::make('ข้อมูลการเข้าสู่ระบบ')
                    ->description('รหัสผ่านและสิทธิ์การใช้งาน')
                    ->icon(Heroicon::Key)
                    ->schema([
                        TextInput::make('password')
                            ->label('รหัสผ่าน')
                            ->password()
                            ->required(fn ($operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->placeholder('••••••••')
                            ->helperText('รหัสผ่านอย่างน้อย 8 ตัวอักษร')
                            ->revealable()
                            ->hiddenOn('edit'),
                        Select::make('role')
                            ->label('สิทธิ์การใช้งาน')
                            ->options(Roles::class)
                            ->default(Roles::Guest)
                            ->required()
                            ->helperText('กำหนดสิทธิ์การเข้าถึงระบบ')
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('สถานะใช้งาน')
                            ->default(true)
                            ->helperText('เปิด/ปิดการใช้งานผู้ใช้ในระบบ')
                            ->inline(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
