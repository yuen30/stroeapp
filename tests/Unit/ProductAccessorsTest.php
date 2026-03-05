<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\StockReservation;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAccessorsTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_reserved_quantity_from_active_reservations(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Create active reservation
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 10,
            'expires_at' => now()->addHours(24),
        ]);

        // Act
        $reservedQuantity = $product->reserved_quantity;

        // Assert
        $this->assertEquals(10, $reservedQuantity);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_ignores_expired_reservations_in_reserved_quantity(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Create expired reservation
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 10,
            'expires_at' => now()->subHour(),
        ]);

        // Act
        $reservedQuantity = $product->reserved_quantity;

        // Assert
        $this->assertEquals(0, $reservedQuantity);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_available_stock_correctly(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 30,
        ]);

        // Create active reservation
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 30,
            'expires_at' => now()->addHours(24),
        ]);

        // Act
        $availableStock = $product->available_stock;

        // Assert
        $this->assertEquals(70, $availableStock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_zero_for_available_stock_when_fully_reserved(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 50]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        // Create reservation for all stock
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 50,
            'expires_at' => now()->addHours(24),
        ]);

        // Act
        $availableStock = $product->available_stock;

        // Assert
        $this->assertEquals(0, $availableStock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sums_multiple_reservations_correctly(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder1 = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $saleOrder2 = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        $item1 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder1->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $item2 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder2->id,
            'product_id' => $product->id,
            'quantity' => 15,
        ]);

        // Create multiple reservations
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder1->id,
            'sale_order_item_id' => $item1->id,
            'reserved_quantity' => 20,
            'expires_at' => now()->addHours(24),
        ]);

        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder2->id,
            'sale_order_item_id' => $item2->id,
            'reserved_quantity' => 15,
            'expires_at' => now()->addHours(24),
        ]);

        // Act
        $reservedQuantity = $product->reserved_quantity;
        $availableStock = $product->available_stock;

        // Assert
        $this->assertEquals(35, $reservedQuantity);
        $this->assertEquals(65, $availableStock);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_stock_reservations_relationship(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 10,
            'expires_at' => now()->addHours(24),
        ]);

        // Act
        $reservations = $product->stockReservations;

        // Assert
        $this->assertCount(1, $reservations);
        $this->assertEquals(10, $reservations->first()->reserved_quantity);
    }
}
