<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\StockReservation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockReservationService
{
    /**
     * สร้างการจองสต็อกสำหรับ SaleOrderItem
     *
     * @throws InsufficientStockException
     * @throws Exception
     */
    public function createReservation(SaleOrderItem $item): StockReservation
    {
        return DB::transaction(function () use ($item) {
            // Lock product row เพื่อป้องกัน race condition
            $product = Product::where('id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw new Exception("Product not found: {$item->product_id}");
            }

            // ตรวจสอบ available stock
            $availableStock = $this->getAvailableStock($product);

            if ($availableStock < $item->quantity) {
                Log::warning('Insufficient stock for reservation', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'requested_quantity' => $item->quantity,
                    'available_stock' => $availableStock,
                    'sale_order_id' => $item->sale_order_id,
                    'sale_order_item_id' => $item->id,
                ]);

                throw new InsufficientStockException(
                    "สต็อกไม่เพียงพอ: {$product->name} (พร้อมใช้: {$availableStock}, ต้องการ: {$item->quantity})"
                );
            }

            // สร้างการจอง
            $reservation = StockReservation::create([
                'code' => 'RSV-'.now()->format('YmdHis').'-'.str_pad($item->id, 6, '0', STR_PAD_LEFT),
                'product_id' => $item->product_id,
                'sale_order_id' => $item->sale_order_id,
                'sale_order_item_id' => $item->id,
                'reserved_quantity' => $item->quantity,
                'expires_at' => now()->addHours(24),
            ]);

            Log::info('Stock reservation created', [
                'reservation_id' => $reservation->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'reserved_quantity' => $item->quantity,
                'sale_order_id' => $item->sale_order_id,
                'expires_at' => $reservation->expires_at,
            ]);

            return $reservation;
        });
    }

    /**
     * อัพเดทการจองเมื่อเปลี่ยนจำนวน
     *
     * @throws InsufficientStockException
     * @throws Exception
     */
    public function updateReservation(SaleOrderItem $item, int $oldQuantity): void
    {
        DB::transaction(function () use ($item, $oldQuantity) {
            // Lock product row
            $product = Product::where('id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw new Exception("Product not found: {$item->product_id}");
            }

            // หา reservation ที่มีอยู่
            $reservation = StockReservation::where('sale_order_item_id', $item->id)
                ->first();

            if (! $reservation) {
                Log::warning('Reservation not found for update', [
                    'sale_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                ]);

                return;
            }

            $quantityDiff = $item->quantity - $oldQuantity;

            // ถ้าเพิ่มจำนวน ต้องตรวจสอบ available stock
            if ($quantityDiff > 0) {
                $availableStock = $this->getAvailableStock($product, $item->id);

                if ($availableStock < $quantityDiff) {
                    Log::warning('Insufficient stock for reservation update', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'additional_quantity' => $quantityDiff,
                        'available_stock' => $availableStock,
                        'sale_order_item_id' => $item->id,
                    ]);

                    throw new InsufficientStockException(
                        "สต็อกไม่เพียงพอ: {$product->name} (พร้อมใช้: {$availableStock}, ต้องการเพิ่ม: {$quantityDiff})"
                    );
                }
            }

            // อัพเดทจำนวนการจอง
            $reservation->update([
                'reserved_quantity' => $item->quantity,
                'expires_at' => now()->addHours(24),  // รีเซ็ตเวลาหมดอายุ
            ]);

            Log::info('Stock reservation updated', [
                'reservation_id' => $reservation->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $item->quantity,
                'quantity_diff' => $quantityDiff,
                'expires_at' => $reservation->expires_at,
            ]);
        });
    }

    /**
     * ลบการจองสำหรับ SaleOrderItem
     */
    public function deleteReservation(SaleOrderItem $item): void
    {
        DB::transaction(function () use ($item) {
            $reservation = StockReservation::where('sale_order_item_id', $item->id)
                ->first();

            if ($reservation) {
                $reservedQuantity = $reservation->reserved_quantity;
                $productId = $reservation->product_id;

                $reservation->delete();

                Log::info('Stock reservation deleted', [
                    'product_id' => $productId,
                    'reserved_quantity' => $reservedQuantity,
                    'sale_order_id' => $item->sale_order_id,
                    'sale_order_item_id' => $item->id,
                ]);
            }
        });
    }

    /**
     * ปลดล็อคการจองทั้งหมดของ SaleOrder
     */
    public function releaseReservations(SaleOrder $saleOrder): void
    {
        DB::transaction(function () use ($saleOrder) {
            $reservations = StockReservation::where('sale_order_id', $saleOrder->id)
                ->get();

            if ($reservations->isEmpty()) {
                return;
            }

            $totalReleased = $reservations->sum('reserved_quantity');
            $productIds = $reservations->pluck('product_id')->unique();

            StockReservation::where('sale_order_id', $saleOrder->id)
                ->delete();

            Log::info('Stock reservations released', [
                'sale_order_id' => $saleOrder->id,
                'invoice_number' => $saleOrder->invoice_number,
                'total_reservations' => $reservations->count(),
                'total_quantity_released' => $totalReleased,
                'product_ids' => $productIds->toArray(),
            ]);
        });
    }

    /**
     * คำนวณ available stock สำหรับ Product
     *
     * @param  string|null  $excludeItemId  SaleOrderItem ID ที่จะไม่นับรวมในการคำนวณ (สำหรับ update)
     */
    public function getAvailableStock(Product $product, ?string $excludeItemId = null): int
    {
        $query = StockReservation::where('product_id', $product->id)
            ->where('expires_at', '>', now());

        // ไม่นับการจองของ item ที่กำลังแก้ไข
        if ($excludeItemId) {
            $query->where('sale_order_item_id', '!=', $excludeItemId);
        }

        $reservedQuantity = $query->sum('reserved_quantity');

        return max(0, $product->stock_quantity - $reservedQuantity);
    }

    /**
     * ลบการจองที่หมดอายุ
     *
     * @return int จำนวนการจองที่ถูกลบ
     */
    public function cleanupExpiredReservations(): int
    {
        return DB::transaction(function () {
            $expiredReservations = StockReservation::where('expires_at', '<=', now())
                ->get();

            if ($expiredReservations->isEmpty()) {
                return 0;
            }

            $count = $expiredReservations->count();
            $totalQuantity = $expiredReservations->sum('reserved_quantity');

            // Log รายละเอียดก่อนลบ
            foreach ($expiredReservations as $reservation) {
                Log::info('Expired reservation cleanup', [
                    'reservation_id' => $reservation->id,
                    'product_id' => $reservation->product_id,
                    'sale_order_id' => $reservation->sale_order_id,
                    'reserved_quantity' => $reservation->reserved_quantity,
                    'expired_at' => $reservation->expires_at,
                ]);
            }

            StockReservation::where('expires_at', '<=', now())
                ->delete();

            Log::info('Expired reservations cleanup completed', [
                'total_deleted' => $count,
                'total_quantity_released' => $totalQuantity,
            ]);

            return $count;
        });
    }
}

/**
 * Exception สำหรับกรณีสต็อกไม่เพียงพอ
 */
class InsufficientStockException extends Exception {}
