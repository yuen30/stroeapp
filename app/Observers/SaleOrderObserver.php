<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\SaleOrder;
use App\Models\StockMovement;

class SaleOrderObserver
{
    /**
     * Handle the SaleOrder "created" event.
     */
    public function created(SaleOrder $saleOrder): void
    {
        // ตัดสต็อกเมื่อสถานะเป็น Confirmed
        if ($saleOrder->status === OrderStatus::Confirmed) {
            $this->createStockMovements($saleOrder);
        }
    }

    /**
     * Handle the SaleOrder "updated" event.
     */
    public function updated(SaleOrder $saleOrder): void
    {
        // ถ้าเปลี่ยนสถานะจาก Draft เป็น Confirmed
        if ($saleOrder->wasChanged('status') &&
                $saleOrder->status === OrderStatus::Confirmed &&
                $saleOrder->getOriginal('status') === OrderStatus::Draft) {
            // ปลดล็อคการจองสต็อก (เพราะจะตัดสต็อกจริงแล้ว)
            $this->releaseReservations($saleOrder);

            // ตัดสต็อก
            $this->createStockMovements($saleOrder);
        }

        // ถ้ายกเลิก ให้คืนสต็อก
        if ($saleOrder->wasChanged('status') &&
                $saleOrder->status === OrderStatus::Cancelled) {
            $originalStatus = $saleOrder->getOriginal('status');

            // ถ้ายกเลิกจาก Confirmed ให้คืนสต็อก
            if ($originalStatus === OrderStatus::Confirmed) {
                $this->revertStockMovements($saleOrder);
            }

            // ถ้ายกเลิกจาก Draft ให้ปลดล็อคการจอง
            if ($originalStatus === OrderStatus::Draft) {
                $this->releaseReservations($saleOrder);
            }
        }
    }

    /**
     * สร้าง StockMovement และตัดสต็อก
     */
    private function createStockMovements(SaleOrder $saleOrder): void
    {
        foreach ($saleOrder->items as $item) {
            $product = $item->product;
            $stockBefore = $product->stock_quantity;

            // ตรวจสอบสต็อกเพียงพอหรือไม่
            if ($stockBefore < $item->quantity) {
                throw new \Exception(
                    "สต็อกสินค้า {$product->name} ไม่เพียงพอ (มีอยู่ {$stockBefore}, ต้องการ {$item->quantity})"
                );
            }

            StockMovement::create([
                'product_id' => $item->product_id,
                'sale_order_id' => $saleOrder->id,
                'created_by' => $saleOrder->created_by,
                'type' => StockMovementType::Out,
                'quantity' => $item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore - $item->quantity,
                'notes' => "ตัดสต็อกจากใบสั่งขายเลขที่ {$saleOrder->invoice_number}",
            ]);

            // ตัดสต็อกสินค้า
            $product->decrement('stock_quantity', $item->quantity);
        }
    }

    /**
     * คืนสต็อกเมื่อยกเลิก
     */
    private function revertStockMovements(SaleOrder $saleOrder): void
    {
        $movements = StockMovement::where('sale_order_id', $saleOrder->id)->get();

        foreach ($movements as $movement) {
            $product = $movement->product;

            // คืนสต็อก
            $product->increment('stock_quantity', $movement->quantity);

            // ลบ StockMovement
            $movement->delete();
        }
    }

    /**
     * ปลดล็อคการจองสต็อก
     */
    private function releaseReservations(SaleOrder $saleOrder): void
    {
        \App\Models\StockReservation::where('sale_order_id', $saleOrder->id)->delete();
    }
}
