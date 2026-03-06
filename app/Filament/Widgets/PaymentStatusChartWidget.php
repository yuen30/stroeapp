<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\SaleOrder;
use Filament\Widgets\ChartWidget;

class PaymentStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return '💰 สถานะการชำระเงิน';
    }

    protected function getData(): array
    {
        $paid = SaleOrder::where('status', OrderStatus::Confirmed)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total_amount');

        $unpaid = SaleOrder::where('status', OrderStatus::Confirmed)
            ->where('payment_status', PaymentStatus::Unpaid)
            ->sum('total_amount');

        $partial = SaleOrder::where('status', OrderStatus::Confirmed)
            ->where('payment_status', PaymentStatus::Partial)
            ->sum('total_amount');

        return [
            'datasets' => [
                [
                    'label' => 'มูลค่า (บาท)',
                    'data' => [$paid, $partial, $unpaid],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // เขียว - ชำระแล้ว
                        'rgba(245, 158, 11, 0.8)',  // เหลือง - ชำระบางส่วน
                        'rgba(239, 68, 68, 0.8)',  // แดง - ค้างชำระ
                    ],
                ],
            ],
            'labels' => ['ชำระแล้ว', 'ชำระบางส่วน', 'ค้างชำระ'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": ฿" + context.parsed.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
