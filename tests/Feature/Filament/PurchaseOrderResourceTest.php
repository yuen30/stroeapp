<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PurchaseOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_can_view_purchase_orders_list(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListPurchaseOrders::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_see_purchase_orders_in_table(): void
    {
        $supplier = Supplier::factory()->create();

        PurchaseOrder::factory()->for($supplier)->create([
            'order_number' => 'PO-TEST-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListPurchaseOrders::class)
            ->assertSee('PO-TEST-001');
    }

    #[Test]
    public function it_can_search_purchase_orders(): void
    {
        $supplier = Supplier::factory()->create();

        PurchaseOrder::factory()->for($supplier)->create([
            'order_number' => 'PO-SEARCH-001',
            'created_by' => $this->user->id,
        ]);

        PurchaseOrder::factory()->for($supplier)->create([
            'order_number' => 'PO-OTHER-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListPurchaseOrders::class)
            ->searchTable('PO-SEARCH-001')
            ->assertCanSeeTableRecords(PurchaseOrder::where('order_number', 'PO-SEARCH-001')->get())
            ->assertCanNotSeeTableRecords(PurchaseOrder::where('order_number', 'PO-OTHER-001')->get());
    }

    #[Test]
    public function it_shows_multiple_orders(): void
    {
        $supplier = Supplier::factory()->create();

        $po1 = PurchaseOrder::factory()->for($supplier)->create([
            'order_number' => 'PO-001',
            'created_by' => $this->user->id,
        ]);

        $po2 = PurchaseOrder::factory()->for($supplier)->create([
            'order_number' => 'PO-002',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListPurchaseOrders::class)
            ->assertCanSeeTableRecords([$po1, $po2]);
    }

    #[Test]
    public function it_displays_supplier_name(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'ผู้ผลิต ABC']);

        PurchaseOrder::factory()->for($supplier)->create([
            'order_number' => 'PO-SUP-001',
            'created_by' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListPurchaseOrders::class)
            ->assertSee('ผู้ผลิต ABC');
    }
}
