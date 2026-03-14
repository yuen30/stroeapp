<?php

namespace App\Observers;

use App\Models\PurchaseOrder;

class PurchaseOrderObserver
{
    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        // ถ้ามีการเปลี่ยนแปลง discount_amount หรือ vat_amount ให้คำนวณยอดรวมใหม่
        if ($purchaseOrder->isDirty(['discount_amount', 'vat_amount'])) {
            $subtotal = $purchaseOrder->subtotal ?? 0;
            $discountAmount = $purchaseOrder->discount_amount ?? 0;
            $vatAmount = $purchaseOrder->vat_amount ?? 0;
            $totalAmount = $subtotal - $discountAmount + $vatAmount;

            $purchaseOrder->updateQuietly([
                'total_amount' => $totalAmount,
            ]);
        }
    }
}
