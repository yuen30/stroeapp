<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\Reports\GoodsReceiptReport;
use App\Filament\Pages\Reports\LowStockReport;
use App\Models\Branch;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->for($this->company)->create();
        $this->user = User::factory()->for($this->company)->create();
    }

    #[Test]
    public function goods_receipt_report_route_renders_successfully(): void
    {
        $this->actingAs($this->user)
            ->get(route('filament.store.pages.goods-receipt-report'))
            ->assertOk()
            ->assertSee('รายงานการรับสินค้า');
    }

    #[Test]
    public function goods_receipt_report_page_displays_goods_receipt_records(): void
    {
        $supplier = Supplier::factory()->for($this->company)->create([
            'name' => 'Supplier Report Test',
        ]);

        $product = Product::factory()
            ->for($this->company)
            ->for($this->branch)
            ->create([
                'name' => 'Brake Pad Report Test',
            ]);

        $goodsReceipt = GoodsReceipt::factory()
            ->for($this->company)
            ->for($this->branch)
            ->for($supplier)
            ->create([
                'created_by' => $this->user->id,
                'receipt_number' => 'GR-REPORT-001',
                'document_date' => today(),
            ]);

        GoodsReceiptItem::query()->create([
            'goods_receipt_id' => $goodsReceipt->id,
            'product_id' => $product->id,
            'description' => 'Received for report coverage',
            'quantity' => 12,
        ]);

        Livewire::actingAs($this->user)
            ->test(GoodsReceiptReport::class)
            ->assertStatus(200)
            ->assertSee('GR-REPORT-001')
            ->assertSee('Supplier Report Test')
            ->assertSee('12');
    }

    #[Test]
    public function low_stock_report_route_renders_successfully(): void
    {
        $this->actingAs($this->user)
            ->get(route('filament.store.pages.low-stock-report'))
            ->assertOk()
            ->assertSee('รายงานสินค้า Stock ต่ำ');
    }

    #[Test]
    public function low_stock_report_page_displays_only_low_stock_products(): void
    {
        Product::factory()
            ->for($this->company)
            ->for($this->branch)
            ->create([
                'name' => 'Low Stock Report Product',
                'stock_quantity' => 3,
                'min_stock' => 10,
                'is_active' => true,
            ]);

        Product::factory()
            ->for($this->company)
            ->for($this->branch)
            ->create([
                'name' => 'Healthy Stock Product',
                'stock_quantity' => 25,
                'min_stock' => 10,
                'is_active' => true,
            ]);

        Livewire::actingAs($this->user)
            ->test(LowStockReport::class)
            ->assertStatus(200)
            ->assertSee('Low Stock Report Product')
            ->assertDontSee('Healthy Stock Product');
    }
}
