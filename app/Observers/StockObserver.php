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
        if ($stock->isDirty(['quantity', 'cost_price', 'selling_price'])) {
            $product = $stock->product;

            if ($product) {
                $updateData = [];

                if ($stock->isDirty('quantity')) {
                    $totalQuantity = Stock::where('product_id', $stock->product_id)->sum('quantity');
                    $updateData['stock_quantity'] = $totalQuantity;
                }

                if ($stock->isDirty('cost_price')) {
                    $updateData['cost_price'] = $stock->cost_price;
                }

                if ($stock->isDirty('selling_price')) {
                    $updateData['selling_price'] = $stock->selling_price;
                }

                if (! empty($updateData)) {
                    $product->updateQuietly($updateData);
                }
            }
        }
    }

    /**
     * Handle the Stock "created" event.
     * Sync Product เมื่อสร้าง Stock ใหม่
     */
    public function created(Stock $stock): void
    {
        $product = $stock->product;

        if ($product) {
            $totalQuantity = Stock::where('product_id', $stock->product_id)->sum('quantity');
            $product->updateQuietly(['stock_quantity' => $totalQuantity]);
        }
    }

    /**
     * Handle the Stock "deleting" event.
     * ป้องกันการลบ Stock ที่มีสินค้าคงเหลือ
     */
    public function deleting(Stock $stock): void
    {
        if ($stock->quantity > 0) {
            throw new \Exception(
                "ไม่สามารถลบสต็อกได้ เนื่องจากมีสินค้าคงเหลือ {$stock->quantity} หน่วย"
            );
        }
    }
}
