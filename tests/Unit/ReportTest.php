<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTest extends TestCase
{
    #[Test]
    public function it_detects_low_stock_products(): void
    {
        $products = [
            ['stock_quantity' => 5, 'min_stock' => 10, 'is_active' => true],
            ['stock_quantity' => 50, 'min_stock' => 10, 'is_active' => true],
            ['stock_quantity' => 3, 'min_stock' => 5, 'is_active' => true],
        ];

        $lowStockProducts = array_filter($products, function ($p) {
            return $p['is_active'] && $p['min_stock'] > 0 && $p['stock_quantity'] <= $p['min_stock'];
        });

        $this->assertEquals(2, count($lowStockProducts));
    }

    #[Test]
    public function it_calculates_shortage_quantity(): void
    {
        $stockQuantity = 5;
        $minStock = 10;

        $shortage = max($minStock - $stockQuantity, 0);

        $this->assertEquals(5, $shortage);
    }

    #[Test]
    public function it_calculates_restock_cost(): void
    {
        $stockQuantity = 5;
        $minStock = 20;
        $costPrice = 100.00;

        $restockQuantity = max($minStock - $stockQuantity, 0);
        $restockCost = $restockQuantity * $costPrice;

        $this->assertEquals(15, $restockQuantity);
        $this->assertEquals(1500.00, $restockCost);
    }

    #[Test]
    public function it_detects_out_of_stock(): void
    {
        $products = [
            ['stock_quantity' => 0, 'min_stock' => 10],
            ['stock_quantity' => 5, 'min_stock' => 10],
        ];

        $outOfStock = array_filter($products, fn ($p) => $p['stock_quantity'] <= 0);

        $this->assertEquals(1, count($outOfStock));
    }

    #[Test]
    public function it_calculates_total_restock_cost_for_all_low_stock(): void
    {
        $lowStockProducts = [
            ['stock_quantity' => 5, 'min_stock' => 20, 'cost_price' => 100.00],
            ['stock_quantity' => 3, 'min_stock' => 15, 'cost_price' => 50.00],
        ];

        $totalRestockCost = 0;
        foreach ($lowStockProducts as $product) {
            $restockQty = max($product['min_stock'] - $product['stock_quantity'], 0);
            $totalRestockCost += $restockQty * $product['cost_price'];
        }

        $expectedCost = (15 * 100.00) + (12 * 50.00);
        $this->assertEquals($expectedCost, $totalRestockCost);
    }

    #[Test]
    public function it_sorts_low_stock_by_shortage_desc(): void
    {
        $products = [
            ['id' => 1, 'stock_quantity' => 5, 'min_stock' => 10],
            ['id' => 2, 'stock_quantity' => 8, 'min_stock' => 10],
            ['id' => 3, 'stock_quantity' => 2, 'min_stock' => 10],
        ];

        usort($products, fn ($a, $b) => ($b['min_stock'] - $b['stock_quantity']) <=> ($a['min_stock'] - $a['stock_quantity']));

        $this->assertEquals(3, $products[0]['id']);
        $this->assertEquals(1, $products[1]['id']);
        $this->assertEquals(2, $products[2]['id']);
    }

    #[Test]
    public function it_excludes_inactive_products_from_low_stock(): void
    {
        $products = [
            ['stock_quantity' => 5, 'min_stock' => 10, 'is_active' => true],
            ['stock_quantity' => 5, 'min_stock' => 10, 'is_active' => false],
        ];

        $activeLowStock = array_filter($products, fn ($p) => $p['is_active'] && $p['stock_quantity'] <= $p['min_stock']);

        $this->assertEquals(1, count($activeLowStock));
    }

    #[Test]
    public function it_handles_zero_min_stock(): void
    {
        $product = ['stock_quantity' => 5, 'min_stock' => 0];

        $isLowStock = $product['min_stock'] > 0 && $product['stock_quantity'] <= $product['min_stock'];

        $this->assertFalse($isLowStock);
    }

    #[Test]
    public function it_calculates_stock_turnover_days(): void
    {
        $stockQuantity = 100;
        $annualSales = 1200;

        $stockTurnover = $annualSales / $stockQuantity;
        $daysToSell = 365 / $stockTurnover;

        $this->assertEqualsWithDelta(30.42, $daysToSell, 0.01);
    }

    #[Test]
    public function it_generates_overview_summary(): void
    {
        $products = [
            ['stock_quantity' => 5, 'min_stock' => 10, 'cost_price' => 100],
            ['stock_quantity' => 8, 'min_stock' => 15, 'cost_price' => 50],
        ];

        $totalShortage = 0;
        $totalRestockCost = 0;

        foreach ($products as $product) {
            $shortage = max($product['min_stock'] - $product['stock_quantity'], 0);
            $totalShortage += $shortage;
            $totalRestockCost += $shortage * $product['cost_price'];
        }

        $summary = [
            'total_low_stock' => count($products),
            'total_shortage' => $totalShortage,
            'total_restock_cost' => $totalRestockCost,
        ];

        $this->assertEquals(2, $summary['total_low_stock']);
        $this->assertEquals(12, $summary['total_shortage']);
        $this->assertEquals(850, $summary['total_restock_cost']);
    }

    #[Test]
    public function it_filters_by_brand(): void
    {
        $products = [
            ['id' => 1, 'brand_id' => 'brand1', 'stock_quantity' => 5, 'min_stock' => 10, 'brand' => ['name' => 'แบรนด์1']],
            ['id' => 2, 'brand_id' => 'brand2', 'stock_quantity' => 5, 'min_stock' => 10, 'brand' => ['name' => 'แบรนด์2']],
        ];

        $filteredProducts = array_filter($products, fn ($p) => $p['brand_id'] === 'brand1');

        $this->assertEquals(1, count($filteredProducts));
        $this->assertEquals('แบรนด์1', $filteredProducts[0]['brand']['name']);
    }

    #[Test]
    public function it_calculates_shortage_percentage(): void
    {
        $stockQuantity = 5;
        $minStock = 20;

        $shortage = max($minStock - $stockQuantity, 0);
        $shortagePercentage = ($shortage / $minStock) * 100;

        $this->assertEquals(75, $shortagePercentage);
    }

    #[Test]
    public function it_prioritizes_by_urgency(): void
    {
        $products = [
            ['id' => 1, 'stock_quantity' => 5, 'min_stock' => 10, 'cost_price' => 50],
            ['id' => 2, 'stock_quantity' => 3, 'min_stock' => 10, 'cost_price' => 200],
        ];

        usort($products, function ($a, $b) {
            $urgencyA = ($a['min_stock'] - $a['stock_quantity']) * $a['cost_price'];
            $urgencyB = ($b['min_stock'] - $b['stock_quantity']) * $b['cost_price'];

            return $urgencyB <=> $urgencyA;
        });

        $this->assertEquals(2, $products[0]['id']);
    }
}
