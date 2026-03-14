<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Ulid\Ulid;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Setup Global Document Running Numbers
        // Initialize sequences for this company (now that the company exists)
        (new DocumentRunningNumberSeeder())->run();

        // 1. Create Company
        $company = Company::updateOrCreate(
            ['name' => 'บริษัท ถัง กู๊ดพาร์ท จำกัด'],
            [
                'tax_id' => '0105555555555',
                'tel' => '02-111-2222',
                'fax' => '02-111-2223',
                'address_0' => '123 ถ.สุขุมวิท',
                'address_1' => 'แขวงคลองเตยเหนือ',
                'amphoe' => 'วัฒนา',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10110',
                'is_active' => true,
            ]
        );

        // Create Branch (will use BR- prefix if new)
        $branch = Branch::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'สำนักงานใหญ่'],
            [
                'is_headquarter' => true,
                'tel' => '02-111-2222',
                'fax' => '02-111-2223',
                'address_0' => '123 ถ.สุขุมวิท',
                'address_1' => 'แขวงคลองเตยเหนือ',
                'amphoe' => 'วัฒนา',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10110',
                'is_active' => true,
            ]
        );

        // 2. Create Admin User
        User::updateOrCreate([
            'username' => 'admin',
            'email' => 'admin@store.com',
        ], [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'ผู้ดูแลระบบ (Admin)',
            'password' => Hash::make('password'),
            'role' => env('ADMIN_ROLE', 'admin'),  // Fallback to 'admin'
            'is_active' => true,
        ]);

        // Create Staff User
        User::updateOrCreate([
            'username' => 'staff01',
            'email' => 'staff@store.com',
        ], [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'พนักงานขาย (Staff)',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        // 3. Create Master Data (Units, Brands, Categories)
        $unitPiece = Unit::updateOrCreate(['name' => 'ชิ้น', 'code' => 'PCS']);
        $unitSet = Unit::updateOrCreate(['name' => 'ชุด', 'code' => 'SET']);
        $unitLiter = Unit::updateOrCreate(['name' => 'ลิตร', 'code' => 'LTR']);

        $brandToyota = Brand::updateOrCreate(['name' => 'Toyota Genuine Parts', 'code' => 'TOYOTA']);
        $brandBrembo = Brand::updateOrCreate(['name' => 'Brembo', 'code' => 'BREMBO']);
        $brandMotul = Brand::updateOrCreate(['name' => 'Motul', 'code' => 'MOTUL']);

        $catEngine = Category::updateOrCreate(['name' => 'ระบบเครื่องยนต์', 'code' => 'ENG']);
        $catBrake = Category::updateOrCreate(['name' => 'ระบบเบรก', 'code' => 'BRK']);
        $catFluid = Category::updateOrCreate(['name' => 'น้ำมันและสารหล่อลื่น', 'code' => 'FLD']);

        // 4. Create Trading Partners (Suppliers & Customers)
        $suppliers = [
            [
                'name' => 'บริษัท โตโยต้า มอเตอร์ (ประเทศไทย) จำกัด',
                'code' => 'SUP-001',
                'contact_name' => 'คุณสมเกียรติ วงศ์ใหญ่',
                'tel' => '02-386-2000',
                'fax' => '02-386-2001',
                'address_0' => '186/1 ถ.มิตรไมตรี',
                'address_1' => 'แขวงดินแดง',
                'amphoe' => 'ดินแดง',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10400',
                'tax_id' => '0107537000151',
                'is_active' => true,
            ],
            [
                'name' => 'บริษัท เบรมโบ้ (ประเทศไทย) จำกัด',
                'code' => 'SUP-002',
                'contact_name' => 'คุณวิชัย สุขสันต์',
                'tel' => '02-719-8888',
                'fax' => '02-719-8889',
                'address_0' => '700/199 หมู่ 1 ถ.บางนา-ตราด',
                'address_1' => 'ต.บางแก้ว',
                'amphoe' => 'บางพลี',
                'province' => 'สมุทรปราการ',
                'postal_code' => '10540',
                'tax_id' => '0105548012345',
                'is_active' => true,
            ],
            [
                'name' => 'บริษัท โมทูล (ประเทศไทย) จำกัด',
                'code' => 'SUP-003',
                'contact_name' => 'คุณประสิทธิ์ ชัยชนะ',
                'tel' => '02-361-5555',
                'fax' => '02-361-5556',
                'address_0' => '1010 ถ.วิภาวดีรังสิต',
                'address_1' => 'แขวงจตุจักร',
                'amphoe' => 'จตุจักร',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10900',
                'tax_id' => '0105549023456',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            $code = $supplierData['code'];
            unset($supplierData['code']); // Let observer handle it if new

            Supplier::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $supplierData['name'],
                ],
                $supplierData + ['company_id' => $company->id]
            );
        }

        // สร้างลูกค้าหลายรายการ พร้อมเงื่อนไขการชำระเงินที่แตกต่างกัน
        $customers = [
            [
                'name' => 'อู่ขจรเจริญยนต์',
                'code' => 'CUS-001',
                'tel' => '081-222-3333',
                'fax' => '034-123-456',
                'address_0' => '456 ถ.พระราม 2',
                'address_1' => 'ต.ท่าทราย',
                'amphoe' => 'เมืองสมุทรสาคร',
                'province' => 'สมุทรสาคร',
                'postal_code' => '74000',
                'tax_id' => '0105566677788',
                'credit_days' => 0,  // เงินสด
                'credit_limit' => 0,
                'vat_rate' => 7,
                'is_head_office' => true,
                'branch_no' => null,
                'is_active' => true,
            ],
            [
                'name' => 'บริษัท ศรีสมบูรณ์ ออโต้พาร์ท จำกัด',
                'code' => 'CUS-002',
                'tel' => '02-555-6666',
                'fax' => '02-555-6667',
                'address_0' => '789 ถ.รัชดาภิเษก',
                'address_1' => 'แขวงดินแดง',
                'amphoe' => 'ดินแดง',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10400',
                'tax_id' => '0105577788899',
                'credit_days' => 7,  // เครดิต 7 วัน
                'credit_limit' => 50000,
                'vat_rate' => 7,
                'is_head_office' => true,
                'branch_no' => null,
                'is_active' => true,
            ],
            [
                'name' => 'ห้างหุ้นส่วนจำกัด วิชัยการช่าง',
                'code' => 'CUS-003',
                'tel' => '089-333-4444',
                'fax' => null,
                'address_0' => '321 ถ.บางนา-ตราด',
                'address_1' => 'แขวงบางนา',
                'amphoe' => 'บางนา',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10260',
                'tax_id' => '0105588899900',
                'credit_days' => 15,  // เครดิต 15 วัน
                'credit_limit' => 100000,
                'vat_rate' => 7,
                'is_head_office' => true,
                'branch_no' => null,
                'is_active' => true,
            ],
            [
                'name' => 'อู่ประชาชื่น',
                'code' => 'CUS-004',
                'tel' => '092-444-5555',
                'fax' => null,
                'address_0' => '654 ถ.เพชรบุรี',
                'address_1' => 'แขวงมักกะสัน',
                'amphoe' => 'ราชเทวี',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10400',
                'tax_id' => null,  // ไม่มีเลขผู้เสียภาษี (ร้านเล็ก)
                'credit_days' => 0,  // เงินสด
                'credit_limit' => 0,
                'vat_rate' => 0,  // ไม่คิด VAT
                'is_head_office' => true,
                'branch_no' => null,
                'is_active' => true,
            ],
            [
                'name' => 'บริษัท มหานครออโต้ จำกัด',
                'code' => 'CUS-005',
                'tel' => '02-777-8888',
                'fax' => '02-777-8889',
                'address_0' => '987 ถ.ลาดพร้าว',
                'address_1' => 'แขวงจันทรเกษม',
                'amphoe' => 'จตุจักร',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10900',
                'tax_id' => '0105599900011',
                'credit_days' => 30,  // เครดิต 30 วัน
                'credit_limit' => 200000,
                'vat_rate' => 7,
                'is_head_office' => true,
                'branch_no' => null,
                'is_active' => true,
            ],
            [
                'name' => 'ห้างหุ้นส่วนจำกัด สุขสันต์การช่าง',
                'code' => 'CUS-006',
                'tel' => '088-666-7777',
                'fax' => '02-294-5678',
                'address_0' => '147 ถ.พระราม 3',
                'address_1' => 'แขวงบางโพงพาง',
                'amphoe' => 'ยานนาวา',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10120',
                'tax_id' => '0105500011122',
                'credit_days' => 45,  // เครดิต 45 วัน
                'credit_limit' => 150000,
                'vat_rate' => 7,
                'is_head_office' => false,
                'branch_no' => '00001',  // สาขาที่ 1
                'is_active' => true,
            ],
            [
                'name' => 'อู่เจริญศิลป์',
                'code' => 'CUS-007',
                'tel' => '095-888-9999',
                'fax' => null,
                'address_0' => '258 ถ.สุขุมวิท',
                'address_1' => 'แขวงคลองเตย',
                'amphoe' => 'คลองเตย',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10110',
                'tax_id' => null,  // ไม่มีเลขผู้เสียภาษี
                'credit_days' => 0,  // เงินสด
                'credit_limit' => 0,
                'vat_rate' => 0,  // ไม่คิด VAT
                'is_head_office' => true,
                'branch_no' => null,
                'is_active' => true,
            ],
            [
                'name' => 'บริษัท ไทยออโต้ซัพพลาย จำกัด',
                'code' => 'CUS-008',
                'tel' => '02-999-0000',
                'fax' => '02-999-0001',
                'address_0' => '369 ถ.วิภาวดีรังสิต',
                'address_1' => 'แขวงจอมพล',
                'amphoe' => 'จตุจักร',
                'province' => 'กรุงเทพมหานคร',
                'postal_code' => '10900',
                'tax_id' => '0105511122233',
                'credit_days' => 60,  // เครดิต 60 วัน
                'credit_limit' => 300000,
                'vat_rate' => 7,
                'is_head_office' => false,
                'branch_no' => '00002',  // สาขาที่ 2
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customerData) {
            unset($customerData['code']); // Let observer handle it

            Customer::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $customerData['name'],
                ],
                $customerData + ['company_id' => $company->id]
            );
        }

        // 5. Create Products
        $products = [
            // ระบบเบรก
            ['code' => 'BR-001-REV', 'category' => $catBrake, 'brand' => $brandBrembo, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หน้า Brembo - Revo', 'barcode' => '885000000001', 'cost' => 1200, 'price' => 1800, 'stock' => 10],
            ['code' => 'BR-002-REV', 'category' => $catBrake, 'brand' => $brandBrembo, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หลัง Brembo - Revo', 'barcode' => '885000000002', 'cost' => 1000, 'price' => 1500, 'stock' => 15],
            ['code' => 'BR-003-FOR', 'category' => $catBrake, 'brand' => $brandBrembo, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หน้า Brembo - Fortuner', 'barcode' => '885000000003', 'cost' => 1500, 'price' => 2200, 'stock' => 8],
            ['code' => 'BR-004-FOR', 'category' => $catBrake, 'brand' => $brandBrembo, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หลัง Brembo - Fortuner', 'barcode' => '885000000004', 'cost' => 1300, 'price' => 1900, 'stock' => 12],
            ['code' => 'BR-005-CAM', 'category' => $catBrake, 'brand' => $brandToyota, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หน้า แท้ - Camry', 'barcode' => '885000000005', 'cost' => 1800, 'price' => 2500, 'stock' => 6],
            ['code' => 'BR-006-CAM', 'category' => $catBrake, 'brand' => $brandToyota, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หลัง แท้ - Camry', 'barcode' => '885000000006', 'cost' => 1600, 'price' => 2200, 'stock' => 8],
            ['code' => 'BR-007-COR', 'category' => $catBrake, 'brand' => $brandToyota, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หน้า แท้ - Corolla', 'barcode' => '885000000007', 'cost' => 1000, 'price' => 1500, 'stock' => 20],
            ['code' => 'BR-008-COR', 'category' => $catBrake, 'brand' => $brandToyota, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หลัง แท้ - Corolla', 'barcode' => '885000000008', 'cost' => 900, 'price' => 1300, 'stock' => 18],
            ['code' => 'BR-009-YAR', 'category' => $catBrake, 'brand' => $brandToyota, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หน้า แท้ - Yaris', 'barcode' => '885000000009', 'cost' => 800, 'price' => 1200, 'stock' => 25],
            ['code' => 'BR-010-YAR', 'category' => $catBrake, 'brand' => $brandToyota, 'unit' => $unitSet, 'name' => 'ผ้าเบรกคู่หลัง แท้ - Yaris', 'barcode' => '885000000010', 'cost' => 700, 'price' => 1000, 'stock' => 22],
            // น้ำมันเครื่อง
            ['code' => 'MT-HT-10W40', 'category' => $catFluid, 'brand' => $brandMotul, 'unit' => $unitLiter, 'name' => 'น้ำมันเครื่อง Motul H-Tech 10W-40', 'barcode' => '885000000011', 'cost' => 250, 'price' => 350, 'stock' => 50],
            ['code' => 'MT-HT-5W30', 'category' => $catFluid, 'brand' => $brandMotul, 'unit' => $unitLiter, 'name' => 'น้ำมันเครื่อง Motul H-Tech 5W-30', 'barcode' => '885000000012', 'cost' => 280, 'price' => 400, 'stock' => 45],
            ['code' => 'MT-8100-5W40', 'category' => $catFluid, 'brand' => $brandMotul, 'unit' => $unitLiter, 'name' => 'น้ำมันเครื่อง Motul 8100 5W-40', 'barcode' => '885000000013', 'cost' => 350, 'price' => 500, 'stock' => 40],
            ['code' => 'TY-0W20-1L', 'category' => $catFluid, 'brand' => $brandToyota, 'unit' => $unitLiter, 'name' => 'น้ำมันเครื่อง Toyota 0W-20 แท้', 'barcode' => '885000000014', 'cost' => 300, 'price' => 450, 'stock' => 60],
            ['code' => 'TY-5W30-1L', 'category' => $catFluid, 'brand' => $brandToyota, 'unit' => $unitLiter, 'name' => 'น้ำมันเครื่อง Toyota 5W-30 แท้', 'barcode' => '885000000015', 'cost' => 280, 'price' => 420, 'stock' => 55],
            // ไส้กรอง
            ['code' => 'TY-90915-YZZD2', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองน้ำมันเครื่อง VIGO/REVO แท้', 'barcode' => '885000000016', 'cost' => 150, 'price' => 220, 'stock' => 100],
            ['code' => 'TY-90915-10003', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองน้ำมันเครื่อง Fortuner แท้', 'barcode' => '885000000017', 'cost' => 180, 'price' => 260, 'stock' => 80],
            ['code' => 'TY-90915-YZZJ1', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองน้ำมันเครื่อง Camry แท้', 'barcode' => '885000000018', 'cost' => 200, 'price' => 300, 'stock' => 70],
            ['code' => 'TY-90915-YZZJ2', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองน้ำมันเครื่อง Corolla แท้', 'barcode' => '885000000019', 'cost' => 120, 'price' => 180, 'stock' => 120],
            ['code' => 'TY-90915-YZZJ3', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองน้ำมันเครื่อง Yaris แท้', 'barcode' => '885000000020', 'cost' => 100, 'price' => 150, 'stock' => 150],
            ['code' => 'TY-17801-21050', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองอากาศ VIGO/REVO แท้', 'barcode' => '885000000021', 'cost' => 250, 'price' => 380, 'stock' => 60],
            ['code' => 'TY-17801-0C010', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองอากาศ Fortuner แท้', 'barcode' => '885000000022', 'cost' => 300, 'price' => 450, 'stock' => 50],
            ['code' => 'TY-17801-22020', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองอากาศ Camry แท้', 'barcode' => '885000000023', 'cost' => 350, 'price' => 520, 'stock' => 40],
            ['code' => 'TY-17801-21030', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองอากาศ Corolla แท้', 'barcode' => '885000000024', 'cost' => 200, 'price' => 300, 'stock' => 80],
            ['code' => 'TY-17801-0D060', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ไส้กรองอากาศ Yaris แท้', 'barcode' => '885000000025', 'cost' => 180, 'price' => 270, 'stock' => 90],
            // หลอดไฟ
            ['code' => 'TY-90981-13033', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'หลอดไฟหน้า H4 แท้', 'barcode' => '885000000026', 'cost' => 150, 'price' => 250, 'stock' => 100],
            ['code' => 'TY-90981-13034', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'หลอดไฟหน้า H7 แท้', 'barcode' => '885000000027', 'cost' => 180, 'price' => 280, 'stock' => 90],
            ['code' => 'TY-90981-13035', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'หลอดไฟหน้า H11 แท้', 'barcode' => '885000000028', 'cost' => 200, 'price' => 300, 'stock' => 80],
            ['code' => 'TY-90981-13036', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'หลอดไฟหน้า HB3 แท้', 'barcode' => '885000000029', 'cost' => 220, 'price' => 330, 'stock' => 70],
            ['code' => 'TY-90981-13037', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'หลอดไฟหน้า HB4 แท้', 'barcode' => '885000000030', 'cost' => 220, 'price' => 330, 'stock' => 70],
            // แบตเตอรี่
            ['code' => 'BAT-55D23L', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'แบตเตอรี่ 55D23L', 'barcode' => '885000000031', 'cost' => 2000, 'price' => 2800, 'stock' => 15],
            ['code' => 'BAT-75D23L', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'แบตเตอรี่ 75D23L', 'barcode' => '885000000032', 'cost' => 2500, 'price' => 3500, 'stock' => 12],
            ['code' => 'BAT-80D26L', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'แบตเตอรี่ 80D26L', 'barcode' => '885000000033', 'cost' => 2800, 'price' => 3900, 'stock' => 10],
            ['code' => 'BAT-95D31L', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'แบตเตอรี่ 95D31L', 'barcode' => '885000000034', 'cost' => 3200, 'price' => 4500, 'stock' => 8],
            // สายพาน
            ['code' => 'TY-90916-02582', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'สายพานไดนาโม VIGO/REVO แท้', 'barcode' => '885000000035', 'cost' => 350, 'price' => 520, 'stock' => 30],
            ['code' => 'TY-90916-02583', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'สายพานไดนาโม Fortuner แท้', 'barcode' => '885000000036', 'cost' => 400, 'price' => 600, 'stock' => 25],
            ['code' => 'TY-90916-02584', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'สายพานไดนาโม Camry แท้', 'barcode' => '885000000037', 'cost' => 450, 'price' => 680, 'stock' => 20],
            ['code' => 'TY-90916-02585', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'สายพานไดนาโม Corolla แท้', 'barcode' => '885000000038', 'cost' => 300, 'price' => 450, 'stock' => 35],
            ['code' => 'TY-90916-02586', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'สายพานไดนาโม Yaris แท้', 'barcode' => '885000000039', 'cost' => 280, 'price' => 420, 'stock' => 40],
            // ปั๊มน้ำ
            ['code' => 'TY-16100-09450', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ปั๊มน้ำ VIGO/REVO แท้', 'barcode' => '885000000040', 'cost' => 2500, 'price' => 3500, 'stock' => 10],
            ['code' => 'TY-16100-09451', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ปั๊มน้ำ Fortuner แท้', 'barcode' => '885000000041', 'cost' => 2800, 'price' => 3900, 'stock' => 8],
            ['code' => 'TY-16100-09452', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ปั๊มน้ำ Camry แท้', 'barcode' => '885000000042', 'cost' => 3000, 'price' => 4200, 'stock' => 6],
            ['code' => 'TY-16100-09453', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ปั๊มน้ำ Corolla แท้', 'barcode' => '885000000043', 'cost' => 2200, 'price' => 3100, 'stock' => 12],
            ['code' => 'TY-16100-09454', 'category' => $catEngine, 'brand' => $brandToyota, 'unit' => $unitPiece, 'name' => 'ปั๊มน้ำ Yaris แท้', 'barcode' => '885000000044', 'cost' => 2000, 'price' => 2800, 'stock' => 15],
            // น้ำมันเกียร์
            ['code' => 'MT-GEAR-75W90', 'category' => $catFluid, 'brand' => $brandMotul, 'unit' => $unitLiter, 'name' => 'น้ำมันเกียร์ Motul 75W-90', 'barcode' => '885000000045', 'cost' => 400, 'price' => 600, 'stock' => 30],
            ['code' => 'MT-GEAR-80W90', 'category' => $catFluid, 'brand' => $brandMotul, 'unit' => $unitLiter, 'name' => 'น้ำมันเกียร์ Motul 80W-90', 'barcode' => '885000000046', 'cost' => 350, 'price' => 520, 'stock' => 35],
            ['code' => 'TY-GEAR-75W85', 'category' => $catFluid, 'brand' => $brandToyota, 'unit' => $unitLiter, 'name' => 'น้ำมันเกียร์ Toyota 75W-85 แท้', 'barcode' => '885000000047', 'cost' => 450, 'price' => 680, 'stock' => 25],
            ['code' => 'TY-ATF-WS', 'category' => $catFluid, 'brand' => $brandToyota, 'unit' => $unitLiter, 'name' => 'น้ำมันเกียร์ออโต้ Toyota ATF-WS แท้', 'barcode' => '885000000048', 'cost' => 500, 'price' => 750, 'stock' => 40],
            // น้ำมันเบรก
            ['code' => 'MT-DOT4', 'category' => $catFluid, 'brand' => $brandMotul, 'unit' => $unitLiter, 'name' => 'น้ำมันเบรก Motul DOT4', 'barcode' => '885000000049', 'cost' => 200, 'price' => 300, 'stock' => 50],
            ['code' => 'TY-DOT3', 'category' => $catFluid, 'brand' => $brandToyota, 'unit' => $unitLiter, 'name' => 'น้ำมันเบรก Toyota DOT3 แท้', 'barcode' => '885000000050', 'cost' => 180, 'price' => 270, 'stock' => 60],
        ];

        foreach ($products as $productData) {
            $code = $productData['code'];
            unset($productData['code']); // Let observer handle it

            $product = Product::updateOrCreate(
                ['name' => $productData['name']],
                [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'category_id' => $productData['category']->id,
                    'brand_id' => $productData['brand']->id,
                    'unit_id' => $productData['unit']->id,
                    'name' => $productData['name'],
                    'barcode' => $productData['barcode'],
                    'description' => $productData['name'],
                    'cost_price' => $productData['cost'],
                    'selling_price' => $productData['price'],
                    'stock_quantity' => $productData['stock'],
                ]
            );

            // Create stock record
            if (!$product->stocks()->exists()) {
                \App\Models\Stock::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'quantity' => $productData['stock'],
                ]);
            }
        }

        $this->command->info('Created ' . count($products) . ' products with stock records!');
    }
}
