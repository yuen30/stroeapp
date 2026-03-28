<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('คำเตือน')
                    ->description('การแก้ไขข้อมูลวิธีการชำระเงินอาจส่งผลต่อเอกสารที่ใช้วิธีการชำระเงินนี้')
                    ->warning()
                    ->icon(Heroicon::ExclamationTriangle)
                    ->visible(fn ($context) => $context === 'edit')
                    ->columnSpanFull(),
                Section::make('ข้อมูลวิธีการชำระเงิน')
                    ->description('ข้อมูลพื้นฐานของวิธีการชำระเงิน')
                    ->icon(Heroicon::CreditCard)
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('ชื่อวิธีการชำระเงิน')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('เช่น เงินสด, บัตรเครดิต, โอนเงิน')
                            ->autocomplete(false)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('รหัส')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('เช่น CASH, CARD, BANK')
                            ->autocomplete(false)
                            ->helperText('รหัสนี้ใช้อ้างอิงในระบบ'),
                        TextInput::make('sort_order')
                            ->label('ลำดับการแสดง')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Textarea::make('description')
                            ->label('รายละเอียด')
                            ->rows(2)
                            ->placeholder('รายละเอียดเพิ่มเติม')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('สถานะการใช้งาน')
                            ->default(true)
                            ->inline(false)
                            ->helperText('เปิดใช้งานวิธีการชำระเงินนี้ในระบบ'),
                    ]),
            ]);
    }
}
