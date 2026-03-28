<?php

namespace Tests\Feature\Filament;

use App\Enums\OrderStatus;
use App\Filament\Resources\SaleOrders\Pages\ListSaleOrders;
use App\Filament\Resources\SaleOrders\Pages\ViewSaleOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Customer $customer;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->product = Product::factory()->create(['stock_quantity' => 100]);
    }

    #[Test]
    public function it_can_view_sale_orders_list(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListSaleOrders::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_see_sale_orders_in_table(): void
    {
        SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-TEST-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListSaleOrders::class)
            ->assertSee('INV-TEST-001');
    }

    #[Test]
    public function it_can_view_sale_order_details(): void
    {
        $saleOrder = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-VIEW-001',
            'created_by' => $this->user->id,
            'status' => OrderStatus::Draft,
        ]);

        SaleOrderItem::factory()->for($saleOrder)->for($this->product)->create([
            'quantity' => 10,
            'unit_price' => 1000,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewSaleOrder::class, ['record' => $saleOrder->getKey()])
            ->assertSee('INV-VIEW-001')
            ->assertSee($this->product->name);
    }

    #[Test]
    public function it_can_confirm_sale_order(): void
    {
        $saleOrder = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-CONF-001',
            'created_by' => $this->user->id,
            'status' => OrderStatus::Draft,
        ]);

        SaleOrderItem::factory()->for($saleOrder)->for($this->product)->create([
            'quantity' => 5,
            'unit_price' => 100,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewSaleOrder::class, ['record' => $saleOrder->getKey()])
            ->callAction('confirm');

        $saleOrder->refresh();
        $this->assertEquals(OrderStatus::Confirmed, $saleOrder->status);
    }

    #[Test]
    public function it_can_cancel_sale_order(): void
    {
        $saleOrder = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-CANCEL-001',
            'created_by' => $this->user->id,
            'status' => OrderStatus::Draft,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewSaleOrder::class, ['record' => $saleOrder->getKey()])
            ->callAction('cancel');

        $saleOrder->refresh();
        $this->assertEquals(OrderStatus::Cancelled, $saleOrder->status);
    }

    #[Test]
    public function it_deducts_stock_on_confirm(): void
    {
        $initialStock = $this->product->stock_quantity;

        $saleOrder = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-STOCK-001',
            'created_by' => $this->user->id,
            'status' => OrderStatus::Draft,
        ]);

        SaleOrderItem::factory()->for($saleOrder)->for($this->product)->create([
            'quantity' => 10,
            'unit_price' => 100,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewSaleOrder::class, ['record' => $saleOrder->getKey()])
            ->callAction('confirm');

        $this->product->refresh();
        $this->assertEquals($initialStock - 10, $this->product->stock_quantity);
    }

    #[Test]
    public function it_restores_stock_on_cancel(): void
    {
        $initialStock = $this->product->stock_quantity;

        $saleOrder = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-RESTORE-001',
            'created_by' => $this->user->id,
            'status' => OrderStatus::Draft,
        ]);

        SaleOrderItem::factory()->for($saleOrder)->for($this->product)->create([
            'quantity' => 10,
            'unit_price' => 100,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewSaleOrder::class, ['record' => $saleOrder->getKey()])
            ->callAction('confirm');

        Livewire::actingAs($this->user)
            ->test(ViewSaleOrder::class, ['record' => $saleOrder->getKey()])
            ->callAction('cancel');

        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock_quantity);
    }

    #[Test]
    public function it_can_search_by_invoice_number(): void
    {
        SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-SEARCH-001',
            'created_by' => $this->user->id,
        ]);

        SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-OTHER-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListSaleOrders::class)
            ->searchTable('INV-SEARCH-001')
            ->assertCanSeeTableRecords(SaleOrder::where('invoice_number', 'INV-SEARCH-001')->get())
            ->assertCanNotSeeTableRecords(SaleOrder::where('invoice_number', 'INV-OTHER-001')->get());
    }

    #[Test]
    public function it_shows_multiple_orders(): void
    {
        $order1 = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-001',
            'created_by' => $this->user->id,
        ]);

        $order2 = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-002',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListSaleOrders::class)
            ->assertCanSeeTableRecords([$order1, $order2]);
    }

    #[Test]
    public function it_displays_customer_name(): void
    {
        SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-CUST-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListSaleOrders::class)
            ->assertSee($this->customer->name);
    }

    #[Test]
    public function it_displays_order_items(): void
    {
        $saleOrder = SaleOrder::factory()->for($this->customer)->create([
            'invoice_number' => 'INV-ITEMS-001',
            'created_by' => $this->user->id,
        ]);

        SaleOrderItem::factory()->for($saleOrder)->for($this->product)->create([
            'quantity' => 5,
            'unit_price' => 200,
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewSaleOrder::class, ['record' => $saleOrder->getKey()])
            ->assertSee('5')
            ->assertSee('200.00');
    }
}
