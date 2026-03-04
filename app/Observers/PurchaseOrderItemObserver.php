<?php

namespace App\Observers;

use App\Models\PurchaseOrderItem;

class PurchaseOrderItemObserver
{
    /**
     * Handle the PurchaseOrderItem "created" event.
     */
    public function created(PurchaseOrderItem $item): void
    {
        $this->recalculatePurchaseOrderTotals($item);
    }

    /**
     * Handle the PurchaseOrderItem "updated" event.
     */
    public function updated(PurchaseOrderItem $item): void
    {
        $this->recalculatePurchaseOrderTotals($item);
    }

    /**
     * Handle the PurchaseOrderItem "deleted" event.
     */
    public function deleted(PurchaseOrderItem $item): void
    {
        $this->recalculatePurchaseOrderTotals($item);
    }

    /**
     * Recalculate purchase order totals
     */
    private function recalculatePurchaseOrderTotals(PurchaseOrderItem $item): void
    {
        $purchaseOrder = $item->purchaseOrder;

        if (!$purchaseOrder) {
            return;
        }

        // คำนวณยอดรวมจากรายการสินค้าทั้งหมด
        $subtotal = $purchaseOrder->items()->sum('total_price');

        // คำนวณยอดรวมสุทธิ = ยอดรวม - ส่วนลด + VAT
        $discountAmount = $purchaseOrder->discount_amount ?? 0;
        $vatAmount = $purchaseOrder->vat_amount ?? 0;
        $totalAmount = $subtotal - $discountAmount + $vatAmount;

        // อัพเดทยอดเงินโดยไม่ trigger observer อีกรอบ
        $purchaseOrder->updateQuietly([
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
        ]);
    }
}
