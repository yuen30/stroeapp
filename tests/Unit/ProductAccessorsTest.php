<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\StockReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductAccessorsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_reserved_quantity_from_active_reservations(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();

        // Observer will create reservation automatically when SaleOrderItem is created
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        $reservedQuantity = $product->fresh()->reserved_quantity;

        $this->assertEquals(10, $reservedQuantity);
    }

    #[Test]
    public function it_ignores_expired_reservations_in_reserved_quantity(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        // Manually update reservation to be expired (Observer creates active one)
        StockReservation::where('sale_order_item_id', $item->id)
            ->update(['expires_at' => now()->subHour()]);

        $reservedQuantity = $product->fresh()->reserved_quantity;

        $this->assertEquals(0, $reservedQuantity);
    }

    #[Test]
    public function it_calculates_available_stock_correctly(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 30);

        $availableStock = $product->fresh()->available_stock;

        $this->assertEquals(70, $availableStock);
    }

    #[Test]
    public function it_returns_zero_for_available_stock_when_fully_reserved(): void
    {
        $product = $this->createProductWithStock(50);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 50);

        $availableStock = $product->fresh()->available_stock;

        $this->assertEquals(0, $availableStock);
    }

    #[Test]
    public function it_sums_multiple_reservations_correctly(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder1 = $this->createSaleOrder();
        $saleOrder2 = $this->createSaleOrder();

        // Observer will create reservations automatically
        $this->createSaleOrderItem($saleOrder1, $product, 20);
        $this->createSaleOrderItem($saleOrder2, $product, 15);

        $reservedQuantity = $product->fresh()->reserved_quantity;
        $availableStock = $product->fresh()->available_stock;

        $this->assertEquals(35, $reservedQuantity);
        $this->assertEquals(65, $availableStock);
    }

    #[Test]
    public function it_has_stock_reservations_relationship(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        $reservations = $product->fresh()->stockReservations;

        $this->assertCount(1, $reservations);
        $this->assertEquals(10, $reservations->first()->reserved_quantity);
    }

    private function createProductWithStock(int $stock): Product
    {
        return Product::factory()->create([
            'stock_quantity' => $stock,
        ]);
    }

    private function createSaleOrder(): SaleOrder
    {
        return SaleOrder::factory()->create([
            'status' => OrderStatus::Draft,
        ]);
    }

    private function createSaleOrderItem(SaleOrder $saleOrder, Product $product, int $quantity): SaleOrderItem
    {
        return SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
    }
}
