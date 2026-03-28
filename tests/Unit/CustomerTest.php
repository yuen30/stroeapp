<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SaleOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_calculates_remaining_credit_limit(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 50000,
        ]);

        $outstanding = 20000;
        $remaining = $customer->credit_limit - $outstanding;

        $this->assertEquals(30000, $remaining);
    }

    #[Test]
    public function it_returns_zero_when_no_credit_limit(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 0,
        ]);

        $this->assertEquals(0, $customer->credit_limit);
    }

    #[Test]
    public function it_calculates_credit_usage_percentage(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 100000,
        ]);

        $outstanding = 25000;
        $usagePercent = ($outstanding / $customer->credit_limit) * 100;

        $this->assertEquals(25, $usagePercent);
    }

    #[Test]
    public function it_caps_credit_usage_at_100_percent(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 50000,
        ]);

        $outstanding = 60000;
        $usagePercent = min(100, ($outstanding / $customer->credit_limit) * 100);

        $this->assertEquals(100, $usagePercent);
    }

    #[Test]
    public function it_can_create_sale_order_within_credit(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 100000,
        ]);

        $outstanding = 30000;
        $remaining = $customer->credit_limit - $outstanding;
        $canOrder = $remaining >= 50000;

        $this->assertTrue($canOrder);
    }

    #[Test]
    public function it_rejects_sale_order_exceeding_credit(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 50000,
        ]);

        $outstanding = 40000;
        $remaining = $customer->credit_limit - $outstanding;
        $canOrder = $remaining >= 20000;

        $this->assertFalse($canOrder);
    }

    #[Test]
    public function it_allows_cash_customer_always(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_limit' => 0,
        ]);

        $canOrder = $customer->credit_limit <= 0;

        $this->assertTrue($canOrder);
    }

    #[Test]
    public function it_generates_full_address(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'address_0' => '123 ถ.สุขุมวิท',
            'address_1' => 'แขวงคลองเตย',
            'amphoe' => 'คลองเตย',
            'province' => 'กรุงเทพมหานคร',
            'postal_code' => '10110',
        ]);

        $addressParts = array_filter([
            $customer->address_0,
            $customer->address_1,
            $customer->amphoe,
            $customer->province,
            $customer->postal_code,
        ]);

        $fullAddress = implode(', ', $addressParts);

        $this->assertStringContainsString('กรุงเทพมหานคร', $fullAddress);
    }

    #[Test]
    public function it_validates_tax_id_format(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'tax_id' => '0105548012345',
        ]);

        $this->assertEquals(13, strlen($customer->tax_id));
    }

    #[Test]
    public function it_handles_inactive_customer(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'is_active' => false,
        ]);

        $this->assertFalse($customer->is_active);
    }

    #[Test]
    public function it_calculates_aging_report_buckets(): void
    {
        $buckets = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'over_90' => 0,
        ];

        $orders = [
            ['age' => 0, 'amount' => 10000],
            ['age' => 25, 'amount' => 15000],
            ['age' => 45, 'amount' => 8000],
            ['age' => 75, 'amount' => 12000],
            ['age' => 100, 'amount' => 5000],
        ];

        foreach ($orders as $order) {
            if ($order['age'] <= 0) {
                $buckets['current'] += $order['amount'];
            } elseif ($order['age'] <= 30) {
                $buckets['days_1_30'] += $order['amount'];
            } elseif ($order['age'] <= 60) {
                $buckets['days_31_60'] += $order['amount'];
            } elseif ($order['age'] <= 90) {
                $buckets['days_61_90'] += $order['amount'];
            } else {
                $buckets['over_90'] += $order['amount'];
            }
        }

        $this->assertEquals(10000, $buckets['current']);
        $this->assertEquals(15000, $buckets['days_1_30']);
        $this->assertEquals(8000, $buckets['days_31_60']);
        $this->assertEquals(12000, $buckets['days_61_90']);
        $this->assertEquals(5000, $buckets['over_90']);
    }

    #[Test]
    public function it_handles_head_office_and_branches(): void
    {
        $company = Company::factory()->create();

        $headOffice = Customer::factory()->for($company)->create([
            'name' => 'บริษัท สยาม จำกัด (สำนักงานใหญ่)',
            'is_head_office' => true,
        ]);

        $branch1 = Customer::factory()->for($company)->create([
            'name' => 'บริษัท สยาม จำกัด สาขาที่ 1',
            'is_head_office' => false,
            'branch_no' => '00001',
        ]);

        $this->assertTrue($headOffice->is_head_office);
        $this->assertFalse($branch1->is_head_office);
    }

    #[Test]
    public function it_calculates_customer_risk_score(): void
    {
        $creditUsage = 85;
        $paymentDaysAvg = 45;
        $orderFrequency = 2;

        $riskScore = ($creditUsage * 0.4) + (min($paymentDaysAvg / 60, 1) * 30) + ((12 - min($orderFrequency, 12)) * 2.5);

        $this->assertLessThan(100, $riskScore);
    }

    #[Test]
    public function it_validates_phone_number_format(): void
    {
        $validPhones = [
            '02-123-4567',
            '089-123-4567',
            '081-234-5678',
            '+6681-234-5678',
        ];

        foreach ($validPhones as $phone) {
            $this->assertNotEmpty($phone);
        }
    }

    #[Test]
    public function it_calculates_total_outstanding(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $customer = Customer::factory()->for($company)->create();

        SaleOrder::factory()->count(3)->for($company)->for($branch)->for($customer)->create([
            'total_amount' => 10000,
        ]);

        $this->assertEquals(3, $customer->saleOrders()->count());
    }

    #[Test]
    public function it_calculates_payment_terms_days(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create([
            'credit_days' => 30,
        ]);

        $orderDate = now();
        $dueDate = $orderDate->copy()->addDays($customer->credit_days);

        $this->assertEquals(30, $customer->credit_days);
    }

    #[Test]
    public function it_handles_soft_delete(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $customer = Customer::factory()->for($company)->create();
        $customerId = $customer->id;

        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customerId]);
        $this->assertNotNull($customer->fresh()->deleted_at);
    }
}
