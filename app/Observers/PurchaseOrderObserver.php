<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Services\DocumentNumberService;

class PurchaseOrderObserver
{
    public function __construct(
        private DocumentNumberService $documentNumberService
    ) {}

    /**
     * Handle the PurchaseOrder "creating" event.
     */
    public function creating(PurchaseOrder $purchaseOrder): void
    {
        // สร้างเลขที่เอกสารอัตโนมัติถ้ายังไม่มี
        if (empty($purchaseOrder->order_number)) {
            $purchaseOrder->order_number = $this->documentNumberService->generate(
                'PO',
                $purchaseOrder->company_id,
                $purchaseOrder->branch_id
            );
        }
    }

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
