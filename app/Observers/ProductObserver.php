<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Stock;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     * สร้างรหัสสินค้าอัตโนมัติถ้าไม่ได้ระบุ
     */
    public function creating(Product $product): void
    {
        if (empty($product->code)) {
            $product->code = $this->generateProductCode();
        }
    }

    /**
     * Handle the Product "created" event.
     * สร้าง Stock record อัตโนมัติเมื่อสร้างสินค้าใหม่
     */
    public function created(Product $product): void
    {
        // สร้าง Stock record สำหรับสินค้าใหม่
        Stock::create([
            'product_id' => $product->id,
            'quantity' => $product->stock_quantity ?? 0,
            'cost_price' => $product->cost_price ?? 0,
            'selling_price' => $product->selling_price ?? 0,
        ]);
    }

    /**
     * Handle the Product "updated" event.
     * อัปเดต Stock record เมื่อแก้ไขข้อมูลสินค้า
     */
    public function updated(Product $product): void
    {
        // ตรวจสอบว่ามีการเปลี่ยนแปลงราคาหรือจำนวนสต็อกหรือไม่
        if ($product->isDirty(['cost_price', 'selling_price', 'stock_quantity'])) {
            $stock = $product->stocks()->first();

            if ($stock) {
                $updateData = [];

                if ($product->isDirty('cost_price')) {
                    $updateData['cost_price'] = $product->cost_price;
                }

                if ($product->isDirty('selling_price')) {
                    $updateData['selling_price'] = $product->selling_price;
                }

                if ($product->isDirty('stock_quantity')) {
                    $updateData['quantity'] = $product->stock_quantity;
                }

                if (!empty($updateData)) {
                    $stock->updateQuietly($updateData);
                }
            }
        }
    }

    /**
     * Handle the Product "deleting" event.
     */
    public function deleting(Product $product): void
    {
        // ป้องกันการลบสินค้าที่มีสต็อกคงเหลือ
        if ($product->stock_quantity > 0) {
            throw new \Exception(
                "ไม่สามารถลบสินค้า '{$product->name}' ได้ เนื่องจากมีสต็อกคงเหลือ {$product->stock_quantity} ชิ้น"
            );
        }

        // ป้องกันการลบสินค้าที่มีประวัติการเคลื่อนไหวสต็อก
        if ($product->stockMovements()->exists()) {
            throw new \Exception(
                "ไม่สามารถลบสินค้า '{$product->name}' ได้ เนื่องจากมีประวัติการเคลื่อนไหวสต็อก"
            );
        }

        // ลบ Stock record ที่เกี่ยวข้อง
        $product->stocks()->delete();
    }

    /**
     * สร้างรหัสสินค้าอัตโนมัติ
     * รูปแบบ: PROD-XXXXXX (X = เลข 6 หัก)
     */
    private function generateProductCode(): string
    {
        do {
            // หาเลขลำดับถัดไป
            $lastProduct = Product::withTrashed()
                ->where('code', 'like', 'PROD-%')
                ->orderBy('code', 'desc')
                ->first();

            if ($lastProduct && preg_match('/PROD-(\d+)/', $lastProduct->code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }

            $code = 'PROD-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // ตรวจสอบว่ารหัสนี้ยังไม่มีในระบบ
            $exists = Product::withTrashed()->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
