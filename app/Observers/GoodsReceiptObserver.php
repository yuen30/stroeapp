<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\GoodsReceipt;
use App\Models\Stock;
use App\Models\StockMovement;

class GoodsReceiptObserver
{
    public function creating(GoodsReceipt $goodsReceipt): void
    {
        // Auto-generate receipt_number
        if (empty($goodsReceipt->receipt_number)) {
            $lastReceipt = GoodsReceipt::withTrashed()
                ->where('receipt_number', 'like', 'GR-%')
                ->orderBy('receipt_number', 'desc')
                ->first();

            if ($lastReceipt && preg_match('/GR-(\d+)/', $lastReceipt->receipt_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $goodsReceipt->receipt_number = 'GR-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }
    }

    public function updated(GoodsReceipt $goodsReceipt): void
    {
        // ตรวจสอบว่ามีการเปลี่ยนสถานะเป็น confirmed หรือไม่
        if ($goodsReceipt->isDirty('status') && $goodsReceipt->status === OrderStatus::Confirmed) {
            $this->updateStock($goodsReceipt);
            $this->updatePurchaseOrderStatus($goodsReceipt);
        }
    }

    protected function updateStock(GoodsReceipt $goodsReceipt): void
    {
        // วนลูปทุกรายการสินค้าในใบรับสินค้า
        foreach ($goodsReceipt->items as $item) {
            // หา stock record หรือสร้างใหม่
            $stock = Stock::firstOrCreate(
                [
                    'product_id' => $item->product_id,
                    'branch_id' => $goodsReceipt->branch_id,
                ],
                [
                    'quantity' => 0,
                ]
            );

            // อัพเดทจำนวน stock
            $stock->increment('quantity', $item->quantity);

            // คำนวณ stock ก่อนและหลัง
            $stockBefore = $stock->quantity - $item->quantity;
            $stockAfter = $stock->quantity;

            // บันทึก stock movement
            StockMovement::create([
                'product_id' => $item->product_id,
                'goods_receipt_id' => $goodsReceipt->id,
                'created_by' => $goodsReceipt->created_by ?? auth()->id(),
                'type' => StockMovementType::In,
                'quantity' => $item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => 'รับสินค้าจากใบสั่งซื้อ ' . $goodsReceipt->purchaseOrder->order_number,
            ]);
        }
    }

    protected function updatePurchaseOrderStatus(GoodsReceipt $goodsReceipt): void
    {
        $purchaseOrder = $goodsReceipt->purchaseOrder;

        if (!$purchaseOrder) {
            return;
        }

        // คำนวณจำนวนที่สั่งทั้งหมด
        $totalOrdered = $purchaseOrder->items->sum('quantity');

        // คำนวณจำนวนที่รับแล้วทั้งหมด (จากทุกใบรับสินค้าที่ confirmed)
        $totalReceived = $purchaseOrder
            ->goodsReceipts()
            ->where('status', OrderStatus::Confirmed)
            ->with('items')
            ->get()
            ->flatMap(fn($receipt) => $receipt->items)
            ->sum('quantity');

        // อัพเดทสถานะตามจำนวนที่รับ
        if ($totalReceived >= $totalOrdered) {
            // รับครบแล้ว
            $purchaseOrder->update(['status' => OrderStatus::Completed]);
        } else {
            // รับบางส่วน
            $purchaseOrder->update(['status' => OrderStatus::PartiallyReceived]);
        }
    }
}
