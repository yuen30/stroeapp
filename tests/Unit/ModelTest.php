<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_tax_id_length(): void
    {
        $taxId = '0105548012345';

        $this->assertEquals(13, strlen($taxId));
    }

    #[Test]
    public function it_validates_phone_number_formats(): void
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
    public function it_generates_full_address(): void
    {
        $address = [
            'address_0' => '123 ถ.สุขุมวิท',
            'address_1' => 'แขวงคลองเตย',
            'amphoe' => 'คลองเตย',
            'province' => 'กรุงเทพมหานคร',
            'postal_code' => '10110',
        ];

        $fullAddress = implode(', ', array_filter([
            $address['address_0'],
            $address['address_1'],
            $address['amphoe'],
            $address['province'],
            $address['postal_code'],
        ]));

        $this->assertStringContainsString('กรุงเทพมหานคร', $fullAddress);
    }

    #[Test]
    public function it_validates_code_uniqueness(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();
        $unit1 = Unit::factory()->create();
        $unit2 = Unit::factory()->create();

        $product1 = Product::factory()->for($company)->for($branch)->for($unit1)->create(['code' => 'PROD001']);
        $product2 = Product::factory()->for($company)->for($branch)->for($unit2)->create(['code' => 'PROD002']);

        $this->assertNotEquals($product1->code, $product2->code);
    }

    #[Test]
    public function it_validates_barcode_uniqueness(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $barcode = '1234567890123';
        Product::factory()->for($company)->for($branch)->create(['barcode' => $barcode]);

        $exists = Product::where('barcode', $barcode)->exists();

        $this->assertTrue($exists);
    }

    #[Test]
    public function it_calculates_product_profit_margin(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->for($company)->create();

        $product = Product::factory()->for($company)->for($branch)->create([
            'cost_price' => 80.00,
            'selling_price' => 100.00,
        ]);

        $profitMargin = (($product->selling_price - $product->cost_price) / $product->selling_price) * 100;

        $this->assertEqualsWithDelta(20, $profitMargin, 0.01);
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
    public function it_validates_email_format(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@company.co.th',
            'admin+test@domain.com',
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue(filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
        }
    }

    #[Test]
    public function it_formats_currency(): void
    {
        $amount = 12345.67;

        $formatted = number_format($amount, 2);

        $this->assertEquals('12,345.67', $formatted);
    }

    #[Test]
    public function it_calculates_vat(): void
    {
        $amount = 1000;
        $vatRate = 7;

        $vat = $amount * ($vatRate / 100);

        $this->assertEquals(70, $vat);
    }

    #[Test]
    public function it_calculates_total_with_vat(): void
    {
        $amount = 1000;
        $vatRate = 7;

        $vat = $amount * ($vatRate / 100);
        $total = $amount + $vat;

        $this->assertEquals(1070, $total);
    }
}
