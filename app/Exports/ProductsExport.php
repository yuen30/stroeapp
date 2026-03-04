<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with(['category', 'brand', 'unit'])->get();
    }

    public function headings(): array
    {
        return [
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
    }

    public function map($product): array
    {
        return [
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
    }
}
