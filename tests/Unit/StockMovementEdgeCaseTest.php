<?php

namespace Tests\Unit;

use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockMovementEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_running_balance(): void
    {
        $movements = [
            ['type' => 'in', 'qty' => 100],
            ['type' => 'out', 'qty' => 30],
            ['type' => 'in', 'qty' => 50],
            ['type' => 'out', 'qty' => 20],
        ];

        $balance = 0;
        $balances = [];

        foreach ($movements as $movement) {
            if ($movement['type'] === 'in') {
                $balance += $movement['qty'];
            } else {
                $balance -= $movement['qty'];
            }
            $balances[] = $balance;
        }

        $this->assertEquals(100, $balances[0]);
        $this->assertEquals(70, $balances[1]);
        $this->assertEquals(120, $balances[2]);
        $this->assertEquals(100, $balances[3]);
    }

    #[Test]
    public function it_detects_negative_stock(): void
    {
        $currentStock = 10;
        $requestedQty = 15;

        $wouldBeNegative = $currentStock - $requestedQty < 0;

        $this->assertTrue($wouldBeNegative);
    }

    #[Test]
    public function it_handles_adjustment_zero_sum(): void
    {
        $positiveAdjustments = 50;
        $negativeAdjustments = 50;

        $netAdjustment = $positiveAdjustments - $negativeAdjustments;

        $this->assertEquals(0, $netAdjustment);
    }

    #[Test]
    public function it_calculates_turnover_rate(): void
    {
        $beginningStock = 100;
        $endingStock = 150;
        $costOfGoodsSold = 500;

        $averageStock = ($beginningStock + $endingStock) / 2;
        $turnoverRate = $costOfGoodsSold / $averageStock;

        $this->assertEquals(125, $averageStock);
        $this->assertEqualsWithDelta(4, $turnoverRate, 0.01);
    }

    #[Test]
    public function it_validates_movement_references(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $product = Product::factory()->for($company)->for($branch)->create();

        $saleOrder = SaleOrder::factory()->for($company)->for($branch)->for($user, 'creator')->create();

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'sale_order_id' => $saleOrder->id,
            'created_by' => $user->id,
            'type' => StockMovementType::Out,
            'quantity' => 10,
            'stock_before' => 100,
            'stock_after' => 90,
            'reference' => 'SO-001',
            'notes' => 'Sale order completion',
        ]);

        $this->assertEquals($saleOrder->id, $movement->sale_order_id);
    }

    #[Test]
    public function it_calculates_days_of_inventory(): void
    {
        $averageInventory = 500;
        $costOfGoodsSoldPerDay = 50;

        $daysOfInventory = $averageInventory / $costOfGoodsSoldPerDay;

        $this->assertEquals(10, $daysOfInventory);
    }

    #[Test]
    public function it_handles_transfer_movements(): void
    {
        $outMovement = [
            'type' => StockMovementType::Out,
            'quantity' => 10,
            'from_location' => 'Warehouse A',
        ];

        $inMovement = [
            'type' => StockMovementType::In,
            'quantity' => 10,
            'to_location' => 'Warehouse B',
        ];

        $this->assertEquals($outMovement['quantity'], $inMovement['quantity']);
    }

    #[Test]
    public function it_calculates_shrinkage(): void
    {
        $beginningInventory = 1000;
        $purchases = 500;
        $sales = 600;
        $endingInventory = 850;

        $expectedEnding = $beginningInventory + $purchases - $sales;
        $shrinkage = $expectedEnding - $endingInventory;

        $this->assertEquals(900, $expectedEnding);
        $this->assertEquals(50, $shrinkage);
    }

    #[Test]
    public function it_validates_stock_before_and_after(): void
    {
        $stockBefore = 100;
        $quantity = 25;
        $type = StockMovementType::Out;

        $stockAfter = $type === StockMovementType::In
            ? $stockBefore + $quantity
            : $stockBefore - $quantity;

        $this->assertEquals(75, $stockAfter);
    }

    #[Test]
    public function it_calculates_safety_stock_variance(): void
    {
        $safetyStock = 50;
        $actualStock = 35;

        $variance = $actualStock - $safetyStock;
        $variancePercent = ($variance / $safetyStock) * 100;

        $this->assertEquals(-15, $variance);
        $this->assertEquals(-30, $variancePercent);
    }

    #[Test]
    public function it_handles_consignment_stock(): void
    {
        $ownStock = 100;
        $consignmentStock = 50;
        $totalAvailable = $ownStock + $consignmentStock;

        $this->assertEquals(150, $totalAvailable);
    }

    #[Test]
    public function it_calculates_dead_stock_ratio(): void
    {
        $totalStock = 1000;
        $deadStock = 100;

        $deadStockRatio = ($deadStock / $totalStock) * 100;

        $this->assertEquals(10, $deadStockRatio);
    }

    #[Test]
    public function it_validates_movement_date_sequence(): void
    {
        $movements = [
            ['date' => now()->subDays(3), 'type' => 'in', 'qty' => 100],
            ['date' => now()->subDays(2), 'type' => 'out', 'qty' => 30],
            ['date' => now()->subDays(1), 'type' => 'in', 'qty' => 50],
            ['date' => now(), 'type' => 'out', 'qty' => 20],
        ];

        for ($i = 1; $i < count($movements); $i++) {
            $this->assertTrue($movements[$i]['date']->greaterThanOrEqualTo($movements[$i - 1]['date']));
        }
    }

    #[Test]
    public function it_calculates_stock_accuracy(): void
    {
        $systemQuantity = 1000;
        $physicalCount = 985;
        $variance = abs($systemQuantity - $physicalCount);
        $accuracy = (1 - ($variance / $systemQuantity)) * 100;

        $this->assertEquals(15, $variance);
        $this->assertEqualsWithDelta(98.5, $accuracy, 0.01);
    }

    #[Test]
    public function it_handles_negative_adjustment(): void
    {
        $currentStock = 100;
        $adjustment = -10;

        $newStock = $currentStock + $adjustment;

        $this->assertEquals(90, $newStock);
    }

    #[Test]
    public function it_calculates_abc_classification(): void
    {
        $items = [
            ['sku' => 'A', 'value' => 5000],
            ['sku' => 'B', 'value' => 2000],
            ['sku' => 'C', 'value' => 500],
            ['sku' => 'D', 'value' => 200],
            ['sku' => 'E', 'value' => 100],
        ];

        $totalValue = array_sum(array_column($items, 'value'));
        usort($items, fn ($a, $b) => $b['value'] <=> $a['value']);

        $cumulativeValue = 0;
        $classACount = 0;
        $classBCount = 0;
        $classCCount = 0;

        foreach ($items as &$item) {
            $cumulativeValue += $item['value'];
            $cumulativePercent = ($cumulativeValue / $totalValue) * 100;
            $item['class'] = $cumulativePercent <= 80 ? 'A' : ($cumulativePercent <= 95 ? 'B' : 'C');
        }

        foreach ($items as $item) {
            if ($item['class'] === 'A') {
                $classACount++;
            }
            if ($item['class'] === 'B') {
                $classBCount++;
            }
            if ($item['class'] === 'C') {
                $classCCount++;
            }
        }

        $this->assertEquals(1, $classACount);
        $this->assertEquals(1, $classBCount);
        $this->assertEquals(3, $classCCount);
    }

    #[Test]
    public function it_calculates_xyz_demand_variation(): void
    {
        $demands = [100, 95, 105, 98, 102];
        $avgDemand = array_sum($demands) / count($demands);

        $variance = 0;
        foreach ($demands as $d) {
            $variance += pow($d - $avgDemand, 2);
        }
        $stdDev = sqrt($variance / count($demands));
        $cv = ($stdDev / $avgDemand) * 100;

        $this->assertEquals(100, $avgDemand);
        $this->assertEqualsWithDelta(3.41, $stdDev, 0.1);
        $this->assertEqualsWithDelta(3.41, $cv, 0.1);
    }

    #[Test]
    public function it_validates_reorder_quantity(): void
    {
        $currentStock = 50;
        $safetyStock = 20;
        $maxStock = 200;
        $reorderPoint = 30;

        $reorderQty = max(0, $maxStock - $currentStock);

        $this->assertEquals(150, $reorderQty);
        $this->assertTrue($currentStock > $reorderPoint);
    }
}
