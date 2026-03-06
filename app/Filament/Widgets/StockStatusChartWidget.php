<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class StockStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return '📦 สต็อกตามสถานะ';
    }

    protected function getData(): array
    {
        $outOfStock = Product::where('stock_quantity', 0)->count();
        $lowStock = Product::where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<', 10)
            ->count();
        $normalStock = Product::where('stock_quantity', '>=', 10)->count();

        return [
            'datasets' => [
                [
                    'label' => 'จำนวนสินค้า',
                    'data' => [$normalStock, $lowStock, $outOfStock],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // เขียว - ปกติ
                        'rgba(245, 158, 11, 0.8)',  // เหลือง - ใกล้หมด
                        'rgba(239, 68, 68, 0.8)',  // แดง - หมด
                    ],
                ],
            ],
            'labels' => ['ปกติ (≥10)', 'ใกล้หมด (<10)', 'หมดสต็อก'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
