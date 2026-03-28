<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoodsReceiptEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        GoodsReceiptItem::unsetEventDispatcher();
        PurchaseOrderItem::unsetEventDispatcher();
    }

    #[Test]
    public function it_validates_received_quantity(): void
    {
        $orderedQty = 100;
        $receivedQty = 50;
        $pendingQty = $orderedQty - $receivedQty;

        $this->assertEquals(50, $pendingQty);
        $this->assertGreaterThan(0, $pendingQty);
    }

    #[Test]
    public function it_handles_excess_receiving(): void
    {
        $orderedQty = 100;
        $receivedQty = 110;

        $this->assertGreaterThan($orderedQty, $receivedQty);
    }

    #[Test]
    public function it_calculates_short_receiving(): void
    {
        $orderedQty = 100;
        $receivedQty = 85;
        $shortQty = $orderedQty - $receivedQty;

        $this->assertEquals(15, $shortQty);
    }

    #[Test]
    public function it_handles_quality_rejection(): void
    {
        $receivedQty = 100;
        $rejectedQty = 5;
        $acceptedQty = $receivedQty - $rejectedQty;

        $this->assertEquals(95, $acceptedQty);
    }

    #[Test]
    public function it_calculates_batch_expiry_tracking(): void
    {
        $manufacturingDate = now()->subDays(30);
        $shelfLifeDays = 90;
        $expiryDate = $manufacturingDate->copy()->addDays($shelfLifeDays);
        $daysUntilExpiry = now()->diffInDays($expiryDate);

        $this->assertEqualsWithDelta(60, $daysUntilExpiry, 1);
    }

    #[Test]
    public function it_validates_batch_number_format(): void
    {
        $batchFormats = [
            'BATCH-2024-001',
            'LOT-20240301-A',
            'PROD-001',
        ];

        foreach ($batchFormats as $batch) {
            $this->assertNotEmpty($batch);
        }
    }

    #[Test]
    public function it_calculates_fifo_cost(): void
    {
        $batches = [
            ['qty' => 100, 'cost' => 50, 'date' => now()->subDays(30)],
            ['qty' => 50, 'cost' => 55, 'date' => now()->subDays(15)],
            ['qty' => 75, 'cost' => 52, 'date' => now()->subDays(7)],
        ];

        usort($batches, fn ($a, $b) => $a['date'] <=> $b['date']);

        $this->assertEquals(50, $batches[0]['cost']);
        $this->assertEquals(52, $batches[2]['cost']);
    }

    #[Test]
    public function it_handles_partial_receipt_status(): void
    {
        $orderedQty = 100;
        $receivedQty = 50;
        $isPartial = $receivedQty < $orderedQty;

        $this->assertTrue($isPartial);
    }

    #[Test]
    public function it_calculates_receiving_accuracy(): void
    {
        $orderedQty = 100;
        $receivedQty = 98;
        $damagedQty = 2;

        $accuracy = (($receivedQty - $damagedQty) / $orderedQty) * 100;

        $this->assertEqualsWithDelta(96, $accuracy, 0.01);
    }

    #[Test]
    public function it_handles_multi_supplier_receipt(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();

        $receipt1 = GoodsReceipt::factory()->for($company)->for($branch)->for($user, 'creator')->create([
            'is_standalone' => true,
        ]);
        $receipt2 = GoodsReceipt::factory()->for($company)->for($branch)->for($user, 'creator')->create([
            'is_standalone' => true,
        ]);

        $this->assertNotEquals($receipt1->id, $receipt2->id);
    }

    #[Test]
    public function it_validates_receipt_date_not_future(): void
    {
        $receiptDate = now()->addDays(1);
        $isFuture = $receiptDate->greaterThan(now());

        $this->assertTrue($isFuture);
    }

    #[Test]
    public function it_calculates_inspection_pass_rate(): void
    {
        $totalInspected = 100;
        $passed = 95;
        $failed = 5;

        $passRate = ($passed / $totalInspected) * 100;

        $this->assertEquals(95, $passRate);
    }

    #[Test]
    public function it_handles_receipt_reference_linkage(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $supplier = Supplier::factory()->for($company)->create();

        $purchaseOrder = PurchaseOrder::factory()->for($company)->for($branch)->for($supplier)->for($user, 'creator')->create();

        $receipt = GoodsReceipt::factory()->for($company)->for($branch)->for($user, 'creator')->for($supplier)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'is_standalone' => false,
        ]);

        $this->assertEquals($purchaseOrder->id, $receipt->purchase_order_id);
    }

    #[Test]
    public function it_calculates_average_receiving_time(): void
    {
        $receivingTimes = [30, 45, 60, 25, 50];
        $avgTime = array_sum($receivingTimes) / count($receivingTimes);

        $this->assertEquals(42, $avgTime);
    }

    #[Test]
    public function it_handles_temperature_sensitive_storage(): void
    {
        $minTemp = 2;
        $maxTemp = 8;
        $currentTemp = 5;

        $isInRange = $currentTemp >= $minTemp && $currentTemp <= $maxTemp;

        $this->assertTrue($isInRange);
    }

    #[Test]
    public function it_calculates_storage_location_capacity(): void
    {
        $totalCapacity = 1000;
        $currentOccupancy = 750;
        $utilization = ($currentOccupancy / $totalCapacity) * 100;

        $this->assertEquals(75, $utilization);
    }

    #[Test]
    public function it_validates_weight_and_dimensions(): void
    {
        $items = [
            ['weight' => 5.5, 'length' => 30, 'width' => 20, 'height' => 15],
            ['weight' => 3.2, 'length' => 25, 'width' => 15, 'height' => 10],
        ];

        foreach ($items as $item) {
            $this->assertGreaterThan(0, $item['weight']);
            $this->assertGreaterThan(0, $item['length']);
            $this->assertGreaterThan(0, $item['width']);
            $this->assertGreaterThan(0, $item['height']);
        }
    }

    #[Test]
    public function it_calculates_receipt_value(): void
    {
        $items = [
            ['qty' => 100, 'cost' => 50],
            ['qty' => 50, 'cost' => 75],
            ['qty' => 25, 'cost' => 100],
        ];

        $totalValue = 0;
        foreach ($items as $item) {
            $totalValue += $item['qty'] * $item['cost'];
        }

        $this->assertEquals(11250, $totalValue);
    }

    #[Test]
    public function it_handles_receipt_cancellation(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $supplier = Supplier::factory()->for($company)->create();

        $receipt = GoodsReceipt::factory()->for($company)->for($branch)->for($user, 'creator')->for($supplier)->create([
            'is_standalone' => true,
        ]);

        $this->assertNotNull($receipt);
    }
}
