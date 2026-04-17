<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Traits\InteractsWithPageTableWorkaround;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GoodsReceiptOverview extends BaseWidget
{
    use InteractsWithPageTableWorkaround;

    protected function getTablePage(): string
    {
        return \App\Filament\Pages\Reports\GoodsReceiptReport::class;
    }

    protected function getStats(): array
    {
        // ดึง Query หลักที่ผ่านการจัด Filter หน้า Page มาแล้ว (ยอดเยี่ยมมากสำหรับการทำ Dashboard)
        $query = $this->getPageTableQuery();

        $totalCount = (clone $query)->count();

        // คำนวณจำนวนชิ้นรวม
        $totalQuantity = (clone $query)->with('items')->get()->sum(fn ($gr) => $gr->items->sum('quantity'));

        // แยกตามสถานะ
        $completedCount = (clone $query)->where('status', OrderStatus::Completed->value)->count();
        $draftCount = (clone $query)->where('status', OrderStatus::Draft->value)->count();

        return [
            Stat::make('ใบรับสินค้า (ตามช่วงเวลา)', number_format($totalCount) . ' ใบ')
                ->description('จำนวนเอกสารทั้งหมดในรายงานนี้')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('ปริมาณสินค้ารับเข้า (รวม)', number_format($totalQuantity) . ' หน่วย')
                ->description('ยอดรวมชิ้นสินค้าเข้าคลังจริง')
                ->descriptionIcon('heroicon-m-archive-box-arrow-down')
                ->color('success'),

            Stat::make('สถานะรับออเดอร์', number_format($completedCount) . ' สำเร็จ')
                ->description(number_format($draftCount) . ' รอดำเนินการ (แบบร่าง)')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($draftCount > 0 ? 'warning' : 'info'),
        ];
    }
}
