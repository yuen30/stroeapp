<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\SaleOrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return '🏆 สินค้าขายดี Top 10';
    }

    protected function getData(): array
    {
        $topProducts = SaleOrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereHas('saleOrder', function ($query) {
                $query
                    ->where('status', OrderStatus::Confirmed)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->with('product')
            ->get();

        $labels = [];
        $data = [];

        foreach ($topProducts as $item) {
            $labels[] = $item->product->name ?? 'N/A';
            $data[] = $item->total_quantity;
        }

        return [
            'datasets' => [
                [
                    'label' => 'จำนวนที่ขาย',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
