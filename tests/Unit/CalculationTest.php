<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalculationTest extends TestCase
{
    #[Test]
    public function it_calculates_vat_correctly(): void
    {
        $amounts = [
            100 => 7.00,
            500 => 35.00,
            1000 => 70.00,
            5000 => 350.00,
            10000 => 700.00,
        ];

        foreach ($amounts as $amount => $expectedVat) {
            $vat = $amount * 0.07;
            $this->assertEqualsWithDelta($expectedVat, $vat, 0.01, "VAT for $amount should be $expectedVat");
        }
    }

    #[Test]
    public function it_calculates_net_amount_with_discount(): void
    {
        $scenarios = [
            ['amount' => 10000, 'discount' => 0, 'expectedNet' => 10000],
            ['amount' => 10000, 'discount' => 500, 'expectedNet' => 9500],
            ['amount' => 5000, 'discount' => 10, 'expectedNet' => 4990],
            ['amount' => 1000, 'discount' => 100, 'expectedNet' => 900],
        ];

        foreach ($scenarios as $scenario) {
            $net = $scenario['amount'] - $scenario['discount'];
            $this->assertEquals($scenario['expectedNet'], $net);
        }
    }

    #[Test]
    public function it_calculates_total_with_vat(): void
    {
        $subtotal = 10000;
        $vatRate = 7;
        $discount = 500;

        $netAmount = $subtotal - $discount;
        $vatAmount = $netAmount * ($vatRate / 100);
        $total = $netAmount + $vatAmount;

        $this->assertEquals(9500, $netAmount);
        $this->assertEqualsWithDelta(665, $vatAmount, 0.01);
        $this->assertEqualsWithDelta(10165, $total, 0.01);
    }

    #[Test]
    public function it_calculates_profit(): void
    {
        $costPrice = 75;
        $sellingPrice = 100;

        $profit = $sellingPrice - $costPrice;
        $profitMargin = (($sellingPrice - $costPrice) / $sellingPrice) * 100;

        $this->assertEquals(25, $profit);
        $this->assertEqualsWithDelta(25.00, $profitMargin, 0.01);
    }

    #[Test]
    public function it_calculates_average_cost(): void
    {
        $batches = [
            ['qty' => 100, 'cost' => 50],
            ['qty' => 50, 'cost' => 60],
            ['qty' => 75, 'cost' => 55],
        ];

        $totalQty = 0;
        $totalValue = 0;

        foreach ($batches as $batch) {
            $totalQty += $batch['qty'];
            $totalValue += $batch['qty'] * $batch['cost'];
        }

        $averageCost = $totalValue / $totalQty;

        $this->assertEquals(225, $totalQty);
        $this->assertEquals(12125, $totalValue);
        $this->assertEqualsWithDelta(53.89, $averageCost, 0.01);
    }

    #[Test]
    public function it_calculates_credit_usage(): void
    {
        $creditLimit = 100000;
        $outstandingAmounts = [0, 25000, 50000, 75000, 100000];

        foreach ($outstandingAmounts as $outstanding) {
            $remaining = $creditLimit - $outstanding;
            $usagePercentage = ($outstanding / $creditLimit) * 100;

            $this->assertGreaterThanOrEqual(0, $remaining);
            $this->assertGreaterThanOrEqual(0, $usagePercentage);
            $this->assertLessThanOrEqual(100, $usagePercentage);
        }
    }

    #[Test]
    public function it_calculates_quantity_discount(): void
    {
        $tiers = [
            ['min' => 10, 'discount' => 5],
            ['min' => 50, 'discount' => 10],
            ['min' => 100, 'discount' => 15],
        ];

        $testCases = [
            ['qty' => 5, 'expectedDiscount' => 0],
            ['qty' => 10, 'expectedDiscount' => 5],
            ['qty' => 25, 'expectedDiscount' => 5],
            ['qty' => 50, 'expectedDiscount' => 10],
            ['qty' => 75, 'expectedDiscount' => 10],
            ['qty' => 100, 'expectedDiscount' => 15],
            ['qty' => 200, 'expectedDiscount' => 15],
        ];

        foreach ($testCases as $case) {
            $applicable = 0;
            foreach ($tiers as $tier) {
                if ($case['qty'] >= $tier['min']) {
                    $applicable = $tier['discount'];
                }
            }
            $this->assertEquals($case['expectedDiscount'], $applicable, "Qty {$case['qty']} should have discount {$case['expectedDiscount']}");
        }
    }

    #[Test]
    public function it_calculates_withholding_tax(): void
    {
        $incomes = [10000, 50000, 100000];
        $rates = [3, 5];

        foreach ($incomes as $income) {
            foreach ($rates as $rate) {
                $tax = $income * ($rate / 100);
                $net = $income - $tax;

                $this->assertEqualsWithDelta($income * (1 - $rate / 100), $net, 0.01);
            }
        }
    }

    #[Test]
    public function it_calculates_stock_balance(): void
    {
        $openingStock = 100;
        $purchases = 50;
        $sales = 30;
        $adjustments = -5;

        $closingStock = $openingStock + $purchases - $sales + $adjustments;

        $this->assertEquals(115, $closingStock);
    }

    #[Test]
    public function it_calculates_percentage(): void
    {
        $values = [
            ['part' => 25, 'total' => 100, 'expected' => 25],
            ['part' => 1, 'total' => 4, 'expected' => 25],
            ['part' => 3, 'total' => 4, 'expected' => 75],
            ['part' => 1, 'total' => 3, 'expected' => 33.33],
        ];

        foreach ($values as $value) {
            $percentage = ($value['part'] / $value['total']) * 100;
            $this->assertEqualsWithDelta($value['expected'], $percentage, 0.01);
        }
    }

    #[Test]
    public function it_rounds_currency_correctly(): void
    {
        $amounts = [
            [123.456, 2, 123.46],
            [123.454, 2, 123.45],
            [123.455, 2, 123.46],
            [123.999, 2, 124.00],
        ];

        foreach ($amounts as $amount) {
            $rounded = round($amount[0], $amount[1]);
            $this->assertEquals($amount[2], $rounded);
        }
    }

    #[Test]
    public function it_calculates_early_payment_discount(): void
    {
        $scenarios = [
            ['total' => 10000, 'discount' => 2, 'expected' => 9800],
            ['total' => 50000, 'discount' => 3, 'expected' => 48500],
            ['total' => 25000, 'discount' => 5, 'expected' => 23750],
        ];

        foreach ($scenarios as $scenario) {
            $discounted = $scenario['total'] * (1 - $scenario['discount'] / 100);
            $this->assertEquals($scenario['expected'], $discounted);
        }
    }
}
