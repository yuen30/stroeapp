<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\SaleOrderItem;
use App\Services\StockReservationService;

class SaleOrderItemObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        private StockReservationService $reservationService
    ) {}

    /**
     * Handle the SaleOrderItem "created" event.
     */
    public function created(SaleOrderItem $item): void
    {
        // จองสต็อกถ้าเป็น draft status
        if ($item->saleOrder->status === OrderStatus::Draft) {
            $this->reservationService->createReservation($item);
        }

        $this->recalculateSaleOrderTotals($item);
    }

    /**
     * Handle the SaleOrderItem "updated" event.
     */
    public function updated(SaleOrderItem $item): void
    {
        // อัพเดทการจองถ้าเปลี่ยน quantity ในสถานะ draft
        if ($item->saleOrder->status === OrderStatus::Draft &&
                $item->wasChanged('quantity')) {
            $oldQuantity = $item->getOriginal('quantity');
            $this->reservationService->updateReservation($item, $oldQuantity);
        }

        $this->recalculateSaleOrderTotals($item);
    }

    /**
     * Handle the SaleOrderItem "deleted" event.
     */
    public function deleted(SaleOrderItem $item): void
    {
        // ลบการจอง
        $this->reservationService->deleteReservation($item);

        $this->recalculateSaleOrderTotals($item);
    }

    /**
     * Recalculate sale order totals
     */
    private function recalculateSaleOrderTotals(SaleOrderItem $item): void
    {
        $saleOrder = $item->saleOrder;

        if (! $saleOrder) {
            return;
        }

        // คำนวณยอดรวมจากรายการสินค้าทั้งหมด
        $subtotal = $saleOrder->items()->sum('total_price');

        // คำนวณยอดรวมสุทธิ = ยอดรวม - ส่วนลด + VAT
        $discountAmount = $saleOrder->discount_amount ?? 0;
        $vatAmount = $saleOrder->vat_amount ?? 0;
        $totalAmount = $subtotal - $discountAmount + $vatAmount;

        // อัพเดทยอดเงินโดยไม่ trigger observer อีกรอบ
        $saleOrder->updateQuietly([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
        ]);
    }
}
