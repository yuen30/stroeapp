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
        // 1. Create Company and Branch
        $company = Company::create([
            'name' => 'บริษัท อะไหล่ไทย จำกัด (มหาชน)',
            'code' => 'COMP-001',
            'tax_id' => '0105555555555',
            'tel' => '02-111-2222',
            'address_0' => '123 ถ.สุขุมวิท กรุงเทพมหานคร 10110'
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'สำนักงานใหญ่',
            'code' => 'HQ001',
            'address_0' => '123 ถ.สุขุมวิท กรุงเทพมหานคร 10110'
        ]);

        // 2. Create Admin User
        User::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'ผู้ดูแลระบบ (Admin)',
            'username' => 'admin',
            'email' => 'admin@store.com',
            'password' => Hash::make('password'),
            'role' => env('ADMIN_ROLE', 'admin'), // Fallback to 'admin'
            'is_active' => true,
        ]);

        // Create Staff User
        User::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'พนักงานขาย (Staff)',
            'username' => 'staff01',
            'email' => 'staff@store.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        // 3. Create Master Data (Units, Brands, Categories)
        $unitPiece = Unit::create(['name' => 'ชิ้น', 'code' => 'PCS']);
        $unitSet = Unit::create(['name' => 'ชุด', 'code' => 'SET']);
        $unitLiter = Unit::create(['name' => 'ลิตร', 'code' => 'LTR']);

        $brandToyota = Brand::create(['name' => 'Toyota Genuine Parts', 'code' => 'TOYOTA']);
        $brandBrembo = Brand::create(['name' => 'Brembo', 'code' => 'BREMBO']);
        $brandMotul = Brand::create(['name' => 'Motul', 'code' => 'MOTUL']);

        $catEngine = Category::create(['name' => 'ระบบเครื่องยนต์', 'code' => 'ENG']);
        $catBrake = Category::create(['name' => 'ระบบเบรก', 'code' => 'BRK']);
        $catFluid = Category::create(['name' => 'น้ำมันและสารหล่อลื่น', 'code' => 'FLD']);

        // 4. Create Trading Partners (Suppliers & Customers)
        $supplier1 = Supplier::create([
            'company_id' => $company->id,
            'name' => 'บริษัท โตโยต้า มอเตอร์ (ประเทศไทย)',
            'code' => 'SUP-001',
            'contact_name' => 'สมเกียรติ',
            'tel' => '02-386-2000'
        ]);

        $customer1 = Customer::create([
            'company_id' => $company->id,
            'name' => 'อู่ขจรเจริญยนต์',
            'code' => 'CUS-001',
            'tel' => '081-222-3333'
        ]);

        // 5. Create Products
        Product::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'category_id' => $catBrake->id,
            'brand_id' => $brandBrembo->id,
            'unit_id' => $unitSet->id,
            'name' => 'ผ้าเบรกคู่หน้า Brembo - Revo',
            'code' => 'BR-001-REV',
            'barcode' => '885000000001',
            'description' => 'ผ้าเบรกคู่หน้าสำหรับ Toyota Revo',
            'cost_price' => 1200,
            'selling_price' => 1800,
            'stock_quantity' => 10,
        ]);

        Product::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'category_id' => $catFluid->id,
            'brand_id' => $brandMotul->id,
            'unit_id' => $unitLiter->id,
            'name' => 'น้ำมันเครื่อง Motul H-Tech 10W-40',
            'code' => 'MT-HT-10W40',
            'barcode' => '885000000002',
            'description' => 'น้ำมันเครื่องสังเคราะห์ 100% ขนาด 1 ลิตร',
            'cost_price' => 250,
            'selling_price' => 350,
            'stock_quantity' => 50,
        ]);

        Product::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'category_id' => $catEngine->id,
            'brand_id' => $brandToyota->id,
            'unit_id' => $unitPiece->id,
            'name' => 'ไส้กรองน้ำมันเครื่อง VIGO/REVO แท้',
            'code' => 'TY-90915-YZZD2',
            'barcode' => '885000000003',
            'description' => 'ไส้กรองแท้เบิกศูนย์',
            'cost_price' => 150,
            'selling_price' => 220,
            'stock_quantity' => 100,
        ]);

    }
}
