<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขข้อมูลหน่วยนับจะส่งผลต่อสินค้าทั้งหมดที่เชื่อมโยงกับหน่วยนับนี้')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลหน่วยนับ')
                    ->description('ข้อมูลพื้นฐานของหน่วยนับสินค้า')
                    ->icon(Heroicon::Scale)
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('รหัสหน่วยนับ')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('เช่น PCS, BOX, PACK, CTN')
                            ->autocomplete(false)
                            ->helperText('รหัสหน่วยนับต้องไม่ซ้ำกัน'),
                        TextInput::make('name')
                            ->label('ชื่อหน่วยนับ')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('เช่น ชิ้น, กล่อง, แพ็ค, ลัง')
                            ->autocomplete(false),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เปิดใช้งานหน่วยนับนี้ในระบบ')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
