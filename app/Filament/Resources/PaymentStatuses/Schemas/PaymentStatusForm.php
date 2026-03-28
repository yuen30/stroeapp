<?php

namespace App\Filament\Resources\PaymentStatuses\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PaymentStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลสถานะการชำระเงิน')
                    ->description('ข้อมูลพื้นฐานของสถานะการชำระเงิน')
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('ชื่อสถานะ')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('เช่น รอชำระเงิน, ชำระแล้ว, เกินกำหนด')
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('รหัส')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('เช่น PENDING, PAID, OVERDUE')
                            ->helperText('รหัสนี้ใช้อ้างอิงในระบบ'),
                        ColorPicker::make('color')
                            ->label('สี')
                            ->placeholder('#FF0000')
                            ->helperText('รหัสสี HEX สำหรับแสดงผล (เช่น #22C55E)'),
                        Textarea::make('description')
                            ->label('รายละเอียด')
                            ->rows(2)
                            ->placeholder('รายละเอียดเพิ่มเติม')
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('ลำดับการแสดง')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
