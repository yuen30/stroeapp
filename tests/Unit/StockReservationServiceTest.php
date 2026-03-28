<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\StockReservation;
use App\Models\Unit;
use App\Services\InsufficientStockException;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockReservationService;
        Log::spy();

        // Disable observer to prevent double reservation creation
        SaleOrderItem::unsetEventDispatcher();
    }

    #[Test]
    public function it_creates_reservation_with_sufficient_stock(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        $reservation = $this->service->createReservation($item);

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

    #[Test]
    public function it_throws_exception_when_stock_insufficient(): void
    {
        $product = $this->createProductWithStock(5);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('สต็อกไม่เพียงพอ');

        try {
            $this->service->createReservation($item);
        } catch (InsufficientStockException $e) {
            $this->assertDatabaseMissing('stock_reservations', [
                'product_id' => $product->id,
            ]);
            throw $e;
        }
    }

    #[Test]
    public function it_updates_reservation_when_increasing_quantity_with_sufficient_stock(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);
        $reservation = $this->service->createReservation($item);
        $oldQuantity = 10;

        $item->quantity = 20;
        $item->save();

        $this->service->updateReservation($item, $oldQuantity);

        $reservation->refresh();
        $this->assertEquals(20, $reservation->reserved_quantity);
        $this->assertTrue($reservation->expires_at->greaterThan(now()->addHours(23)));
    }

    #[Test]
    public function it_updates_reservation_when_decreasing_quantity(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 20);
        $reservation = $this->service->createReservation($item);
        $oldQuantity = 20;

        $item->quantity = 10;
        $item->save();

        $this->service->updateReservation($item, $oldQuantity);

        $reservation->refresh();
        $this->assertEquals(10, $reservation->reserved_quantity);
    }

    #[Test]
    public function it_throws_exception_when_increasing_quantity_with_insufficient_stock(): void
    {
        $product = $this->createProductWithStock(15);
        $saleOrder = $this->createSaleOrder();

        $otherItem = $this->createSaleOrderItem($saleOrder, $product, 10);
        $this->service->createReservation($otherItem);

        $item = $this->createSaleOrderItem($saleOrder, $product, 3);
        $reservation = $this->service->createReservation($item);
        $oldQuantity = 3;

        $item->quantity = 10;
        $item->save();

        $this->expectException(InsufficientStockException::class);

        try {
            $this->service->updateReservation($item, $oldQuantity);
        } catch (InsufficientStockException $e) {
            $reservation->refresh();
            $this->assertEquals(3, $reservation->reserved_quantity);
            throw $e;
        }
    }

    #[Test]
    public function it_deletes_reservation(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);
        $reservation = $this->service->createReservation($item);
        $reservationId = $reservation->id;

        $this->service->deleteReservation($item);

        $this->assertDatabaseMissing('stock_reservations', [
            'id' => $reservationId,
        ]);
    }

    #[Test]
    public function it_handles_delete_when_reservation_does_not_exist(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        $this->service->deleteReservation($item);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_releases_all_reservations_for_sale_order(): void
    {
        $unit = Unit::factory()->create();
        $product1 = $this->createProductWithStock(100, $unit);
        $product2 = $this->createProductWithStock(100, $unit);
        $saleOrder = $this->createSaleOrder();

        $item1 = $this->createSaleOrderItem($saleOrder, $product1, 10);
        $item2 = $this->createSaleOrderItem($saleOrder, $product2, 20);
        $this->service->createReservation($item1);
        $this->service->createReservation($item2);

        $this->service->releaseReservations($saleOrder);

        $this->assertDatabaseMissing('stock_reservations', [
            'sale_order_id' => $saleOrder->id,
        ]);
    }

    #[Test]
    public function it_handles_release_when_no_reservations_exist(): void
    {
        $saleOrder = $this->createSaleOrder();

        $this->service->releaseReservations($saleOrder);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_calculates_available_stock_correctly(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();

        $item1 = $this->createSaleOrderItem($saleOrder, $product, 10);
        $item2 = $this->createSaleOrderItem($saleOrder, $product, 20);
        $this->service->createReservation($item1);
        $this->service->createReservation($item2);

        $availableStock = $this->service->getAvailableStock($product->fresh());

        $this->assertEquals(70, $availableStock);
    }

    #[Test]
    public function it_excludes_item_when_calculating_available_stock(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();

        $item1 = $this->createSaleOrderItem($saleOrder, $product, 10);
        $item2 = $this->createSaleOrderItem($saleOrder, $product, 20);
        $this->service->createReservation($item1);
        $this->service->createReservation($item2);

        $availableStock = $this->service->getAvailableStock($product->fresh(), $item1->id);

        $this->assertEquals(80, $availableStock);
    }

    #[Test]
    public function it_ignores_expired_reservations_in_available_stock_calculation(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        StockReservation::create([
            'code' => 'RSV-'.now()->format('His'),
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 10,
            'expires_at' => now()->subHour(),
        ]);

        $availableStock = $this->service->getAvailableStock($product->fresh());

        $this->assertEquals(100, $availableStock);
    }

    #[Test]
    public function it_cleans_up_expired_reservations(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item1 = $this->createSaleOrderItem($saleOrder, $product, 10);
        $item2 = $this->createSaleOrderItem($saleOrder, $product, 20);

        StockReservation::create([
            'code' => 'RSV-'.now()->format('His').'-1',
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item1->id,
            'reserved_quantity' => 10,
            'expires_at' => now()->subHour(),
        ]);

        StockReservation::create([
            'code' => 'RSV-'.now()->format('His').'-2',
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item2->id,
            'reserved_quantity' => 20,
            'expires_at' => now()->addHours(24),
        ]);

        $count = $this->service->cleanupExpiredReservations();

        $this->assertEquals(1, $count);
        $this->assertDatabaseMissing('stock_reservations', [
            'sale_order_item_id' => $item1->id,
        ]);
        $this->assertDatabaseHas('stock_reservations', [
            'sale_order_item_id' => $item2->id,
        ]);
    }

    #[Test]
    public function it_returns_zero_when_no_expired_reservations_to_cleanup(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);
        $this->service->createReservation($item);

        $count = $this->service->cleanupExpiredReservations();

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function it_uses_database_transaction_for_create_reservation(): void
    {
        $product = $this->createProductWithStock(100);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($callback) => $callback());

        $this->service->createReservation($item);
    }

    #[Test]
    public function it_rolls_back_transaction_on_insufficient_stock(): void
    {
        $product = $this->createProductWithStock(5);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);

        $this->expectException(InsufficientStockException::class);

        try {
            $this->service->createReservation($item);
        } catch (InsufficientStockException $e) {
            $this->assertDatabaseMissing('stock_reservations', [
                'product_id' => $product->id,
            ]);
            throw $e;
        }
    }

    #[Test]
    public function it_returns_zero_for_available_stock_when_fully_reserved(): void
    {
        $product = $this->createProductWithStock(10);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 10);
        $this->service->createReservation($item);

        $availableStock = $this->service->getAvailableStock($product->fresh());

        $this->assertEquals(0, $availableStock);
    }

    #[Test]
    public function it_returns_zero_for_available_stock_when_over_reserved(): void
    {
        $product = $this->createProductWithStock(10);
        $saleOrder = $this->createSaleOrder();
        $item = $this->createSaleOrderItem($saleOrder, $product, 5);

        StockReservation::create([
            'code' => 'RSV-'.now()->format('His'),
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'sale_order_item_id' => $item->id,
            'reserved_quantity' => 15,
            'expires_at' => now()->addHours(24),
        ]);

        $availableStock = $this->service->getAvailableStock($product->fresh());

        $this->assertEquals(0, $availableStock);
    }

    private function createProductWithStock(int $stock, ?Unit $unit = null): Product
    {
        $attributes = ['stock_quantity' => $stock];

        if ($unit !== null) {
            $attributes['unit_id'] = $unit->id;
        }

        return Product::factory()->create($attributes);
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
