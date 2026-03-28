<?php

namespace App\Filament\Resources\DocumentRunningNumbers\Schemas;

use App\Models\Branch;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DocumentRunningNumberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลเอกสาร')
                    ->description('ตั้งค่าการสร้างเลขที่เอกสารอัตโนมัติ')
                    ->icon(Heroicon::DocumentText)
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('company_id')
                                    ->label('บริษัท')
                                    ->relationship('company', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->placeholder('เลือกบริษัท')
                                    ->default(fn () => Company::first()?->id)
                                    ->columnSpan(1),
                                Select::make('branch_id')
                                    ->label('สาขา')
                                    ->relationship('branch', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->placeholder('เลือกสาขา (ทุกสาขา)')
                                    ->default(fn () => Branch::where('is_headquarter', true)->first()?->id)
                                    ->columnSpan(1),
                            ]),
                    ]),
                Section::make('รูปแบบเลขที่')
                    ->description('กำหนดรูปแบบการสร้างเลขที่เอกสาร')
                    ->icon(Heroicon::Hashtag)
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('document_type')
                                    ->label('ประเภทเอกสาร')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('เช่น sale_order, purchase_order, goods_receipt')
                                    ->helperText('ต้องตรงกับ document_type ในโค้ด')
                                    ->columnSpan(1),
                                TextInput::make('prefix')
                                    ->label('คำนำหน้า')
                                    ->maxLength(20)
                                    ->placeholder('เช่น SO, PO, GR, INV')
                                    ->helperText('คำนำหน้าเลขที่เอกสาร เช่น SO = Sale Order')
                                    ->columnSpan(1),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('date_format')
                                    ->label('รูปแบบวันที่')
                                    ->maxLength(20)
                                    ->placeholder('Ym (202603)')
                                    ->helperText('รูปแบบ PHP date format เช่น Ym จะได้ 202603')
                                    ->columnSpan(1),
                                TextInput::make('running_length')
                                    ->label('จำนวนหลักตัวเลข')
                                    ->required()
                                    ->numeric()
                                    ->default(4)
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->helperText('จำนวนหลักของตัวเลข เช่น 4 จะได้ 0001')
                                    ->columnSpan(1),
                                TextInput::make('current_number')
                                    ->label('รอบเลขที่ปัจจุบัน')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('เลขที่เอกสารล่าสุด ระบบจะเริ่มจากเลขนี้ + 1')
                                    ->columnSpan(1),
                            ]),
                    ]),
                Section::make('ตั้งค่าอื่นๆ')
                    ->icon(Heroicon::Cog6Tooth)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('ปิดใช้งานถ้าไม่ต้องการให้สร้างเลขที่อัตโนมัติ'),
                    ]),
            ]);
    }
}
