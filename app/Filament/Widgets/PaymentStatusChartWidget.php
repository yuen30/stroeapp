<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\PaymentStatus;
use App\Models\SaleOrder;
use Filament\Widgets\ChartWidget;

class PaymentStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return 'สถานะการชำระเงิน';
    }

    protected function getData(): array
    {
        $paidId = PaymentStatus::where('code', 'PAID')->first()?->id;
        $pendingId = PaymentStatus::where('code', 'PENDING')->first()?->id;
        $partialId = PaymentStatus::where('code', 'PARTIAL')->first()?->id;

        $paid = SaleOrder::where('status', OrderStatus::Confirmed)
            ->when($paidId, fn ($q) => $q->where('payment_status_id', $paidId))
            ->sum('total_amount');

        $unpaid = SaleOrder::where('status', OrderStatus::Confirmed)
            ->when($pendingId, fn ($q) => $q->where('payment_status_id', $pendingId))
            ->sum('total_amount');

        $partial = SaleOrder::where('status', OrderStatus::Confirmed)
            ->when($partialId, fn ($q) => $q->where('payment_status_id', $partialId))
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
