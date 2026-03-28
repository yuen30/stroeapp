<?php

namespace Tests\Feature\Filament;

use App\Filament\Widgets\LowStockProductsWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    #[Test]
    public function stats_overview_widget_displays_sales_data(): void
    {
        Livewire::actingAs($this->user)
            ->test(StatsOverviewWidget::class)
            ->assertSee('ยอดขายวันนี้')
            ->assertSee('สินค้าใกล้หมด')
            ->assertSee('สต็อกที่ถูกจอง');
    }

    #[Test]
    public function stats_overview_widget_shows_correct_low_stock_count(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create([
            'stock_quantity' => 5,
        ]);

        Product::factory()->for($brand)->for($unit)->create([
            'stock_quantity' => 8,
        ]);

        Livewire::actingAs($this->user)
            ->test(StatsOverviewWidget::class)
            ->assertSee('2 รายการ');
    }

    #[Test]
    public function stats_overview_widget_shows_out_of_stock_count(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create([
            'stock_quantity' => 0,
            'name' => 'สินค้าหมด',
        ]);

        Livewire::actingAs($this->user)
            ->test(StatsOverviewWidget::class)
            ->assertSee('หมดสต็อก: 1 รายการ');
    }

    #[Test]
    public function low_stock_widget_shows_products_below_threshold(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้าต่ำ',
            'stock_quantity' => 5,
        ]);

        Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้าปกติ',
            'stock_quantity' => 50,
        ]);

        Livewire::actingAs($this->user)
            ->test(LowStockProductsWidget::class)
            ->assertSee('สินค้าต่ำ')
            ->assertCanNotSeeTableRecords(Product::where('name', 'สินค้าปกติ')->get());
    }

    #[Test]
    public function low_stock_widget_shows_active_products_only(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้าที่ใช้งาน',
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้าไม่ใช้งาน',
            'stock_quantity' => 5,
            'is_active' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test(LowStockProductsWidget::class)
            ->assertSee('สินค้าที่ใช้งาน');
    }
}
