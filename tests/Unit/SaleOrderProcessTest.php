<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleOrderProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SaleOrderItem::unsetEventDispatcher();

        PaymentStatus::create(['name' => 'รอชำระเงิน', 'code' => 'pending', 'sort_order' => 1]);
        PaymentStatus::create(['name' => 'ชำระแล้ว', 'code' => 'paid', 'sort_order' => 2]);

        PaymentMethod::create(['name' => 'เงินสด', 'code' => 'CASH', 'sort_order' => 1]);
    }

    #[Test]
    public function it_can_create_sale_order_with_items(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);
        $user = User::factory()->for($company)->for($branch)->create();

        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $saleOrder = SaleOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'invoice_number' => 'SO-'.date('Ymd').'-0001',
            'order_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'vat_amount' => 70,
            'total_amount' => 1070,
        ]);

        SaleOrderItem::create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100,
            'discount' => 0,
            'total_price' => 1000,
        ]);

        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'status' => OrderStatus::Draft,
        ]);

        $this->assertDatabaseHas('sale_order_items', [
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
    }

    #[Test]
    public function it_confirms_sale_order(): void
    {
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        $saleOrder->update(['status' => OrderStatus::Confirmed]);

        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'status' => OrderStatus::Confirmed,
        ]);
    }

    #[Test]
    public function it_cancels_sale_order(): void
    {
        $saleOrder = SaleOrder::factory()->create(['status' => OrderStatus::Draft]);

        $saleOrder->update(['status' => OrderStatus::Cancelled]);

        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'status' => OrderStatus::Cancelled,
        ]);
    }

    #[Test]
    public function it_completes_sale_order_and_deducts_stock(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);
        $user = User::factory()->for($company)->for($branch)->create();

        $saleOrder = SaleOrder::factory()->for($company)->for($branch)->for($user, 'creator')->create(['status' => OrderStatus::Confirmed]);

        SaleOrderItem::create([
            'sale_order_id' => $saleOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100,
            'discount' => 0,
            'total_price' => 1000,
        ]);

        $saleOrder->update(['status' => OrderStatus::Completed]);

        StockMovement::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'created_by' => $user->id,
            'type' => StockMovementType::Out,
            'quantity' => 10,
            'stock_before' => 100,
            'stock_after' => 90,
        ]);

        $product->update(['stock_quantity' => 90]);

        $this->assertEquals(90, $product->fresh()->stock_quantity);
    }
}
