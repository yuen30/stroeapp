<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            // ตัวอย่างข้อมูล
            [
                'เสื้อยืดคอกลม',
                '',  // ถ้าไม่ระบุจะสร้างอัตโนมัติ
                '8850123456789',
                'เสื้อยืดคอกลมสีขาว ผ้าคอตตอน 100%',
                150.0,
                250.0,
                100,
                'เสื้อผ้า',
                'Nike',
                'ชิ้น',
            ],
            [
                'กางเกงยีนส์',
                'PROD002',  // หรือระบุเองก็ได้
                '8850123456790',
                'กางเกงยีนส์ขายาว สีน้ำเงิน',
                300.0,
                550.0,
                50,
                'เสื้อผ้า',
                "Levi's",
                'ตัว',
            ],
        ];
    }

    public function headings(): array
    {
        return [
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
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // ชื่อสินค้า
            'B' => 15,  // รหัสสินค้า
            'C' => 15,  // บาร์โค้ด
            'D' => 35,  // รายละเอียด
            'E' => 12,  // ราคาทุน
            'F' => 12,  // ราคาขาย
            'G' => 15,  // จำนวนสต็อก
            'H' => 15,  // หมวดหมู่
            'I' => 15,  // ยี่ห้อ
            'J' => 12,  // หน่วยนับ
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // สไตล์สำหรับหัวตาราง
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '1F2937'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DBEAFE'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '9CA3AF'],
                ],
            ],
        ]);

        // สไตล์สำหรับข้อมูล
        $sheet->getStyle('A2:J3')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // จัดตำแหน่งคอลัมน์ตัวเลข
        $sheet->getStyle('E2:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // ความสูงของแถว
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }
}
