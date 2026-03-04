<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('📦 ข้อมูลสต็อกสินค้า')
                    ->description('ข้อมูลสต็อกสินค้าในคลัง ระบบจะอัปเดตอัตโนมัติจากการรับและจ่ายสินค้า')
                    ->info()
                    ->columnSpanFull(),
                Section::make('ข้อมูลสินค้า')
                    ->description('เลือกสินค้าและระบุจำนวน')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->schema([
                        Select::make('product_id')
                            ->label('สินค้า')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('เลือกสินค้าที่ต้องการจัดการสต็อก')
                            ->placeholder('เลือกสินค้า'),
                        TextInput::make('quantity')
                            ->label('จำนวนคงเหลือ')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('จำนวนสินค้าคงเหลือในสต็อก')
                            ->placeholder('0')
                            ->suffix('หน่วย'),
                    ]),
                Section::make('ข้อมูลราคา')
                    ->description('ราคาต้นทุนและราคาขาย')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('ราคาต้นทุน')
                            ->numeric()
                            ->prefix('฿')
                            ->minValue(0)
                            ->helperText('ราคาต้นทุนต่อหน่วย')
                            ->placeholder('0.00'),
                        TextInput::make('selling_price')
                            ->label('ราคาขาย')
                            ->numeric()
                            ->prefix('฿')
                            ->minValue(0)
                            ->helperText('ราคาขายต่อหน่วย')
                            ->placeholder('0.00'),
                    ]),
            ]);
    }
}
