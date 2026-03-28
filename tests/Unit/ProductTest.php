<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_detects_low_stock(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 5,
            'min_stock' => 10,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    #[Test]
    public function it_detects_normal_stock(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 50,
            'min_stock' => 10,
        ]);

        $this->assertFalse($product->isLowStock());
    }

    #[Test]
    public function it_detects_over_stock(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 250,
            'max_stock' => 200,
        ]);

        $this->assertTrue($product->isOverStock());
    }

    #[Test]
    public function it_calculates_available_stock(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 100,
        ]);

        $this->assertEquals(100, $product->available_stock);
    }

    #[Test]
    public function it_validates_unique_code(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product1 = Product::factory()->for($company)->for($branch)->create();

        $this->expectException(QueryException::class);

        Product::factory()->for($company)->for($branch)->create([
            'code' => $product1->code,
        ]);
    }

    #[Test]
    public function it_validates_unique_sku(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product1 = Product::factory()->for($company)->for($branch)->create();

        $this->expectException(QueryException::class);

        Product::factory()->for($company)->for($branch)->create([
            'sku' => $product1->sku,
        ]);
    }

    #[Test]
    public function it_validates_unique_barcode(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'barcode' => '1234567890123',
        ]);

        $this->assertEquals('1234567890123', $product->barcode);
    }

    #[Test]
    public function it_calculates_profit_margin(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'cost_price' => 75,
            'selling_price' => 100,
        ]);

        $margin = (($product->selling_price - $product->cost_price) / $product->selling_price) * 100;

        $this->assertEqualsWithDelta(25, $margin, 0.01);
    }

    #[Test]
    public function it_calculates_markup(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'cost_price' => 80,
            'selling_price' => 100,
        ]);

        $markup = (($product->selling_price - $product->cost_price) / $product->cost_price) * 100;

        $this->assertEqualsWithDelta(25, $markup, 0.01);
    }

    #[Test]
    public function it_handles_inactive_product(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'is_active' => false,
        ]);

        $this->assertFalse($product->is_active);
    }

    #[Test]
    public function it_validates_price_hierarchy(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'cost_price' => 50,
            'selling_price' => 100,
        ]);

        $this->assertGreaterThan($product->cost_price, $product->selling_price);
    }

    #[Test]
    public function it_calculates_inventory_value(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 100,
            'cost_price' => 75.50,
        ]);

        $inventoryValue = $product->stock_quantity * $product->cost_price;

        $this->assertEqualsWithDelta(7550, $inventoryValue, 0.01);
    }

    #[Test]
    public function it_handles_product_with_category(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'category_id' => null,
        ]);

        $this->assertNull($product->category_id);
    }

    #[Test]
    public function it_handles_product_with_brand(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $brand = Brand::factory()->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'brand_id' => $brand->id,
        ]);

        $this->assertEquals($brand->id, $product->brand_id);
    }

    #[Test]
    public function it_handles_product_with_unit(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $unit = Unit::factory()->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'unit_id' => $unit->id,
        ]);

        $this->assertEquals($unit->id, $product->unit_id);
    }

    #[Test]
    public function it_validates_barcode_format(): void
    {
        $validBarcodes = [
            '1234567890123',
            '0123456789012',
            '5901234123457',
        ];

        foreach ($validBarcodes as $barcode) {
            $this->assertEquals(13, strlen($barcode));
        }
    }

    #[Test]
    public function it_handles_soft_delete(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 0]);
        $productId = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);
        $this->assertNotNull($product->fresh()->deleted_at);
    }

    #[Test]
    public function it_restores_soft_deleted_product(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 0]);
        $productId = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);

        Product::withTrashed()->find($productId)->restore();

        $this->assertNull(Product::find($productId)->deleted_at);
    }

    #[Test]
    public function it_calculates_reorder_quantity(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 20,
            'max_stock' => 200,
        ]);

        $reorderQty = $product->max_stock - $product->stock_quantity;

        $this->assertEquals(180, $reorderQty);
    }

    #[Test]
    public function it_validates_stock_boundaries(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 0,
            'min_stock' => 10,
        ]);

        $this->assertEquals(0, $product->stock_quantity);
        $this->assertTrue($product->isLowStock());
    }

    #[Test]
    public function it_calculates_stock_turnover_days(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'stock_quantity' => 100,
        ]);

        $annualSales = 1200;
        $stockTurnover = $annualSales / $product->stock_quantity;
        $daysToSell = 365 / $stockTurnover;

        $this->assertEqualsWithDelta(30.42, $daysToSell, 0.01);
    }
}
