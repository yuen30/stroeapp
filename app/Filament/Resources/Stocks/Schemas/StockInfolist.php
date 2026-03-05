<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class StockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('ข้อมูลสต๊อกสินค้า')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('สินค้า')
                                    ->icon(Heroicon::Cube)
                                    ->columnSpan(2),
                                TextEntry::make('product.code')
                                    ->label('รหัสสินค้า')
                                    ->icon(Heroicon::Hashtag),
                                TextEntry::make('branch.name')
                                    ->label('สาขา')
                                    ->icon(Heroicon::BuildingStorefront),
                                TextEntry::make('quantity')
                                    ->label('จำนวนคงเหลือ')
                                    ->icon(Heroicon::ArchiveBox)
                                    ->badge()
                                    ->color(fn($record) => $record->product->isLowStock() ? 'danger' : 'success')
                                    ->formatStateUsing(fn($state, $record) => number_format($state) . ' ' . $record->product->unit->name),
                                TextEntry::make('product.min_stock')
                                    ->label('สต๊อกขั้นต่ำ')
                                    ->icon(Heroicon::ArrowTrendingDown)
                                    ->formatStateUsing(fn($state, $record) => $state > 0 ? number_format($state) . ' ' . $record->product->unit->name : '-'),
                                TextEntry::make('product.max_stock')
                                    ->label('สต๊อกสูงสุด')
                                    ->icon(Heroicon::ArrowTrendingUp)
                                    ->formatStateUsing(fn($state, $record) => $state > 0 ? number_format($state) . ' ' . $record->product->unit->name : '-'),
                                TextEntry::make('cost_price')
                                    ->label('ราคาทุน')
                                    ->icon(Heroicon::Banknotes)
                                    ->money('THB'),
                                TextEntry::make('selling_price')
                                    ->label('ราคาขาย')
                                    ->icon(Heroicon::CurrencyDollar)
                                    ->money('THB'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make('ประวัติการเคลื่อนไหว Stock')
                    ->description('แสดงประวัติการเข้า-ออกของสินค้าในสต๊อก')
                    ->icon(Heroicon::Clock)
                    ->schema([
                        RepeatableEntry::make('product.stockMovements')
                            ->label('')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label('วันที่')
                                            ->dateTime('d/m/Y H:i')
                                            ->icon(Heroicon::Calendar),
                                        TextEntry::make('type')
                                            ->label('ประเภท')
                                            ->badge()
                                            ->icon(fn($state) => $state->getIcon()),
                                        TextEntry::make('quantity')
                                            ->label('จำนวน')
                                            ->formatStateUsing(fn($state, $record) =>
                                                ($record->type->value === 'in' ? '+' : '-') . number_format($state))
                                            ->color(fn($record) => $record->type->value === 'in' ? 'success' : 'danger')
                                            ->weight('bold'),
                                        TextEntry::make('stock_before')
                                            ->label('ก่อน')
                                            ->formatStateUsing(fn($state) => number_format($state)),
                                        TextEntry::make('stock_after')
                                            ->label('หลัง')
                                            ->formatStateUsing(fn($state) => number_format($state)),
                                        TextEntry::make('notes')
                                            ->label('หมายเหตุ')
                                            ->icon(Heroicon::DocumentText)
                                            ->columnSpan(2)
                                            ->limit(50),
                                        TextEntry::make('goodsReceipt.receipt_number')
                                            ->label('เลขที่ใบรับสินค้า')
                                            ->icon(Heroicon::DocumentCheck)
                                            ->url(fn($record) => $record->goods_receipt_id
                                                ? route('filament.store.resources.goods-receipts.view', $record->goods_receipt_id)
                                                : null)
                                            ->color('primary')
                                            ->visible(fn($record) => $record->goods_receipt_id !== null),
                                        TextEntry::make('saleOrder.order_number')
                                            ->label('เลขที่ใบขาย')
                                            ->icon(Heroicon::ShoppingCart)
                                            ->url(fn($record) => $record->sale_order_id
                                                ? route('filament.store.resources.sale-orders.view', $record->sale_order_id)
                                                : null)
                                            ->color('primary')
                                            ->visible(fn($record) => $record->sale_order_id !== null),
                                        TextEntry::make('creator.name')
                                            ->label('ผู้บันทึก')
                                            ->icon(Heroicon::User),
                                    ]),
                            ])
                            ->columns(1)
                            ->contained(false)
                            ->getStateUsing(fn($record) =>
                                $record
                                    ->product
                                    ->stockMovements()
                                    ->where('product_id', $record->product_id)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(50)
                                    ->get()),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}
