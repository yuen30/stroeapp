<?php

namespace App\Observers;

use App\Models\SaleOrderItem;

class SaleOrderItemObserver
{
    /**
     * Handle the SaleOrderItem "created" event.
     */
    public function created(SaleOrderItem $item): void
    {
        $this->recalculateSaleOrderTotals($item);
    }

    /**
     * Handle the SaleOrderItem "updated" event.
     */
    public function updated(SaleOrderItem $item): void
    {
        $this->recalculateSaleOrderTotals($item);
    }

    /**
     * Handle the SaleOrderItem "deleted" event.
     */
    public function deleted(SaleOrderItem $item): void
    {
        $this->recalculateSaleOrderTotals($item);
    }

    /**
     * Recalculate sale order totals
     */
    private function recalculateSaleOrderTotals(SaleOrderItem $item): void
    {
        $saleOrder = $item->saleOrder;

        if (!$saleOrder) {
            return;
        }

        // คำนวณยอดรวมจากรายการสินค้าทั้งหมด
        $subtotal = $saleOrder->items()->sum('total_price');

        // คำนวณยอดรวมสุทธิ = ยอดรวม - ส่วนลด + VAT
        $discountAmount = $saleOrder->discount_amount ?? 0;
        $vatAmount = ($subtotal - $discountAmount) * 0.07;
        $totalAmount = $subtotal - $discountAmount + $vatAmount;

        // อัพเดทยอดเงินโดยไม่ trigger observer อีกรอบ
        $saleOrder->updateQuietly([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
        ]);
    }
}
