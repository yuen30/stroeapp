<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขข้อมูลหมวดหมู่จะส่งผลต่อสินค้าทั้งหมดที่เชื่อมโยงกับหมวดหมู่นี้')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลหมวดหมู่')
                    ->description('ข้อมูลพื้นฐานของหมวดหมู่สินค้า')
                    ->icon(Heroicon::Squares2x2)
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('รหัสหมวดหมู่')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('เช่น ELEC, APPL')
                            ->autocomplete(false)
                            ->helperText('รหัสหมวดหมู่ต้องไม่ซ้ำกัน'),
                        Select::make('parent_id')
                            ->label('หมวดหมู่หลัก')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('เลือกหมวดหมู่หลัก (ถ้ามี)')
                            ->helperText('เลือกหมวดหมู่หลักเพื่อสร้างหมวดหมู่ย่อย')
                            ->native(false)
                            ->columnStart(1),
                        TextInput::make('name')
                            ->label('ชื่อหมวดหมู่')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('เช่น อิเล็กทรอนิกส์, เครื่องใช้ไฟฟ้า')
                            ->autocomplete(false),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เปิดใช้งานหมวดหมู่นี้ในระบบ')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
