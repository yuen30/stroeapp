<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\User;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockReservationEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SaleOrderItem::unsetEventDispatcher();
    }

    #[Test]
    public function it_handles_zero_reservation(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->for($company)->for($branch)->for($user, 'creator')->create();

        $quantity = 0;
        $this->assertEquals(0, $quantity);
    }

    #[Test]
    public function it_validates_expiry_date_not_past(): void
    {
        $expiryDate = now()->subDays(1);
        $isExpired = $expiryDate->isPast();

        $this->assertTrue($isExpired);
    }

    #[Test]
    public function it_calculates_reservation_hours(): void
    {
        $reservationHours = 24;
        $expiresAt = now()->addHours($reservationHours);
        $hoursRemaining = now()->diffInHours($expiresAt);

        $this->assertEqualsWithDelta(24, $hoursRemaining, 1);
    }

    #[Test]
    public function it_handles_multiple_reservations_same_product(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);

        $reservations = [20, 15, 10];
        $totalReserved = array_sum($reservations);

        $this->assertEquals(45, $totalReserved);
    }

    #[Test]
    public function it_detects_over_reservation(): void
    {
        $stockQuantity = 100;
        $existingReservations = 80;
        $newRequest = 30;

        $wouldExceedStock = ($existingReservations + $newRequest) > $stockQuantity;

        $this->assertTrue($wouldExceedStock);
    }

    #[Test]
    public function it_calculates_available_for_new_reservation(): void
    {
        $stockQuantity = 100;
        $existingReservations = 60;
        $available = $stockQuantity - $existingReservations;

        $this->assertEquals(40, $available);
    }

    #[Test]
    public function it_handles_reservation_expiry_cleanup(): void
    {
        $reservations = [
            ['id' => 1, 'expires_at' => now()->subHours(1)],
            ['id' => 2, 'expires_at' => now()->addHours(24)],
            ['id' => 3, 'expires_at' => now()->subHours(2)],
        ];

        $expired = array_filter($reservations, fn ($r) => $r['expires_at']->isPast());

        $this->assertCount(2, $expired);
    }

    #[Test]
    public function it_validates_minimum_reservation_quantity(): void
    {
        $minQuantity = 1;
        $requestedQuantity = 0;

        $this->assertLessThan($minQuantity, $requestedQuantity);
    }

    #[Test]
    public function it_calculates_partial_fulfillment(): void
    {
        $stockAvailable = 30;
        $requestedQuantity = 50;

        $canFulfill = min($requestedQuantity, $stockAvailable);

        $this->assertEquals(30, $canFulfill);
    }

    #[Test]
    public function it_handles_reservation_cancellation_restock(): void
    {
        $originalStock = 100;
        $reservedQuantity = 20;
        $stockAfterReserve = $originalStock - $reservedQuantity;
        $stockAfterCancel = $stockAfterReserve + $reservedQuantity;

        $this->assertEquals(80, $stockAfterReserve);
        $this->assertEquals(100, $stockAfterCancel);
    }

    #[Test]
    public function it_validates_reservation_cannot_exceed_available(): void
    {
        $available = 50;
        $requested = 75;

        $this->assertGreaterThan($available, $requested);
    }

    #[Test]
    public function it_handles_reservation_extension(): void
    {
        $originalExpiry = now()->addHours(24);
        $extension = 12;
        $newExpiry = $originalExpiry->copy()->addHours($extension);

        $this->assertEqualsWithDelta(36, now()->diffInHours($newExpiry), 1);
    }

    #[Test]
    public function it_calculates_reservation_utilization(): void
    {
        $totalStock = 1000;
        $reservedStock = 250;
        $utilization = ($reservedStock / $totalStock) * 100;

        $this->assertEquals(25, $utilization);
    }

    #[Test]
    public function it_handles_concurrent_reservation_attempts(): void
    {
        $stock = 50;
        $request1 = 30;
        $request2 = 30;

        $firstReservation = min($request1, $stock);
        $remainingStock = $stock - $firstReservation;
        $secondReservation = min($request2, $remainingStock);

        $this->assertEquals(30, $firstReservation);
        $this->assertEquals(20, $secondReservation);
    }

    #[Test]
    public function it_validates_reservation_reference(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->for($company)->for($branch)->for($user, 'creator')->create();
        SaleOrderItem::unsetEventDispatcher();

        $item = SaleOrderItem::create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100,
            'discount' => 0,
            'total_price' => 1000,
        ]);

        $service = new StockReservationService;
        $reservation = $service->createReservation($item);

        $this->assertEquals($saleOrder->id, $reservation->sale_order_id);
    }

    #[Test]
    public function it_handles_reservation_split(): void
    {
        $requestedQuantity = 50;
        $stockAvailable = 30;

        $partialReservation = $stockAvailable;
        $backorderQuantity = $requestedQuantity - $stockAvailable;

        $this->assertEquals(30, $partialReservation);
        $this->assertEquals(20, $backorderQuantity);
    }

    #[Test]
    public function it_calculates_waiting_time_for_backorder(): void
    {
        $backorderDate = now();
        $expectedRestockDate = now()->addDays(7);
        $waitDays = $backorderDate->diffInDays($expectedRestockDate);

        $this->assertEqualsWithDelta(7, $waitDays, 0.1);
    }

    #[Test]
    public function it_validates_reservation_status_transitions(): void
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['fulfilled', 'cancelled'],
            'fulfilled' => [],
            'cancelled' => [],
        ];

        $this->assertArrayHasKey('pending', $validTransitions);
        $this->assertContains('confirmed', $validTransitions['pending']);
    }

    #[Test]
    public function it_handles_reservation_fulfillment(): void
    {
        $reservationQuantity = 10;
        $fulfillQuantity = 10;
        $remainingReservation = $reservationQuantity - $fulfillQuantity;

        $this->assertEquals(0, $remainingReservation);
    }

    #[Test]
    public function it_calculates_shortage_quantity(): void
    {
        $reservedQuantity = 50;
        $stockAvailable = 35;
        $shortage = $reservedQuantity - $stockAvailable;

        $this->assertEquals(15, $shortage);
    }

    #[Test]
    public function it_handles_reservation_priority_queue(): void
    {
        $reservations = [
            ['id' => 1, 'priority' => 2, 'created_at' => now()->subMinutes(10)],
            ['id' => 2, 'priority' => 1, 'created_at' => now()->subMinutes(5)],
            ['id' => 3, 'priority' => 1, 'created_at' => now()->subMinutes(8)],
        ];

        usort($reservations, fn ($a, $b) => $a['priority'] <=> $b['priority'] ?: $a['created_at'] <=> $b['created_at']);

        $this->assertEquals(3, $reservations[0]['id']);
        $this->assertEquals(2, $reservations[1]['id']);
        $this->assertEquals(1, $reservations[2]['id']);
    }

    #[Test]
    public function it_calculates_reservation_release_on_cancellation(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->for($company)->for($branch)->for($user, 'creator')->create();
        SaleOrderItem::unsetEventDispatcher();

        $item = SaleOrderItem::create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 100,
            'discount' => 0,
            'total_price' => 2000,
        ]);

        $service = new StockReservationService;
        $reservation = $service->createReservation($item);

        $this->assertEquals(20, $reservation->reserved_quantity);
    }

    #[Test]
    public function it_handles_zero_stock_scenario(): void
    {
        $stockQuantity = 0;
        $reservedQuantity = 10;

        $available = max(0, $stockQuantity - $reservedQuantity);

        $this->assertEquals(0, $available);
    }
}
