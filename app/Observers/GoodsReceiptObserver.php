<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\GoodsReceipt;
use App\Models\StockMovement;

class GoodsReceiptObserver
{
    public function updated(GoodsReceipt $goodsReceipt): void
    {
        if ($goodsReceipt->isDirty('status') && $goodsReceipt->status === OrderStatus::Confirmed) {
            $this->updateStock($goodsReceipt);
            $this->updatePurchaseOrderStatus($goodsReceipt);
        }
    }

    protected function updateStock(GoodsReceipt $goodsReceipt): void
    {
        foreach ($goodsReceipt->items as $item) {
            $product = $item->product;
            $stockBefore = $product->stock_quantity;
            $product->increment('stock_quantity', $item->quantity);
            $stockAfter = $product->stock_quantity;

            StockMovement::create([
                'product_id' => $item->product_id,
                'goods_receipt_id' => $goodsReceipt->id,
                'created_by' => $goodsReceipt->created_by ?? auth()->id(),
                'type' => StockMovementType::In,
                'quantity' => $item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $goodsReceipt->is_standalone
                    ? 'รับสินค้า (รับแยก)'
                    : 'รับสินค้าจากใบสั่งซื้อ '.$goodsReceipt->purchaseOrder?->order_number,
            ]);
        }
    }

    protected function updatePurchaseOrderStatus(GoodsReceipt $goodsReceipt): void
    {
        $purchaseOrder = $goodsReceipt->purchaseOrder;

        if (! $purchaseOrder) {
            return;
        }

        $totalOrdered = $purchaseOrder->items->sum('quantity');

        $totalReceived = $purchaseOrder
            ->goodsReceipts()
            ->where('status', OrderStatus::Confirmed)
            ->with('items')
            ->get()
            ->flatMap(fn ($receipt) => $receipt->items)
            ->sum('quantity');

        if ($totalReceived >= $totalOrdered) {
            $purchaseOrder->update(['status' => OrderStatus::Completed]);
        } else {
            $purchaseOrder->update(['status' => OrderStatus::PartiallyReceived]);
        }
    }
}
