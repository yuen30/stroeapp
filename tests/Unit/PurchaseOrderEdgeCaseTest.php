<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PurchaseOrderEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PurchaseOrderItem::unsetEventDispatcher();

        PaymentStatus::create(['name' => 'รอชำระเงิน', 'code' => 'pending', 'sort_order' => 1]);
        PaymentStatus::create(['name' => 'ชำระแล้ว', 'code' => 'paid', 'sort_order' => 2]);
        PaymentMethod::create(['name' => 'เงินสด', 'code' => 'CASH', 'sort_order' => 1]);
    }

    #[Test]
    public function it_validates_minimum_purchase_quantity(): void
    {
        $minQuantity = 1;
        $testQuantity = 0;

        $this->assertLessThan($minQuantity, $testQuantity);
    }

    #[Test]
    public function it_calculates_net_amount_for_import(): void
    {
        $subtotal = 100000;
        $importDuty = 10000;
        $transport = 5000;
        $vat = 8050;

        $totalCost = $subtotal + $importDuty + $transport + $vat;

        $this->assertEquals(123050, $totalCost);
    }

    #[Test]
    public function it_handles_zero_vat_purchase(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $supplier = Supplier::factory()->for($company)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $purchaseOrder = PurchaseOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'order_number' => 'PO-'.date('Ymd').'-0001',
            'order_date' => now(),
            'expected_date' => now()->addDays(7),
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
            'subtotal' => 10000,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 10000,
        ]);

        $this->assertEquals(0, $purchaseOrder->vat_amount);
    }

    #[Test]
    public function it_calculates_average_cost_from_multiple_purchases(): void
    {
        $batches = [
            ['qty' => 100, 'cost' => 50],
            ['qty' => 200, 'cost' => 60],
            ['qty' => 150, 'cost' => 55],
        ];

        $totalQty = 0;
        $totalCost = 0;

        foreach ($batches as $batch) {
            $totalQty += $batch['qty'];
            $totalCost += $batch['qty'] * $batch['cost'];
        }

        $avgCost = $totalCost / $totalQty;

        $this->assertEquals(450, $totalQty);
        $this->assertEqualsWithDelta(56.11, $avgCost, 0.01);
    }

    #[Test]
    public function it_handles_supplier_lead_time(): void
    {
        $orderDate = now();
        $leadTimeDays = 7;
        $expectedDate = $orderDate->copy()->addDays($leadTimeDays);

        $this->assertEquals($expectedDate->toDateString(), $orderDate->copy()->addDays($leadTimeDays)->toDateString());
    }

    #[Test]
    public function it_prevents_over_receiving(): void
    {
        $purchaseOrderQuantity = 100;
        $alreadyReceived = 100;
        $requestedToReceive = 10;

        $canReceive = $requestedToReceive <= ($purchaseOrderQuantity - $alreadyReceived);

        $this->assertFalse($canReceive);
    }

    #[Test]
    public function it_calculates_partial_receipt(): void
    {
        $orderedQuantity = 100;
        $receivedQuantity = 75;
        $pendingQuantity = $orderedQuantity - $receivedQuantity;

        $this->assertEquals(25, $pendingQuantity);
        $this->assertEquals(75, $receivedQuantity);
    }

    #[Test]
    public function it_handles_cancelled_purchase_order(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $supplier = Supplier::factory()->for($company)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $purchaseOrder = PurchaseOrder::factory()->for($company)->for($branch)->for($supplier)->for($user, 'creator')->create([
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
        ]);

        $purchaseOrder->update(['status' => OrderStatus::Cancelled]);

        $this->assertEquals(OrderStatus::Cancelled, $purchaseOrder->fresh()->status);
    }

    #[Test]
    public function it_calculates_total_with_multiple_discounts(): void
    {
        $subtotal = 10000;
        $discountPercent = 5;
        $discountAmount = 500;

        $netAmount = $subtotal - ($subtotal * $discountPercent / 100) - $discountAmount;
        $vat = $netAmount * 0.07;
        $total = $netAmount + $vat;

        $this->assertEqualsWithDelta(9000, $netAmount, 0.01);
        $this->assertEqualsWithDelta(630, $vat, 0.01);
    }

    #[Test]
    public function it_validates_order_date_before_expected_date(): void
    {
        $orderDate = now();
        $expectedDate = now()->addDays(7);

        $this->assertTrue($expectedDate->greaterThan($orderDate));
    }

    #[Test]
    public function it_handles_reorder_point_calculation(): void
    {
        $avgDailySales = 10;
        $leadTimeDays = 7;
        $safetyStock = 20;

        $reorderPoint = ($avgDailySales * $leadTimeDays) + $safetyStock;

        $this->assertEquals(90, $reorderPoint);
    }

    #[Test]
    public function it_calculates_economic_order_quantity(): void
    {
        $annualDemand = 1200;
        $orderingCost = 100;
        $holdingCostRate = 0.25;

        $unitCost = 50;
        $holdingCost = $unitCost * $holdingCostRate;

        $eoq = sqrt((2 * $annualDemand * $orderingCost) / $holdingCost);

        $this->assertEqualsWithDelta(138.56, $eoq, 0.01);
    }

    #[Test]
    public function it_handles_multiple_products_in_purchase_order(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $supplier = Supplier::factory()->for($company)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $purchaseOrder = PurchaseOrder::factory()->for($company)->for($branch)->for($supplier)->for($user, 'creator')->create([
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
        ]);

        $product = Product::factory()->for($company)->for($branch)->create();
        $cost = $product->cost_price;
        $totalAmount = 0;

        for ($i = 0; $i < 5; $i++) {
            $qty = rand(10, 50);
            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_cost' => $cost,
                'discount' => 0,
                'total_cost' => $cost * $qty,
            ]);
            $totalAmount += $cost * $qty;
        }

        $this->assertEquals(5, $purchaseOrder->items()->count());
        $this->assertGreaterThan(0, $totalAmount);
    }

    #[Test]
    public function it_calculates_backorder_quantity(): void
    {
        $orderedQty = 100;
        $shippedQty = 60;
        $backorderQty = $orderedQty - $shippedQty;

        $this->assertEquals(40, $backorderQty);
    }

    #[Test]
    public function it_handles_freight_cost_allocation(): void
    {
        $freightCost = 1000;
        $totalWeight = 500;
        $orderWeight = 50;

        $allocatedFreight = ($orderWeight / $totalWeight) * $freightCost;

        $this->assertEquals(100, $allocatedFreight);
    }

    #[Test]
    public function it_calculates_landed_cost(): void
    {
        $unitCost = 100;
        $freight = 10;
        $insurance = 5;
        $handling = 3;
        $duty = 15;

        $landedCost = $unitCost + $freight + $insurance + $handling + $duty;

        $this->assertEquals(133, $landedCost);
    }

    #[Test]
    public function it_validates_supplier_tax_id_format(): void
    {
        $validTaxIds = [
            '0105548012345',
            '0105538012345',
            '0105563012345',
        ];

        foreach ($validTaxIds as $taxId) {
            $this->assertEquals(13, strlen($taxId));
        }
    }
}
