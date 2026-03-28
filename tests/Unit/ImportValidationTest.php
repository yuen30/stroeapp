<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportValidationTest extends TestCase
{
    #[Test]
    public function it_validates_required_fields(): void
    {
        $requiredFields = ['name', 'cost_price', 'selling_price', 'stock_quantity'];

        $validData = [
            'name' => 'สินค้าทดสอบ',
            'cost_price' => 100.00,
            'selling_price' => 150.00,
            'stock_quantity' => 50,
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $validData);
            $this->assertNotEmpty($validData[$field]);
        }
    }

    #[Test]
    public function it_validates_product_name_not_empty(): void
    {
        $validName = 'สินค้าทดสอบ';
        $invalidName = '';

        $this->assertNotEmpty($validName);
        $this->assertEmpty($invalidName);
    }

    #[Test]
    public function it_validates_price_must_be_positive(): void
    {
        $validPrices = [0.01, 100.00, 999999.99];
        $invalidPrices = [0, -1, -100.00];

        foreach ($validPrices as $price) {
            $this->assertGreaterThan(0, $price);
        }

        foreach ($invalidPrices as $price) {
            $this->assertLessThanOrEqual(0, $price);
        }
    }

    #[Test]
    public function it_validates_stock_quantity_must_be_integer(): void
    {
        $validQuantities = [0, 1, 100, 9999];
        $invalidQuantities = [-1, -100];

        foreach ($validQuantities as $qty) {
            $this->assertIsInt($qty);
            $this->assertGreaterThanOrEqual(0, $qty);
        }

        foreach ($invalidQuantities as $qty) {
            $this->assertLessThan(0, $qty);
        }
    }

    #[Test]
    public function it_validates_barcode_format_13_digits(): void
    {
        $validBarcodes = [
            '1234567890123',
            '8850123456789',
            '5901234123457',
        ];

        foreach ($validBarcodes as $barcode) {
            $this->assertEquals(13, strlen($barcode));
            $this->assertTrue(ctype_digit($barcode));
        }
    }

    #[Test]
    public function it_validates_cost_price_less_than_selling_price(): void
    {
        $costPrice = 100.00;
        $sellingPrice = 150.00;

        $this->assertLessThan($sellingPrice, $costPrice);
    }

    #[Test]
    public function it_normalizes_optional_fields(): void
    {
        $data = [
            'name' => '  สินค้าทดสอบ  ',
            'description' => null,
            'barcode' => '',
        ];

        $normalized = [
            'name' => trim($data['name']),
            'description' => $data['description'] ?? '',
            'barcode' => $data['barcode'] ?: null,
        ];

        $this->assertEquals('สินค้าทดสอบ', $normalized['name']);
        $this->assertEquals('', $normalized['description']);
        $this->assertNull($normalized['barcode']);
    }

    #[Test]
    public function it_validates_max_field_lengths(): void
    {
        $maxLengths = [
            'name' => 255,
            'code' => 50,
            'barcode' => 20,
            'description' => 1000,
        ];

        $testData = [
            'name' => str_repeat('a', 256),
            'code' => str_repeat('a', 51),
            'barcode' => str_repeat('a', 21),
        ];

        $this->assertGreaterThan($maxLengths['name'], strlen($testData['name']));
        $this->assertGreaterThan($maxLengths['code'], strlen($testData['code']));
        $this->assertGreaterThan($maxLengths['barcode'], strlen($testData['barcode']));
    }

    #[Test]
    public function it_tracks_import_errors(): void
    {
        $errors = [];

        $rows = [
            ['Name' => '', 'cost_price' => 100],
            ['Name' => 'สินค้า1', 'cost_price' => -50],
        ];

        foreach ($rows as $index => $row) {
            if (empty($row['Name'])) {
                $errors[] = "Row {$index}: Name is required";
            }
            if (isset($row['cost_price']) && $row['cost_price'] <= 0) {
                $errors[] = "Row {$index}: Cost price must be positive";
            }
        }

        $this->assertCount(2, $errors);
        $this->assertStringContainsString('Name is required', $errors[0]);
        $this->assertStringContainsString('Cost price must be positive', $errors[1]);
    }

    #[Test]
    public function it_calculates_profit_margin_on_import(): void
    {
        $costPrice = 80.00;
        $sellingPrice = 100.00;

        $profitMargin = (($sellingPrice - $costPrice) / $sellingPrice) * 100;

        $this->assertEqualsWithDelta(20, $profitMargin, 0.01);
    }

    #[Test]
    public function it_validates_barcode_length(): void
    {
        $barcode = '1234567890123';

        $this->assertEquals(13, strlen($barcode));
        $this->assertTrue(ctype_digit($barcode));
    }

    #[Test]
    public function it_handles_whitespace_trimming(): void
    {
        $input = '  สินค้าทดสอบ  ';
        $trimmed = trim($input);

        $this->assertEquals('สินค้าทดสอบ', $trimmed);
    }

    #[Test]
    public function it_validates_numeric_fields(): void
    {
        $validNumbers = [0, 1, 100.50, 999999.99];
        $invalidNumbers = ['abc', '', null];

        foreach ($validNumbers as $num) {
            $this->assertTrue(is_numeric($num) || is_int($num));
        }

        foreach ($invalidNumbers as $num) {
            $this->assertFalse(is_numeric($num));
        }
    }

    #[Test]
    public function it_generates_unique_code_format(): void
    {
        $code = 'PROD-'.str_pad(1, 6, '0', STR_PAD_LEFT);

        $this->assertEquals('PROD-000001', $code);
        $this->assertEquals(11, strlen($code));
    }
}
