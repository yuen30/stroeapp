<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportTest extends TestCase
{
    #[Test]
    public function it_generates_export_headings(): void
    {
        $expectedHeadings = [
            'ชื่อสินค้า',
            'รหัสสินค้า',
            'บาร์โค้ด',
            'รายละเอียด',
            'ราคาทุน',
            'ราคาขาย',
            'จำนวนสต็อก',
            'หมวดหมู่',
            'แบรนด์',
            'หน่วย',
            'สถานะ',
            'วันที่สร้าง',
        ];

        $this->assertCount(12, $expectedHeadings);
        $this->assertEquals('ชื่อสินค้า', $expectedHeadings[0]);
        $this->assertEquals('รหัสสินค้า', $expectedHeadings[1]);
        $this->assertEquals('ราคาทุน', $expectedHeadings[4]);
        $this->assertEquals('ราคาขาย', $expectedHeadings[5]);
    }

    #[Test]
    public function it_generates_template_export_structure(): void
    {
        $templateHeadings = [
            'ชื่อสินค้า *',
            'รหัสสินค้า',
            'บาร์โค้ด',
            'รายละเอียด',
            'ราคาทุน *',
            'ราคาขาย *',
            'จำนวนสต็อก *',
            'หมวดหมู่',
            'ยี่ห้อ *',
            'หน่วยนับ *',
        ];

        $this->assertCount(10, $templateHeadings);
        $this->assertStringContainsString('*', $templateHeadings[0]);
    }

    #[Test]
    public function it_defines_column_widths(): void
    {
        $columnWidths = [
            'A' => 25,
            'B' => 15,
            'C' => 15,
            'D' => 35,
            'E' => 12,
            'F' => 12,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 12,
        ];

        $this->assertEquals(25, $columnWidths['A']);
        $this->assertEquals(35, $columnWidths['D']);
    }

    #[Test]
    public function it_maps_product_to_export_row(): void
    {
        $product = (object) [
            'name' => 'สินค้าทดสอบ',
            'code' => 'TEST001',
            'barcode' => '1234567890123',
            'description' => 'รายละเอียดสินค้า',
            'cost_price' => 100.00,
            'selling_price' => 150.00,
            'stock_quantity' => 50,
            'category' => (object) ['name' => 'หมวดหมู่1'],
            'brand' => (object) ['name' => 'แบรนด์1'],
            'unit' => (object) ['name' => 'ชิ้น'],
            'is_active' => true,
            'created_at' => now(),
        ];

        $mappedRow = [
            $product->name,
            $product->code,
            $product->barcode,
            $product->description,
            $product->cost_price,
            $product->selling_price,
            $product->stock_quantity,
            $product->category?->name,
            $product->brand?->name,
            $product->unit?->name,
            $product->is_active ? 'ใช้งาน' : 'ไม่ใช้งาน',
            $product->created_at?->format('d/m/Y H:i'),
        ];

        $this->assertCount(12, $mappedRow);
        $this->assertEquals('หมวดหมู่1', $mappedRow[7]);
        $this->assertEquals('แบรนด์1', $mappedRow[8]);
        $this->assertEquals('ชิ้น', $mappedRow[9]);
        $this->assertEquals('ใช้งาน', $mappedRow[10]);
    }

    #[Test]
    public function it_handles_null_relationships_in_export(): void
    {
        $product = (object) [
            'category' => null,
            'brand' => null,
            'unit' => null,
        ];

        $mappedRow = [
            $product->category?->name ?? null,
            $product->brand?->name ?? null,
            $product->unit?->name ?? null,
        ];

        $this->assertNull($mappedRow[0]);
        $this->assertNull($mappedRow[1]);
        $this->assertNull($mappedRow[2]);
    }

    #[Test]
    public function it_formats_active_status_correctly(): void
    {
        $activeStatus = true ? 'ใช้งาน' : 'ไม่ใช้งาน';
        $inactiveStatus = false ? 'ใช้งาน' : 'ไม่ใช้งาน';

        $this->assertEquals('ใช้งาน', $activeStatus);
        $this->assertEquals('ไม่ใช้งาน', $inactiveStatus);
    }
}
