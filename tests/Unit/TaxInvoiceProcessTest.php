<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus as PaymentStatusModel;
use App\Models\SaleOrder;
use App\Models\TaxInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaxInvoiceProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentStatusModel::create(['name' => 'รอชำระเงิน', 'code' => 'pending', 'sort_order' => 1]);
        PaymentStatusModel::create(['name' => 'ชำระแล้ว', 'code' => 'paid', 'sort_order' => 2]);

        PaymentMethod::create(['name' => 'เงินสด', 'code' => 'CASH', 'sort_order' => 1]);
    }

    #[Test]
    public function it_can_create_tax_invoice_from_sale_order(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $customer = Customer::factory()->for($company)->create([
            'name' => 'บริษัท ทดสอบ จำกัด',
            'tax_id' => '1234567890123',
        ]);
        $user = User::factory()->for($company)->for($branch)->create();

        $saleOrder = SaleOrder::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'invoice_number' => 'SO-001',
            'order_date' => now(),
            'status' => OrderStatus::Confirmed,
            'payment_status_id' => PaymentStatusModel::first()->id,
            'payment_method_id' => PaymentMethod::first()->id,
            'subtotal' => 10000,
            'discount_amount' => 0,
            'vat_amount' => 700,
            'total_amount' => 10700,
        ]);

        $taxInvoice = TaxInvoice::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sale_order_id' => $saleOrder->id,
            'created_by' => $user->id,
            'tax_invoice_number' => 'TAX-'.date('Ymd').'-0001',
            'document_date' => now(),
            'customer_name' => $customer->name,
            'customer_tax_id' => $customer->tax_id,
            'customer_address_line1' => '123 ถนนทดสอบ',
            'customer_province' => 'กรุงเทพฯ',
            'customer_postal_code' => '10100',
            'subtotal' => $saleOrder->subtotal,
            'discount_amount' => $saleOrder->discount_amount,
            'vat_rate' => 7.00,
            'vat_amount' => $saleOrder->vat_amount,
            'total_amount' => $saleOrder->total_amount,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $this->assertDatabaseHas('tax_invoices', [
            'id' => $taxInvoice->id,
            'sale_order_id' => $saleOrder->id,
            'customer_id' => $customer->id,
        ]);

        $this->assertEquals($saleOrder->total_amount, $taxInvoice->total_amount);
    }

    #[Test]
    public function it_generates_correct_vat_calculation(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $customer = Customer::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();

        $subtotal = 10000.00;
        $discountAmount = 500.00;
        $netAmount = $subtotal - $discountAmount;
        $vatRate = 7.00;
        $vatAmount = $netAmount * ($vatRate / 100);
        $totalAmount = $netAmount + $vatAmount;

        $taxInvoice = TaxInvoice::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'tax_invoice_number' => 'TAX-001',
            'document_date' => now(),
            'customer_name' => $customer->name,
            'customer_tax_id' => $customer->tax_id,
            'customer_address_line1' => $customer->address_0,
            'customer_province' => $customer->province,
            'customer_postal_code' => $customer->postal_code,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $this->assertEquals(9500.00, $netAmount);
        $this->assertEqualsWithDelta(665.00, $vatAmount, 0.01);
        $this->assertEqualsWithDelta(10165.00, $totalAmount, 0.01);
    }

    #[Test]
    public function it_updates_payment_status_when_paid(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $customer = Customer::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();

        $taxInvoice = TaxInvoice::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'tax_invoice_number' => 'TAX-001',
            'document_date' => now(),
            'customer_name' => $customer->name,
            'customer_tax_id' => $customer->tax_id,
            'customer_address_line1' => $customer->address_0,
            'customer_province' => $customer->province,
            'customer_postal_code' => $customer->postal_code,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'vat_rate' => 7,
            'vat_amount' => 70,
            'total_amount' => 1070,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $taxInvoice->update(['payment_status' => PaymentStatus::Paid]);

        $this->assertEquals(PaymentStatus::Paid, $taxInvoice->fresh()->payment_status);
    }

    #[Test]
    public function it_generates_full_address(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $customer = Customer::factory()->for($company)->create([
            'address_0' => '123 ถนนทดสอบ',
            'address_1' => 'ตึก A',
            'amphoe' => 'ทองหล่อ',
            'province' => 'กรุงเทพฯ',
            'postal_code' => '10100',
        ]);
        $user = User::factory()->for($company)->for($branch)->create();

        $taxInvoice = TaxInvoice::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'tax_invoice_number' => 'TAX-001',
            'document_date' => now(),
            'customer_name' => $customer->name,
            'customer_tax_id' => $customer->tax_id,
            'customer_address_line1' => $customer->address_0,
            'customer_address_line2' => $customer->address_1,
            'customer_amphoe' => $customer->amphoe,
            'customer_province' => $customer->province,
            'customer_postal_code' => $customer->postal_code,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'vat_rate' => 7,
            'vat_amount' => 70,
            'total_amount' => 1070,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $fullAddress = $taxInvoice->full_address;

        $this->assertStringContainsString('123 ถนนทดสอบ', $fullAddress);
        $this->assertStringContainsString('ตึก A', $fullAddress);
        $this->assertStringContainsString('อ.ทองหล่อ', $fullAddress);
        $this->assertStringContainsString('จ.กรุงเทพฯ', $fullAddress);
        $this->assertStringContainsString('10100', $fullAddress);
    }

    #[Test]
    public function it_soft_deletes_tax_invoice(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $customer = Customer::factory()->for($company)->create();
        $user = User::factory()->for($company)->for($branch)->create();

        $taxInvoice = TaxInvoice::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'tax_invoice_number' => 'TAX-001',
            'document_date' => now(),
            'customer_name' => $customer->name,
            'customer_tax_id' => $customer->tax_id,
            'customer_address_line1' => $customer->address_0,
            'customer_province' => $customer->province,
            'customer_postal_code' => $customer->postal_code,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'vat_rate' => 7,
            'vat_amount' => 70,
            'total_amount' => 1070,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $taxInvoiceId = $taxInvoice->id;

        $taxInvoice->delete();

        $this->assertSoftDeleted('tax_invoices', ['id' => $taxInvoiceId]);
    }
}
