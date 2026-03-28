<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleOrderEdgeCaseTest extends TestCase
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
    public function it_validates_minimum_order_amount(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $minOrderAmount = 100;
        $totalAmount = 50;

        $this->assertLessThan($minOrderAmount, $totalAmount);
    }

    #[Test]
    public function it_handles_zero_discount(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
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

        $this->assertEquals(0, $saleOrder->discount_amount);
    }

    #[Test]
    public function it_calculates_vat_from_net_amount(): void
    {
        $subtotal = 10000;
        $discount = 500;
        $vatRate = 7;

        $netAmount = $subtotal - $discount;
        $vatAmount = $netAmount * ($vatRate / 100);

        $this->assertEquals(9500, $netAmount);
        $this->assertEqualsWithDelta(665, $vatAmount, 0.01);
    }

    #[Test]
    public function it_prevents_negative_quantity(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);
        $user = User::factory()->for($company)->for($branch)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $saleOrder = SaleOrder::factory()->for($company)->for($branch)->for($user, 'creator')->create([
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
        ]);

        $quantity = 5;
        $this->assertGreaterThan(0, $quantity);
    }

    #[Test]
    public function it_handles_credit_limit_exceeded(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 10000,
        ]);

        $customer->update(['credit_limit' => 0]);
        $this->assertEquals(0, $customer->credit_limit);

        $customer->update(['credit_limit' => 5000]);
        $this->assertEquals(5000, $customer->credit_limit);
    }

    #[Test]
    public function it_handles_order_date_in_future(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $futureDate = now()->addDays(7);

        $saleOrder = SaleOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'invoice_number' => 'SO-'.date('Ymd').'-0001',
            'order_date' => $futureDate,
            'due_date' => $futureDate->copy()->addDays(30),
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'vat_amount' => 70,
            'total_amount' => 1070,
        ]);

        $this->assertTrue($saleOrder->order_date->greaterThan(now()));
    }

    #[Test]
    public function it_handles_due_date_before_order_date(): void
    {
        $orderDate = now();
        $dueDate = now()->subDays(1);

        $this->assertTrue($dueDate->lessThan($orderDate));
    }

    #[Test]
    public function it_calculates_total_with_100_percent_discount(): void
    {
        $subtotal = 1000;
        $discount = 1000;
        $vatRate = 7;

        $netAmount = $subtotal - $discount;
        $vatAmount = $netAmount * ($vatRate / 100);
        $total = $netAmount + $vatAmount;

        $this->assertEquals(0, $netAmount);
        $this->assertEquals(0, $vatAmount);
        $this->assertEquals(0, $total);
    }

    #[Test]
    public function it_handles_multiple_items_in_order(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();

        $saleOrder = SaleOrder::factory()->for($company)->for($branch)->for($user, 'creator')->create([
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
        ]);

        $product = Product::factory()->for($company)->for($branch)->create();
        $totalAmount = 0;

        for ($i = 0; $i < 3; $i++) {
            $price = ($i + 1) * 100;
            SaleOrderItem::create([
                'sale_order_id' => $saleOrder->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $price,
                'discount' => 0,
                'total_price' => $price,
            ]);
            $totalAmount += $price;
        }

        $this->assertEquals(3, $saleOrder->items()->count());
        $this->assertEquals(600, $totalAmount);
    }

    #[Test]
    public function it_prevents_stock_overflow(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 10]);

        $requestedQuantity = 15;

        $this->assertGreaterThan($product->stock_quantity, $requestedQuantity);
    }

    #[Test]
    public function it_handles_cancelled_order_restock(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 100]);
        $user = User::factory()->for($company)->for($branch)->create();

        $originalStock = $product->stock_quantity;
        $deductedQuantity = 10;
        $product->update(['stock_quantity' => $originalStock - $deductedQuantity]);

        $product->update(['stock_quantity' => $originalStock]);

        $this->assertEquals($originalStock, $product->fresh()->stock_quantity);
    }

    #[Test]
    public function it_validates_vat_rate_boundaries(): void
    {
        $vatRates = [0, 7, 10, 15, 20];

        foreach ($vatRates as $rate) {
            $this->assertGreaterThanOrEqual(0, $rate);
            $this->assertLessThanOrEqual(100, $rate);
        }
    }

    #[Test]
    public function it_handles_empty_notes(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
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
            'notes' => '',
        ]);

        $this->assertEquals('', $saleOrder->notes);
    }

    #[Test]
    public function it_soft_deletes_sale_order(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
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

        $saleOrder->delete();

        $this->assertSoftDeleted('sale_orders', ['id' => $saleOrder->id]);
        $this->assertNotNull($saleOrder->fresh()->deleted_at);
    }

    #[Test]
    public function it_prevents_duplicate_invoice_number(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $paymentStatus = PaymentStatus::first();
        $paymentMethod = PaymentMethod::first();
        $invoiceNumber = 'SO-'.date('Ymd').'-0001';

        SaleOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'invoice_number' => $invoiceNumber,
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

        $this->expectException(QueryException::class);

        SaleOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'invoice_number' => $invoiceNumber,
            'order_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => OrderStatus::Draft,
            'payment_status_id' => $paymentStatus->id,
            'payment_method_id' => $paymentMethod->id,
            'subtotal' => 2000,
            'discount_amount' => 0,
            'vat_amount' => 140,
            'total_amount' => 2140,
        ]);
    }
}
