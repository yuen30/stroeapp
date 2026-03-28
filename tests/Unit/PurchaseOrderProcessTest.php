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
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PurchaseOrderProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentStatus::create(['name' => 'รอชำระเงิน', 'code' => 'pending', 'sort_order' => 1]);
        PaymentStatus::create(['name' => 'ชำระแล้ว', 'code' => 'paid', 'sort_order' => 2]);

        PaymentMethod::create(['name' => 'เงินสด', 'code' => 'CASH', 'sort_order' => 1]);
        PaymentMethod::create(['name' => 'โอนเงิน', 'code' => 'TRANSFER', 'sort_order' => 2]);
    }

    #[Test]
    public function it_can_create_purchase_order_with_items(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $supplier = Supplier::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create();
        $user = User::factory()->for($company)->for($branch)->create();

        $purchaseOrder = PurchaseOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'order_number' => 'PO-'.date('Ymd').'-0001',
            'order_date' => now(),
            'expected_date' => now()->addDays(7),
            'status' => OrderStatus::Draft,
            'payment_status_id' => PaymentStatus::first()->id,
            'payment_method_id' => PaymentMethod::first()->id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'vat_amount' => 0,
            'total_amount' => 0,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'received_quantity' => 0,
            'unit_price' => 50,
            'discount' => 0,
            'total_price' => 2500,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'status' => OrderStatus::Draft,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);
    }

    #[Test]
    public function it_confirms_purchase_order(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create(['status' => OrderStatus::Draft]);

        $purchaseOrder->update(['status' => OrderStatus::Confirmed]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'status' => OrderStatus::Confirmed,
        ]);
    }

    #[Test]
    public function it_cancels_purchase_order(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create(['status' => OrderStatus::Draft]);

        $purchaseOrder->update(['status' => OrderStatus::Cancelled]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'status' => OrderStatus::Cancelled,
        ]);
    }

    #[Test]
    public function it_calculates_purchase_order_totals(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $supplier = Supplier::factory()->for($company)->create();
        $unit1 = Unit::factory()->create();
        $unit2 = Unit::factory()->create();
        $product1 = Product::factory()->for($company)->for($branch)->for($unit1)->create();
        $product2 = Product::factory()->for($company)->for($branch)->for($unit2)->create();
        $user = User::factory()->for($company)->for($branch)->create();

        $purchaseOrder = PurchaseOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'order_number' => 'PO-'.date('Ymd').'-0001',
            'order_date' => now(),
            'status' => OrderStatus::Draft,
            'payment_status_id' => PaymentStatus::first()->id,
            'payment_method_id' => PaymentMethod::first()->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'received_quantity' => 0,
            'unit_price' => 100,
            'discount' => 0,
            'total_price' => 1000,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product2->id,
            'quantity' => 20,
            'received_quantity' => 0,
            'unit_price' => 50,
            'discount' => 100,
            'total_price' => 900,
        ]);

        $subtotal = $purchaseOrder->items()->sum('total_price');
        $vatAmount = $subtotal * 0.07;
        $totalAmount = $subtotal + $vatAmount;

        $purchaseOrder->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
        ]);

        $this->assertEquals(1900, $subtotal);
        $this->assertEquals(133, $vatAmount);
        $this->assertEquals(2033, $totalAmount);
    }
}
