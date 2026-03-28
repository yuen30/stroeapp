<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_can_view_products_list(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_see_products_in_table(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        $product = Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้าทดสอบ',
            'code' => 'TEST001',
        ]);

        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->assertSee('สินค้าทดสอบ')
            ->assertSee('TEST001');
    }

    #[Test]
    public function it_can_view_product_details(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        $product = Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้าทดสอบ',
            'code' => 'VIEW001',
        ]);

        Livewire::actingAs($this->user)
            ->test(ViewProduct::class, ['record' => $product->getKey()])
            ->assertSee('สินค้าทดสอบ')
            ->assertSee('VIEW001');
    }

    #[Test]
    public function it_can_search_products_by_code(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create(['code' => 'SEARCH001']);
        Product::factory()->for($brand)->for($unit)->create(['code' => 'OTHER001']);

        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->searchTable('SEARCH001')
            ->assertCanSeeTableRecords(Product::where('code', 'SEARCH001')->get())
            ->assertCanNotSeeTableRecords(Product::where('code', 'OTHER001')->get());
    }

    #[Test]
    public function it_can_search_products_by_name(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create(['name' => 'สินค้าพิเศษ']);
        Product::factory()->for($brand)->for($unit)->create(['name' => 'สินค้าธรรมดา']);

        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->searchTable('พิเศษ')
            ->assertCanSeeTableRecords(Product::where('name', 'like', '%พิเศษ%')->get());
    }

    #[Test]
    public function it_shows_multiple_products_in_list(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        $product1 = Product::factory()->for($brand)->for($unit)->create(['name' => 'สินค้า A']);
        $product2 = Product::factory()->for($brand)->for($unit)->create(['name' => 'สินค้า B']);

        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->assertCanSeeTableRecords([$product1, $product2]);
    }

    #[Test]
    public function it_shows_correct_columns_in_table(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้า A',
            'code' => 'CODE-A',
        ]);

        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->assertSee('สินค้า A')
            ->assertSee('CODE-A');
    }

    #[Test]
    public function it_shows_inactive_products_in_list(): void
    {
        $brand = Brand::factory()->create();
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create([
            'name' => 'สินค้าไม่ใช้งาน',
            'is_active' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->assertSee('สินค้าไม่ใช้งาน');
    }

    #[Test]
    public function it_displays_product_with_brand_relation(): void
    {
        $brand = Brand::factory()->create(['name' => 'ยี่ห้อดี']);
        $unit = Unit::factory()->create();

        Product::factory()->for($brand)->for($unit)->create(['name' => 'สินค้าแบรนด์']);

        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->assertSee('สินค้าแบรนด์')
            ->assertSee('ยี่ห้อดี');
    }
}
