<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขข้อมูลยี่ห้อจะส่งผลต่อสินค้าทั้งหมดที่เชื่อมโยงกับยี่ห้อนี้')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลยี่ห้อ')
                    ->description('ข้อมูลพื้นฐานของยี่ห้อสินค้า')
                    ->icon(Heroicon::Tag)
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('ชื่อยี่ห้อ')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('เช่น Toyota, Honda, Samsung')
                            ->autocomplete(false)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('รหัสยี่ห้อ')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('เช่น TOY, HON, SAM')
                            ->autocomplete(false)
                            ->helperText('รหัสยี่ห้อต้องไม่ซ้ำกัน')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เปิดใช้งานยี่ห้อนี้ในระบบ')
                            ->columnSpanFull(),
                        FileUpload::make('photo_path')
                            ->label('รูปภาพ')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '16:9',
                                '4:3',
                            ])
                            ->directory('brands')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('ขนาดไฟล์สูงสุด 2MB, รองรับ JPG, PNG, WebP')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
