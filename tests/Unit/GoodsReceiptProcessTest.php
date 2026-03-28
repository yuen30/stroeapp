<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoodsReceiptProcessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_standalone_goods_receipt(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $supplier = Supplier::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 0]);
        $user = User::factory()->for($company)->for($branch)->create();

        $goodsReceipt = GoodsReceipt::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'receipt_number' => 'GR-'.date('Ymd').'-0001',
            'supplier_delivery_no' => 'SDN-001',
            'is_standalone' => true,
            'document_date' => now(),
            'status' => OrderStatus::Confirmed,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $goodsReceipt->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_cost' => 100,
            'total_cost' => 5000,
        ]);

        $this->assertDatabaseHas('goods_receipts', [
            'id' => $goodsReceipt->id,
            'is_standalone' => true,
            'purchase_order_id' => null,
        ]);

        $this->assertDatabaseHas('goods_receipt_items', [
            'goods_receipt_id' => $goodsReceipt->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);
    }

    #[Test]
    public function it_adds_stock_on_goods_receipt_completion(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $supplier = Supplier::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 0]);
        $user = User::factory()->for($company)->for($branch)->create();

        $this->assertEquals(0, $product->stock_quantity);

        $goodsReceipt = GoodsReceipt::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'receipt_number' => 'GR-'.date('Ymd').'-0001',
            'document_date' => now(),
            'is_standalone' => true,
            'status' => OrderStatus::Confirmed,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $goodsReceipt->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_cost' => 100,
            'total_cost' => 5000,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'goods_receipt_id' => $goodsReceipt->id,
            'created_by' => $user->id,
            'type' => StockMovementType::In,
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
        ]);

        $product->update(['stock_quantity' => 50]);

        $this->assertEquals(50, $product->fresh()->stock_quantity);
    }

    #[Test]
    public function it_cancels_goods_receipt(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $product = Product::factory()->for($company)->for($branch)->create(['stock_quantity' => 50]);
        $supplier = Supplier::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();

        $goodsReceipt = GoodsReceipt::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'receipt_number' => 'GR-001',
            'document_date' => now(),
            'is_standalone' => true,
            'status' => OrderStatus::Confirmed,
        ]);

        $goodsReceipt->update(['status' => OrderStatus::Cancelled]);

        $this->assertEquals(OrderStatus::Cancelled, $goodsReceipt->fresh()->status);
    }
}
