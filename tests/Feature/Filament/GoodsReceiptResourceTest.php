<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\GoodsReceipts\Pages\ListGoodsReceipts;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoodsReceiptResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::factory()->create();
        $this->user = User::factory()->for($company)->create();
    }

    #[Test]
    public function it_can_view_goods_receipts_list(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListGoodsReceipts::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_see_goods_receipts_in_table(): void
    {
        $supplier = Supplier::factory()->create();

        $goodsReceipt = GoodsReceipt::factory()->for($supplier)->create([
            'receipt_number' => 'GR-TEST-001',
            'created_by' => $this->user->id,
            'is_standalone' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListGoodsReceipts::class)
            ->assertSee('GR-TEST-001');
    }

    #[Test]
    public function it_can_search_goods_receipts(): void
    {
        $supplier = Supplier::factory()->create();

        GoodsReceipt::factory()->for($supplier)->create([
            'receipt_number' => 'GR-SEARCH-001',
            'created_by' => $this->user->id,
            'is_standalone' => true,
        ]);

        GoodsReceipt::factory()->for($supplier)->create([
            'receipt_number' => 'GR-OTHER-001',
            'created_by' => $this->user->id,
            'is_standalone' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListGoodsReceipts::class)
            ->searchTable('GR-SEARCH-001')
            ->assertCanSeeTableRecords(GoodsReceipt::where('receipt_number', 'GR-SEARCH-001')->get())
            ->assertCanNotSeeTableRecords(GoodsReceipt::where('receipt_number', 'GR-OTHER-001')->get());
    }
}
