<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\SaleOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return '📈 ยอดขาย 30 วันย้อนหลัง';
    }

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = $this->getSalesPerDay();

        return [
            'datasets' => [
                [
                    'label' => 'ยอดขาย (บาท)',
                    'data' => $data['amounts'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getSalesPerDay(): array
    {
        $labels = [];
        $amounts = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');

            $amount = SaleOrder::whereDate('created_at', $date)
                ->where('status', OrderStatus::Confirmed)
                ->sum('total_amount');

            $amounts[] = $amount;
        }

        return [
            'labels' => $labels,
            'amounts' => $amounts,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "฿" + value.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
