<?php

namespace App\Filament\Resources\DocumentRunningNumbers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DocumentRunningNumberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลทั่วไป')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('document_type')
                                    ->label('ประเภทเอกสาร')
                                    ->icon(Heroicon::Tag)
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('prefix')
                                    ->label('คำนำหน้า')
                                    ->icon(Heroicon::Hashtag),
                                TextEntry::make('date_format')
                                    ->label('รูปแบบวันที่')
                                    ->icon(Heroicon::Calendar)
                                    ->placeholder('-'),
                            ]),
                    ]),
                Section::make('การตั้งค่าตัวเลข')
                    ->icon(Heroicon::NumberedList)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('running_length')
                                    ->label('จำนวนหลัก')
                                    ->icon(Heroicon::AdjustmentsHorizontal)
                                    ->numeric(),
                                TextEntry::make('current_number')
                                    ->label('รอบเลขที่ปัจจุบัน')
                                    ->icon(Heroicon::Sparkles)
                                    ->numeric(),
                                IconEntry::make('is_active')
                                    ->label('สถานะ')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),
                    ]),
                Section::make('ข้อมูลอ้างอิง')
                    ->icon(Heroicon::BuildingOffice2)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('company.name')
                                    ->label('บริษัท')
                                    ->icon(Heroicon::BuildingOffice2)
                                    ->placeholder('-'),
                                TextEntry::make('branch.name')
                                    ->label('สาขา')
                                    ->icon(Heroicon::BuildingStorefront)
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make('ข้อมูลระบบ')
                    ->icon(Heroicon::Clock)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('วันที่สร้าง')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('แก้ไขล่าสุด')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
