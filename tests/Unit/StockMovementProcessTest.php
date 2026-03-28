<?php

namespace Tests\Unit;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockMovementProcessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_purchase_stock_movement(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'created_by' => $user->id,
            'type' => StockMovementType::In,
            'quantity' => 50,
            'stock_before' => 100,
            'stock_after' => 150,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovementType::In,
            'quantity' => 50,
            'stock_before' => 100,
            'stock_after' => 150,
        ]);
    }

    #[Test]
    public function it_records_sale_stock_movement(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $saleOrder = SaleOrder::factory()->create();
        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'created_by' => $user->id,
            'type' => StockMovementType::Out,
            'quantity' => 10,
            'stock_before' => 100,
            'stock_after' => 90,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'type' => StockMovementType::Out,
            'quantity' => 10,
        ]);
    }

    #[Test]
    public function it_records_adjustment_stock_movement(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'created_by' => $user->id,
            'type' => StockMovementType::Adjust,
            'quantity' => -5,
            'stock_before' => 100,
            'stock_after' => 95,
            'notes' => 'ชำรุดจากการตรวจรับ',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovementType::Adjust,
            'quantity' => -5,
        ]);
    }

    #[Test]
    public function it_calculates_stock_from_movements(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);
        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'created_by' => $user->id,
            'type' => StockMovementType::In,
            'quantity' => 100,
            'stock_before' => 0,
            'stock_after' => 100,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'created_by' => $user->id,
            'type' => StockMovementType::Out,
            'quantity' => 30,
            'stock_before' => 100,
            'stock_after' => 70,
        ]);

        $totalIn = StockMovement::where('product_id', $product->id)
            ->where('type', StockMovementType::In)
            ->sum('quantity');

        $totalOut = StockMovement::where('product_id', $product->id)
            ->where('type', StockMovementType::Out)
            ->sum('quantity');

        $this->assertEquals(100, $totalIn);
        $this->assertEquals(30, $totalOut);
    }

    #[Test]
    public function it_tracks_movement_history_for_product(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $user = User::factory()->create();

        for ($i = 1; $i <= 5; $i++) {
            StockMovement::create([
                'product_id' => $product->id,
                'created_by' => $user->id,
                'type' => StockMovementType::Out,
                'quantity' => 10,
                'stock_before' => 100 - (($i - 1) * 10),
                'stock_after' => 100 - ($i * 10),
            ]);
        }

        $movements = StockMovement::where('product_id', $product->id)
            ->orderBy('created_at')
            ->get();

        $this->assertEquals(5, $movements->count());
        $this->assertEquals(50, $movements->sum('quantity'));
    }
}
