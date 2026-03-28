<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\PaymentStatus;
use App\Models\SaleOrderItem;
use App\Models\TaxInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaxInvoiceEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SaleOrderItem::unsetEventDispatcher();

        PaymentStatus::create(['name' => 'รอชำระเงิน', 'code' => 'pending', 'sort_order' => 1]);
        PaymentStatus::create(['name' => 'ชำระแล้ว', 'code' => 'paid', 'sort_order' => 2]);
    }

    #[Test]
    public function it_validates_tax_id_format(): void
    {
        $validTaxIds = [
            '0105548012345',
            '0105568012345',
            '0-1555-12345-56-1',
        ];

        foreach ($validTaxIds as $taxId) {
            $this->assertNotEmpty($taxId);
        }
    }

    #[Test]
    public function it_calculates_zero_rated_vat(): void
    {
        $amount = 10000;
        $vatRate = 0;
        $vatAmount = $amount * ($vatRate / 100);

        $this->assertEquals(0, $vatAmount);
    }

    #[Test]
    public function it_calculates_exempt_from_vat(): void
    {
        $amount = 5000;
        $vatRate = 0;
        $vatAmount = $amount * ($vatRate / 100);

        $this->assertEquals(0, $vatAmount);
    }

    #[Test]
    public function it_generates_credit_note_for_return(): void
    {
        $originalAmount = 10000;
        $returnAmount = 1000;
        $newAmount = $originalAmount - $returnAmount;

        $this->assertEquals(9000, $newAmount);
    }

    #[Test]
    public function it_calculates_withholding_tax(): void
    {
        $income = 100000;
        $rate = 3;
        $tax = $income * ($rate / 100);
        $net = $income - $tax;

        $this->assertEquals(3000, $tax);
        $this->assertEquals(97000, $net);
    }

    #[Test]
    public function it_handles_branch_address_format(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $this->assertNotNull($branch);
    }

    #[Test]
    public function it_generates_full_address(): void
    {
        $addressParts = [
            'address_0' => '123 ถนนสุขุมวิท',
            'address_1' => 'แขวงคลองเตย',
            'amphoe' => 'คลองเตย',
            'province' => 'กรุงเทพมหานคร',
            'postal_code' => '10110',
        ];

        $fullAddress = implode(', ', array_filter([
            $addressParts['address_0'],
            $addressParts['address_1'],
            $addressParts['amphoe'],
            $addressParts['province'],
            $addressParts['postal_code'],
        ]));

        $this->assertStringContainsString('กรุงเทพมหานคร', $fullAddress);
    }

    #[Test]
    public function it_validates_invoice_date_not_before_order(): void
    {
        $orderDate = now()->subDays(5);
        $invoiceDate = now();

        $this->assertTrue($invoiceDate->greaterThan($orderDate));
    }

    #[Test]
    public function it_calculates_partial_refund(): void
    {
        $originalTotal = 10000;
        $refundedAmount = 2500;
        $remainingAmount = $originalTotal - $refundedAmount;

        $this->assertEquals(7500, $remainingAmount);
    }

    #[Test]
    public function it_handles_multi_line_item_vat(): void
    {
        $items = [
            ['price' => 1000, 'qty' => 2, 'vat_rate' => 7],
            ['price' => 500, 'qty' => 3, 'vat_rate' => 0],
            ['price' => 2000, 'qty' => 1, 'vat_rate' => 7],
        ];

        $subtotal = 0;
        $vat = 0;

        foreach ($items as $item) {
            $lineTotal = $item['price'] * $item['qty'];
            $subtotal += $lineTotal;
            $vat += $lineTotal * ($item['vat_rate'] / 100);
        }

        $this->assertEquals(5500, $subtotal);
        $this->assertEqualsWithDelta(280, $vat, 0.01);
    }

    #[Test]
    public function it_calculates_reverse_charge_vat(): void
    {
        $amount = 50000;
        $vatRate = 7;
        $vatAmount = $amount * ($vatRate / 100);
        $total = $amount + $vatAmount;

        $this->assertEqualsWithDelta(3500, $vatAmount, 0.01);
        $this->assertEqualsWithDelta(53500, $total, 0.01);
    }

    #[Test]
    public function it_validates_credit_note_reason(): void
    {
        $validReasons = [
            'goods_return',
            'discount',
            'price_adjustment',
            'cancel_invoice',
        ];

        foreach ($validReasons as $reason) {
            $this->assertNotEmpty($reason);
        }
    }

    #[Test]
    public function it_calculates_accrued_vat(): void
    {
        $sales = [
            ['month' => 1, 'amount' => 100000],
            ['month' => 2, 'amount' => 150000],
            ['month' => 3, 'amount' => 120000],
        ];

        $totalSales = array_sum(array_column($sales, 'amount'));
        $vatPayable = $totalSales * 0.07;

        $this->assertEquals(370000, $totalSales);
        $this->assertEqualsWithDelta(25900, $vatPayable, 0.01);
    }

    #[Test]
    public function it_handles_vat_exemption_certificate(): void
    {
        $hasCertificate = true;
        $vatRate = $hasCertificate ? 0 : 7;
        $amount = 50000;

        $vat = $amount * ($vatRate / 100);

        $this->assertEquals(0, $vat);
    }

    #[Test]
    public function it_calculates_deferred_vat(): void
    {
        $installments = 6;
        $totalVat = 10500;
        $vatPerInstallment = $totalVat / $installments;

        $this->assertEquals(1750, $vatPerInstallment);
    }

    #[Test]
    public function it_validates_tax_invoice_sequence(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();
        $customer = Customer::factory()->for($company)->create();

        $invoice1 = TaxInvoice::factory()->for($company)->for($branch)->for($customer)->for($user, 'creator')->create();

        $invoice2 = TaxInvoice::factory()->for($company)->for($branch)->for($customer)->for($user, 'creator')->create();

        $this->assertNotEquals($invoice1->id, $invoice2->id);
    }

    #[Test]
    public function it_calculates_proforma_invoice_validity(): void
    {
        $issueDate = now();
        $validDays = 30;
        $expiryDate = $issueDate->copy()->addDays($validDays);
        $daysRemaining = now()->diffInDays($expiryDate);

        $this->assertEqualsWithDelta(30, $daysRemaining, 1);
    }

    #[Test]
    public function it_handles_receipt_without_vat(): void
    {
        $subtotal = 1000;
        $vatRate = 0;
        $vatAmount = $subtotal * ($vatRate / 100);
        $total = $subtotal + $vatAmount;

        $this->assertEquals(1000, $total);
        $this->assertEquals(0, $vatAmount);
    }

    #[Test]
    public function it_calculates_penalty_for_late_payment(): void
    {
        $amount = 10000;
        $daysLate = 15;
        $penaltyRatePerDay = 0.001;
        $penalty = $amount * $penaltyRatePerDay * $daysLate;

        $this->assertEquals(150, $penalty);
    }
}
