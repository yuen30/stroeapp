<?php

namespace App\Observers;

use App\Models\Stock;

class StockObserver
{
    /**
     * Handle the Stock "updated" event.
     * อัปเดต Product เมื่อมีการแก้ไข Stock
     */
    public function updated(Stock $stock): void
    {
        // ตรวจสอบว่ามีการเปลี่ยนแปลงข้อมูลที่ต้อง sync กลับไปยัง Product หรือไม่
        if ($stock->isDirty(['quantity', 'cost_price', 'selling_price'])) {
            $product = $stock->product;

            if ($product) {
                $updateData = [];

                // Sync quantity
                if ($stock->isDirty('quantity')) {
                    $updateData['stock_quantity'] = $stock->quantity;
                }

                // Sync cost_price
                if ($stock->isDirty('cost_price')) {
                    $updateData['cost_price'] = $stock->cost_price;
                }

                // Sync selling_price
                if ($stock->isDirty('selling_price')) {
                    $updateData['selling_price'] = $stock->selling_price;
                }

                // อัปเดต Product โดยไม่ trigger Observer ซ้ำ
                if (!empty($updateData)) {
                    $product->updateQuietly($updateData);
                }
            }
        }
    }

    /**
     * Handle the Stock "deleting" event.
     * ป้องกันการลบ Stock ที่มีสินค้าคงเหลือ
     */
    public function deleting(Stock $stock): void
    {
        // ป้องกันการลบ Stock ที่มีสินค้าคงเหลือ
        if ($stock->quantity > 0) {
            throw new \Exception(
                "ไม่สามารถลบสต็อกได้ เนื่องจากมีสินค้าคงเหลือ {$stock->quantity} หน่วย"
            );
        }
    }
}
