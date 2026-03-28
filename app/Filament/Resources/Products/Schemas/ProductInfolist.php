<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\StockMovementType;
use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use App\Filament\Resources\SaleOrders\SaleOrderResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลสินค้า')
                    ->icon(Heroicon::Cube)
                    ->schema([
                        TextEntry::make('code')
                            ->label('รหัสสินค้า')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('name')
                            ->label('ชื่อสินค้า'),
                        TextEntry::make('category.name')
                            ->label('หมวดหมู่'),
                        TextEntry::make('brand.name')
                            ->label('ยี่ห้อ'),
                        TextEntry::make('unit.name')
                            ->label('หน่วยนับ'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('ราคาและสต็อก')
                    ->icon(Heroicon::CurrencyDollar)
                    ->schema([
                        TextEntry::make('cost_price')
                            ->label('ราคาทุน')
                            ->money('THB'),
                        TextEntry::make('selling_price')
                            ->label('ราคาขาย')
                            ->money('THB'),
                        TextEntry::make('stock_quantity')
                            ->label('สต็อกคงเหลือ')
                            ->badge()
                            ->color(fn (int $state): string => $state <= 10 ? 'danger' : ($state <= 30 ? 'warning' : 'success')),
                        TextEntry::make('reserved_quantity')
                            ->label('ถูกจอง')
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('available_stock')
                            ->label('พร้อมจำหน่าย')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('min_stock')
                            ->label('ขั้นต่ำ')
                            ->badge()
                            ->color('gray')
                            ->placeholder('0'),
                        TextEntry::make('max_stock')
                            ->label('ขั้นสูง')
                            ->badge()
                            ->color('gray')
                            ->placeholder('0'),
                    ])
                    ->columns(7)
                    ->columnSpanFull(),
                Section::make('ประวัติการเคลื่อนไหวสต็อก')
                    ->icon(Heroicon::ArrowsRightLeft)
                    ->description('ประวัติการรับและจ่ายสินค้า')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('stockMovements')
                            ->label('')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('วันที่')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('type')
                                    ->label('ประเภท')
                                    ->badge()
                                    ->color(fn (StockMovementType $state): string => match ($state) {
                                        StockMovementType::In => 'success',
                                        StockMovementType::Out => 'danger',
                                    })
                                    ->formatStateUsing(fn (StockMovementType $state): string => match ($state) {
                                        StockMovementType::In => 'รับเข้า',
                                        StockMovementType::Out => 'จ่ายออก',
                                    }),
                                TextEntry::make('goodsReceipt.receipt_number')
                                    ->label('ใบรับสินค้า')
                                    ->color('primary')
                                    ->url(fn ($record) => $record->goods_receipt_id
                                        ? GoodsReceiptResource::getUrl('view', ['record' => $record->goods_receipt_id])
                                        : null),
                                TextEntry::make('saleOrder.invoice_number')
                                    ->label('ใบสั่งขาย')
                                    ->color('primary')
                                    ->url(fn ($record) => $record->sale_order_id
                                        ? SaleOrderResource::getUrl('view', ['record' => $record->sale_order_id])
                                        : null),
                                TextEntry::make('quantity')
                                    ->label('จำนวน')
                                    ->numeric()
                                    ->alignRight()
                                    ->suffix(fn ($record) => $record->type === StockMovementType::In ? '+' : '-'),
                                TextEntry::make('stock_before')
                                    ->label('ยอดก่อน')
                                    ->numeric()
                                    ->alignRight(),
                                TextEntry::make('stock_after')
                                    ->label('ยอดหลัง')
                                    ->numeric()
                                    ->alignRight(),
                                TextEntry::make('notes')
                                    ->label('หมายเหตุ')
                                    ->placeholder('-'),
                                TextEntry::make('creator.name')
                                    ->label('ผู้ทำรายการ'),
                            ])
                            ->state(function ($record) {
                                return $record->stockMovements()
                                    ->with(['goodsReceipt', 'saleOrder', 'creator'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(50)
                                    ->get();
                            })
                            ->columns(9)
                            ->table([
                                TableColumn::make('วันที่'),
                                TableColumn::make('ประเภท'),
                                TableColumn::make('ใบรับสินค้า'),
                                TableColumn::make('ใบสั่งขาย'),
                                TableColumn::make('จำนวน'),
                                TableColumn::make('ยอดก่อน'),
                                TableColumn::make('ยอดหลัง'),
                                TableColumn::make('หมายเหตุ'),
                                TableColumn::make('ผู้ทำรายการ'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('ประวัติการทำรายการ')
                    ->icon(Heroicon::Clock)
                    ->description('บันทึกการเปลี่ยนแปลงข้อมูล')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->label('')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('เวลา')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('causer.name')
                                    ->label('ผู้ทำรายการ')
                                    ->default('System'),
                                TextEntry::make('description')
                                    ->label('การกระทำ')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'สร้างสินค้าใหม่' => 'success',
                                        'แก้ไขสินค้า' => 'warning',
                                        'ลบสินค้า' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('properties')
                                    ->label('รายละเอียด')
                                    ->state(function ($record) {
                                        if ($record->description === 'สร้างสินค้าใหม่') {
                                            return 'สร้างรายการใหม่';
                                        }
                                        if ($record->description === 'ลบสินค้า') {
                                            return 'ลบรายการ';
                                        }

                                        $attributes = $record->properties['attributes'] ?? [];
                                        if (empty($attributes)) {
                                            return '-';
                                        }

                                        return collect($attributes)
                                            ->keys()
                                            ->reject(fn ($key) => in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                                            ->map(fn ($key) => str($key)->headline())
                                            ->filter()
                                            ->implode(', ');
                                    }),
                            ])
                            ->columns(4)
                            ->table([
                                TableColumn::make('เวลา'),
                                TableColumn::make('ผู้ทำรายการ'),
                                TableColumn::make('การกระทำ'),
                                TableColumn::make('รายละเอียด'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
