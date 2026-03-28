<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function creating(Product $product): void
    {
        if (blank($product->sku)) {
            $product->sku = 'SKU-'.strtoupper(uniqid());
        }

        if (blank($product->barcode)) {
            $product->barcode = 'BC'.date('ymd').str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Handle the Product "created" event.
     * (ไม่ต้องสร้าง Stock record แล้ว - ยอดอยู่ที่ Product.stock_quantity โดยตรง)
     */
    public function created(Product $product): void
    {
        // Stock อยู่ที่ Product.stock_quantity โดยตรง
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Stock อยู่ที่ Product.stock_quantity โดยตรง ไม่ต้อง sync กับ Stock table
    }

    /**
     * Handle the Product "deleting" event.
     */
    public function deleting(Product $product): void
    {
        if ($product->stock_quantity > 0) {
            throw new \Exception(
                "ไม่สามารถลบสินค้า '{$product->name}' ได้ เนื่องจากมีสต็อกคงเหลือ {$product->stock_quantity} ชิ้น"
            );
        }

        if ($product->stockMovements()->exists()) {
            throw new \Exception(
                "ไม่สามารถลบสินค้า '{$product->name}' ได้ เนื่องจากมีประวัติการเคลื่อนไหวสต็อก"
            );
        }
    }
}
