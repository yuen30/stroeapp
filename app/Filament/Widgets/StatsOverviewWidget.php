<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SaleOrder;
use App\Models\StockReservation;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // ยอดขายวันนี้
        $todaySales = SaleOrder::whereDate('created_at', today())
            ->where('status', OrderStatus::Confirmed)
            ->sum('total_amount');

        // ยอดขายเดือนนี้
        $thisMonthSales = SaleOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', OrderStatus::Confirmed)
            ->sum('total_amount');

        // ยอดขายเดือนที่แล้ว
        $lastMonthSales = SaleOrder::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('status', OrderStatus::Confirmed)
            ->sum('total_amount');

        // คำนวณเปอร์เซ็นต์เปลี่ยนแปลง
        $salesChange = $lastMonthSales > 0
            ? (($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100
            : 0;

        // สินค้าใกล้หมด (< 10 ชิ้น)
        $lowStockCount = Product::where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<', 10)
            ->count();

        // สินค้าหมดสต็อก
        $outOfStockCount = Product::where('stock_quantity', 0)->count();

        // สต็อกที่ถูกจอง
        $reservedStock = StockReservation::where('expires_at', '>', now())
            ->sum('reserved_quantity');

        // ใบสั่งซื้อรอดำเนินการ
        $pendingPurchaseOrders = PurchaseOrder::whereIn('status', [
            OrderStatus::Draft,
            OrderStatus::Confirmed,
        ])->count();

        // ยอดค้างชำระทั้งหมด
        $outstandingAmount = SaleOrder::whereIn('status', [
            OrderStatus::Confirmed,
            OrderStatus::PartiallyReceived,
        ])->whereIn('payment_status', [
            PaymentStatus::Unpaid,
            PaymentStatus::Partial,
        ])->sum('total_amount');

        // ลูกค้าใหม่เดือนนี้
        $newCustomers = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('ยอดขายวันนี้', Number::currency($todaySales, 'THB', 'th'))
                ->description('ยอดขายเดือนนี้: ' . Number::currency($thisMonthSales, 'THB', 'th'))
                ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesChange >= 0 ? 'success' : 'danger')
                ->chart($this->getSalesChartData()),
            Stat::make('สินค้าใกล้หมด', $lowStockCount . ' รายการ')
                ->description('หมดสต็อก: ' . $outOfStockCount . ' รายการ')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 10 ? 'warning' : 'success')
                ->icon('heroicon-o-cube'),
            Stat::make('สต็อกที่ถูกจอง', $reservedStock . ' หน่วย')
                ->description('จากใบสั่งขาย Draft')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('info')
                ->icon('heroicon-o-clock'),
            Stat::make('ใบสั่งซื้อรอดำเนินการ', $pendingPurchaseOrders . ' ใบ')
                ->description('รอยืนยัน / รอรับสินค้า')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($pendingPurchaseOrders > 5 ? 'warning' : 'success')
                ->icon('heroicon-o-document-text'),
            Stat::make('ยอดค้างชำระ', Number::currency($outstandingAmount, 'THB', 'th'))
                ->description('จากใบสั่งขายที่ยืนยันแล้ว')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($outstandingAmount > 100000 ? 'danger' : 'warning')
                ->icon('heroicon-o-currency-dollar'),
            Stat::make('ลูกค้าใหม่', $newCustomers . ' ราย')
                ->description('เดือนนี้')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success')
                ->icon('heroicon-o-users'),
        ];
    }

    protected function getSalesChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $amount = SaleOrder::whereDate('created_at', $date)
                ->where('status', OrderStatus::Confirmed)
                ->sum('total_amount');
            $data[] = $amount / 1000;  // แสดงเป็นพัน
        }
        return $data;
    }
}
