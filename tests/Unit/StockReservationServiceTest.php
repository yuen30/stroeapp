<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\StockReservation;
use App\Services\InsufficientStockException;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class StockReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockReservationService();
        Log::spy();
    }

    /**
     * @test
     */
    public function it_creates_reservation_with_sufficient_stock(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Act
        $reservation = $this->service->createReservation($item);

        // Assert
        $this->assertInstanceOf(StockReservation::class, $reservation);
        $this->assertEquals($product->id, $reservation->product_id);
        $this->assertEquals($saleOrder->id, $reservation->sale_order_id);
        $this->assertEquals($item->id, $reservation->sale_order_item_id);
        $this->assertEquals(10, $reservation->reserved_quantity);
        $this->assertNotNull($reservation->expires_at);
        $this->assertTrue($reservation->expires_at->greaterThan(now()->addHours(23)));

        $this->assertDatabaseHas('stock_reservations', [
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'reserved_quantity' => 10,
        ]);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_stock_insufficient(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Act & Assert
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('สต็อกไม่เพียงพอ');

        $this->service->createReservation($item);

        $this->assertDatabaseMissing('stock_reservations', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * @test
     */
    public function it_updates_reservation_when_increasing_quantity_with_sufficient_stock(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $reservation = $this->service->createReservation($item);
        $oldQuantity = 10;

        // Update item quantity
        $item->quantity = 20;
        $item->save();

        // Act
        $this->service->updateReservation($item, $oldQuantity);

        // Assert
        $reservation->refresh();
        $this->assertEquals(20, $reservation->reserved_quantity);
        $this->assertTrue($reservation->expires_at->greaterThan(now()->addHours(23)));
    }

    /**
     * @test
     */
    public function it_updates_reservation_when_decreasing_quantity(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $reservation = $this->service->createReservation($item);
        $oldQuantity = 20;

        // Update item quantity
        $item->quantity = 10;
        $item->save();

        // Act
        $this->service->updateReservation($item, $oldQuantity);

        // Assert
        $reservation->refresh();
        $this->assertEquals(10, $reservation->reserved_quantity);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_increasing_quantity_with_insufficient_stock(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 15]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        // Create another item that reserves 10, leaving only 5 available
        $otherItem = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
        $this->service->createReservation($otherItem);

        // Create our test item that reserves 3
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $reservation = $this->service->createReservation($item);
        $oldQuantity = 3;

        // Now available stock is 15 - 10 - 3 = 2
        // Try to update to 10 (need 7 more, but only 2 available)
        $item->quantity = 10;
        $item->save();

        // Act & Assert
        try {
            $this->service->updateReservation($item, $oldQuantity);
            $this->fail('Expected InsufficientStockException was not thrown');
        } catch (InsufficientStockException $e) {
            // Reservation should remain unchanged due to transaction rollback
            $reservation->refresh();
            $this->assertEquals(3, $reservation->reserved_quantity);
        }
    }

    /**
     * @test
     */
    public function it_deletes_reservation(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $reservation = $this->service->createReservation($item);

        // Act
        $this->service->deleteReservation($item);

        // Assert
        $this->assertDatabaseMissing('stock_reservations', [
            'id' => $reservation->id,
        ]);
    }

    /**
     * @test
     */
    public function it_handles_delete_when_reservation_does_not_exist(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Act - should not throw exception
        $this->service->deleteReservation($item);

        // Assert - no exception thrown
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_releases_all_reservations_for_sale_order(): void
    {
        // Arrange
        $unit = \App\Models\Unit::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 100, 'unit_id' => $unit->id]);
        $product2 = Product::factory()->create(['stock_quantity' => 100, 'unit_id' => $unit->id]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        $item1 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product1->id,
            'quantity' => 10,
        ]);

        $item2 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product2->id,
            'quantity' => 20,
        ]);

        $this->service->createReservation($item1);
        $this->service->createReservation($item2);

        // Act
        $this->service->releaseReservations($saleOrder);

        // Assert
        $this->assertDatabaseMissing('stock_reservations', [
            'sale_order_id' => $saleOrder->id,
        ]);
    }

    /**
     * @test
     */
    public function it_handles_release_when_no_reservations_exist(): void
    {
        // Arrange
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        // Act - should not throw exception
        $this->service->releaseReservations($saleOrder);

        // Assert - no exception thrown
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_calculates_available_stock_correctly(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        $item1 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $item2 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $this->service->createReservation($item1);
        $this->service->createReservation($item2);

        // Act
        $availableStock = $this->service->getAvailableStock($product);

        // Assert
        $this->assertEquals(70, $availableStock);  // 100 - 10 - 20
    }

    /**
     * @test
     */
    public function it_excludes_item_when_calculating_available_stock(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        $item1 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $item2 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $this->service->createReservation($item1);
        $this->service->createReservation($item2);

        // Act - exclude item1's reservation
        $availableStock = $this->service->getAvailableStock($product, $item1->id);

        // Assert
        $this->assertEquals(80, $availableStock);  // 100 - 20 (item1's 10 is excluded)
    }

    /**
     * @test
     */
    public function it_ignores_expired_reservations_in_available_stock_calculation(): void
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
        $availableStock = $this->service->getAvailableStock($product);

        // Assert
        $this->assertEquals(100, $availableStock);  // Expired reservation not counted
    }

    /**
     * @test
     */
    public function it_cleans_up_expired_reservations(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        $item1 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $item2 = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        // Create expired reservation
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item1->id,
            'reserved_quantity' => 10,
            'expires_at' => now()->subHour(),
        ]);

        // Create active reservation
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item2->id,
            'reserved_quantity' => 20,
            'expires_at' => now()->addHours(24),
        ]);

        // Act
        $count = $this->service->cleanupExpiredReservations();

        // Assert
        $this->assertEquals(1, $count);
        $this->assertDatabaseMissing('stock_reservations', [
            'sale_order_item_id' => $item1->id,
        ]);
        $this->assertDatabaseHas('stock_reservations', [
            'sale_order_item_id' => $item2->id,
        ]);
    }

    /**
     * @test
     */
    public function it_returns_zero_when_no_expired_reservations_to_cleanup(): void
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
        $this->service->createReservation($item);

        // Act
        $count = $this->service->cleanupExpiredReservations();

        // Assert
        $this->assertEquals(0, $count);
    }

    /**
     * @test
     */
    public function it_uses_database_transaction_for_create_reservation(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Act
        $this->service->createReservation($item);

        // Note: DB::shouldReceive is verified automatically by Mockery
    }

    /**
     * @test
     */
    public function it_rolls_back_transaction_on_insufficient_stock(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Act & Assert
        try {
            $this->service->createReservation($item);
            $this->fail('Expected InsufficientStockException was not thrown');
        } catch (InsufficientStockException $e) {
            // Transaction should have rolled back
            $this->assertDatabaseMissing('stock_reservations', [
                'product_id' => $product->id,
            ]);
        }
    }

    /**
     * @test
     */
    public function it_returns_zero_for_available_stock_when_fully_reserved(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->service->createReservation($item);

        // Act
        $availableStock = $this->service->getAvailableStock($product);

        // Assert
        $this->assertEquals(0, $availableStock);
    }

    /**
     * @test
     */
    public function it_returns_zero_for_available_stock_when_over_reserved(): void
    {
        // Arrange - This shouldn't happen in practice, but test defensive coding
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);
        $item = SaleOrderItem::factory()->create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        // Manually create over-reservation to test max(0, ...) logic
        StockReservation::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 15,  // More than stock_quantity
            'expires_at' => now()->addHours(24),
        ]);

        // Act
        $availableStock = $this->service->getAvailableStock($product);

        // Assert
        $this->assertEquals(0, $availableStock);  // Should not return negative
    }
}
