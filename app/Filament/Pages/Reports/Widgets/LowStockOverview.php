<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->where('min_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->get();

        $outOfStockCount = $lowStockProducts->where('stock_quantity', '<=', 0)->count();
        $totalLowStockCount = $lowStockProducts->count();

        $estimatedRestockCost = $lowStockProducts->sum(function ($product) {
            $shortage = max($product->min_stock - $product->stock_quantity, 0);
            return $shortage * $product->cost_price;
        });

        return [
            Stat::make('จำนวนสินค้าที่ต้องเติม', number_format($totalLowStockCount) . ' รายการ')
                ->description('สินค้าที่มีสต็อกต่ำกว่าเกณฑ์')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),

            Stat::make('สินค้าหมดสต็อก (Out of Stock)', number_format($outOfStockCount) . ' รายการ')
                ->description('สินค้าที่จำนวนคงเหลือ 0 (ต้องสั่งด่วน)')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('มูลค่าประเมินการเติมสต็อก', '฿' . number_format($estimatedRestockCost, 2))
                ->description('อ้างอิงจากราคาทุน x จำนวนชิ้นที่ขาด')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
