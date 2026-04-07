# 📦 Enterprise Auto-Parts Inventory System

ระบบบริหารจัดการคลังสินค้าและจำหน่ายอะไหล่ยนต์อัจฉริยะ (Store App) - Powered by Filament v5

---

## 📌 บทนำ (Overview)

ระบบ **Enterprise Auto-Parts Inventory System (Store App)** คือแพลตฟอร์มบริหารจัดการคลังสินค้าสำหรับธุรกิจจัดจำหน่ายอะไหล่ยนต์ ที่ถูกออกแบบและพัฒนามาเพื่อรองรับการดำเนินธุรกิจขนาดกลางจนถึงระดับ **Enterprise** อย่างเต็มรูปแบบ

ระบบมาพร้อมกับโครงสร้างข้อมูลที่ซับซ้อนแต่ยืดหยุ่น เช่น **ระบบตั้งราคาแบบลดหลั่นตามประเภทลูกค้า (Tiered Pricing)**, **ระบบจองสต็อกสำหรับ Draft Sale Order**, **การรับสินค้าแบบย่อย (Goods Receipt - GR)**, **แดชบอร์ดและรายงาน**, และ **การจำแนกเอกสารใบกำกับภาษี (Tax Invoice)** ทั้งหมดดำเนินการผ่านแผงจัดการที่สร้างด้วย **Laravel** และ **Filament PHP**

---

## ✨ ความสามารถหลัก (Core Features)

### 👥 1. ระบบผู้ใช้งานและการเข้าสู่ระบบ (Authentication & Identity)

- **Flexible Login:** รองรับการเข้าสู่ระบบแบบยืดหยุ่นด้วย **อีเมล (Email)** หรือ **ชื่อผู้ใช้งาน (Username)** เพียงกรอกในช่องอินพุตเดิม ระบบจะคัดแยกให้อัตโนมัติ
- **Dynamic Avatars:** บูรณาการ [Filament DiceBear Plugin](https://github.com/leek/filament-dicebear) โดยเมื่อผู้ใช้ไม่มีการแนบรูปภาพโปรไฟล์ จะมีการแสดงภาพ Avatar ลายเส้นสไตล์ Adventurer ให้อัตโนมัติ

### 📦 2. โครงสร้างสินค้าระดับองค์กร (Enterprise Product Schema)

- **Hierarchy Management:** จัดสินค้าแบ่งระดับชัดเจนตั้งแต่ สินค้า, หมวดหมู่, ยี่ห้อ และหน่วยนับ
- **Tiered Pricing (ราคาหลายระดับ):** กำหนดราคาสินค้าล่วงหน้าสำหรับลูกค้าแต่ละประเภทได้ (เช่น ราคาขายส่งอู่ซ่อมรถ, ราคาขายปลีกทั่วไป)
- **Multiple Images:** รองรับการแนบรูปภาพพรีวิวอะไหล่แบบหลายรูปต่อหนึ่งสินค้า
- **Stock Protection:** ป้องกันการลบสินค้าที่มีสต็อกคงเหลือหรือมีประวัติการเคลื่อนไหวอัตโนมัติ

### 🏭 3. ระบบจัดการคลังและพันธมิตร (Inventory & Entities)

- **Multi-Branch & Company:** รองรับการขยายสาขาในองค์กรรูปแบบ Multi-Tenant / Branch
- **Automated Stock Management:** ระบบจัดการสต็อกอัตโนมัติผ่าน Laravel Observers
    - รับสินค้า (GR) เพิ่มสต็อกและสร้าง StockMovement อัตโนมัติ
    - สร้าง Draft Sale Order แล้วจองสต็อกทันทีผ่าน `stock_reservations`
    - ยืนยัน Sale Order แล้วปลดการจองก่อนตัดสต็อกจริง
    - ยกเลิกเอกสาร คืนสต็อกหรือปลดการจองอัตโนมัติ
- **Stock Movement Tracking:** บันทึกการเคลื่อนไหวสต็อกทุกครั้งพร้อม stock_before และ stock_after เพื่อ audit trail
- **Comprehensive Relations:** ระบบจัดเก็บคู่ค้าซัพพลายเออร์ (Suppliers) และลูกค้า (Customers) ที่เชื่อมโยงไปหาเอกสารซื้อ-ขายได้อย่างง่ายดาย
- **Dashboard & Reports:** มี dashboard widgets 9 ตัว และหน้ารายงาน low stock / goods receipt ภายใน Filament

### 📄 4. การจัดการเอกสาร (Document Lifecycles)

- **Automated Document Numbering:** ออกเลขที่เอกสารผ่าน `document_running_numbers` ร่วมกับ `DocumentObserver`
    - รองรับ Multi-Company และ Multi-Branch
    - ป้องกัน Race Condition ด้วย Database Lock
    - รูปแบบ: `PO2026-0001`, `SO2026-0002`, `GR2026-0003`, `INV2026-0004`
- **Purchase Order (PO):** สร้างเอกสารสั่งซื้อไปยังซัพพลายเออร์พร้อมเลขที่เอกสารอัตโนมัติ
- **Goods Receipt (GR):** รองรับรูปแบบเมื่อโรงงานส่งสินค้าไม่ครบตาม PO (Partial Receive) ด้วยระบบการเปิดบิลรับสินค้า
- **Sale Order (SO):** ใบส่งสินค้าที่แยกสถานะ `draft` และ `confirmed` ชัดเจน พร้อมระบบจอง/ตัดสต็อก
- **Tax Invoice (INV):** แยกเอกสารใบกำกับภาษีออกจาก Sale Order และรองรับ auto-fill จาก Sale Order ที่ยืนยันแล้ว

---

## 🛠 เทคโนโลยีและการออกแบบระบบ (Tech Stack & Architecture)

### Backend Stack

- **Framework:** [Laravel 12.x](https://laravel.com/) (PHP 8.2+)
- **Admin Panel:** [Filament PHP v5.3](https://filamentphp.com/) - Server-Driven UI Framework
- **UI Theme:** [Filament Shadcn Theme](https://github.com/openplain/filament-shadcn-theme)
- **Avatar Generator:** [Filament DiceBear](https://github.com/leek/filament-dicebear)
- **PDF Export:** [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
- **Activity Log:** [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog)
- **Excel Export:** [pxlrbt/filament-excel](https://github.com/pxlrbt/filament-excel)

### Filament Architecture

Filament เป็น **Server-Driven UI (SDUI) Framework** ที่ใช้ Livewire, Alpine.js และ Tailwind CSS:

- **Resources:** CRUD interfaces สำหรับ Eloquent models (14 resources)
- **Pages:** Custom pages เช่น Login page และ Reports pages
- **Schemas:** Component-based UI builders (Forms, Tables, Infolists)
- **Actions:** Encapsulated button + modal + logic
- **Notifications:** Flash, Database, และ Broadcast notifications
- **Widgets:** Dashboard components สำหรับแสดงข้อมูลสถิติ (9 widgets)

### Database Architecture

- **Database:** PostgreSQL (รองรับ MySQL/SQLite)
- **Primary Keys:** ULID (ความปลอดภัยสูง, ซ่อนโครงสร้าง DB, URL-safe)
- **Soft Deletes:** ทุกตารางสำคัญใช้ soft delete ป้องกันการลบข้อมูลผิดพลาด
- **Relationships:** Foreign Key Constraints ครบถ้วน

### Design Patterns & Best Practices

- **Observer Pattern:** ใช้ Laravel Observers สำหรับ business logic อัตโนมัติ
    - DocumentObserver - จัดการเลขที่เอกสาร/รหัสสำหรับ model ที่ใช้ `DocumentObservable`
    - GoodsReceiptObserver - อัปเดตสต็อกและสถานะใบสั่งซื้อเมื่อรับสินค้า
    - SaleOrderObserver - ยืนยัน, ตัดสต็อก, ยกเลิก, และคืนสต็อก
    - SaleOrderItemObserver - จองสต็อกและคำนวณยอดขายใหม่
    - PurchaseOrderItemObserver - คำนวณยอดใบสั่งซื้อใหม่
    - ProductObserver / StockObserver - sync สต็อกระหว่าง `products` และ `stocks`
- **Service Layer:** มี service สำคัญ เช่น `StockReservationService` และ `DocumentNumberService`
- **Modular Architecture:** แยก Form/Table Schema ออกจาก Resource เพื่อความเป็นระเบียบ
    - ทุก Resource มี Pages, Schemas, และ Tables แยกกัน
    - Component Classes สำหรับ reusable components
    - Action Classes สำหรับ complex actions
- **Enum Classes:** ใช้ PHP Enums สำหรับค่าคงที่ (OrderStatus, StockMovementType, DocumentType, PaymentStatus)
- **Panel Provider:** ตั้งค่า Filament panel, widgets, theme, middleware และ route ที่ `app/Providers/Filament/StorePanelProvider.php`

---

## 🚀 การติดตั้งโปรเจ็กต์ (Installation)

### ข้อกำหนดระบบ (Requirements)

- PHP >= 8.2
- Composer
- Node.js & npm (หรือ Bun)
- PostgreSQL (หรือ MySQL/SQLite)

### ขั้นตอนการติดตั้ง

1. **โคลนโปรเจ็กต์:**

```bash
git clone {repository-url} storeapp
cd storeapp
```

1. **ติดตั้ง Dependencies:**

```bash
composer install
npm install && npm run build
```

1. **ตั้งค่า Environment:**

```bash
cp .env.example .env
php artisan key:generate
```

แก้ไขไฟล์ `.env` ตั้งค่าฐานข้อมูล:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=storeapp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

1. **สร้างฐานข้อมูล:**

```bash
php artisan migrate --seed
```

> **หมายเหตุ:** Migration files ได้ถูกจัดเรียงตามลำดับ dependencies อย่างถูกต้องแล้ว

1. **รันเซิร์ฟเวอร์:**

**แบบง่าย:**

```bash
php artisan serve
```

**แบบเต็มรูปแบบ (พร้อม Queue, Logs, Vite):**

```bash
composer dev
```

เข้าใช้งานที่: `http://localhost:8000/store`

---

## 📁 โครงสร้างโปรเจ็กต์ (Project Structure)

### โครงสร้างหลัก

```text
storeapp/
├── app/
│   ├── Console/
│   │   └── Commands/             # Artisan commands (cleanup reservations, sync stocks)
│   ├── Enums/                    # PHP Enums (OrderStatus, StockMovementType, etc.)
│   ├── Exports/                  # Export classes
│   ├── Filament/
│   │   ├── Pages/                # Custom Login + Reports pages
│   │   │   ├── Auth/
│   │   │   └── Reports/
│   │   ├── Resources/            # Filament CRUD Resources (14 Resources)
│   │   │   ├── Branches/
│   │   │   ├── Brands/
│   │   │   ├── Categories/
│   │   │   ├── Companies/
│   │   │   ├── Customers/
│   │   │   ├── GoodsReceipts/
│   │   │   ├── Products/
│   │   │   ├── PurchaseOrders/
│   │   │   ├── SaleOrders/
│   │   │   ├── Stocks/
│   │   │   ├── Suppliers/
│   │   │   ├── TaxInvoices/
│   │   │   ├── Units/
│   │   │   └── Users/
│   │   └── Widgets/              # Dashboard widgets (9 files)
│   ├── Models/                   # Eloquent Models (20+ models)
│   ├── Observers/                # Laravel Observers (13 files)
│   ├── Providers/
│   │   └── Filament/             # StorePanelProvider.php
│   ├── Services/                 # Business services
│   │   ├── DocumentNumberService.php
│   │   └── StockReservationService.php
│   └── Traits/
│       └── DocumentObservable.php
├── database/
│   ├── migrations/               # 26 migration files
│   └── seeders/
├── docs/                         # Filament v5 Documentation
│   ├── docs-actions/             # Actions documentation
│   ├── docs-forms/               # Form fields documentation
│   ├── docs-infolists/           # Infolist documentation
│   ├── docs-schemas/             # Schema components documentation
│   ├── docs-tables/              # Table columns/filters documentation
│   └── ...
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       └── pdf/                  # PDF templates
├── tests/
│   ├── Feature/
│   └── Unit/
└── ...
```

### Filament Resource Structure Pattern

ทุก Resource ใช้โครงสร้างแบบ Modular ตาม Filament Best Practices:

```text
app/Filament/Resources/{Entity}/
├── {Entity}Resource.php          # Resource หลัก (Routing/Meta/Authorization)
├── Pages/                         # CRUD Pages (Livewire Components)
│   ├── Create{Entity}.php        # Create page with custom actions
│   ├── Edit{Entity}.php          # Edit page with custom actions
│   ├── List{Entity}.php          # List page with table
│   └── View{Entity}.php          # View page (ถ้ามี)
├── Schemas/                       # Form/Infolist Schemas (แยกออกมา)
│   ├── {Entity}Form.php          # Form schema with Sections/Callouts
│   └── {Entity}Infolist.php      # Infolist schema (optional)
└── Tables/                        # Table Schemas (แยกออกมา)
    └── {Entity}Table.php         # Table with columns/filters/actions
```

**ตัวอย่าง:** `app/Filament/Resources/Companies/`

```text
Companies/
├── CompanyResource.php
├── Pages/
│   ├── CreateCompany.php         # ปุ่ม "สร้างบริษัท" + "ยกเลิก" พร้อมไอคอน
│   ├── EditCompany.php           # ปุ่ม "บันทึกการเปลี่ยนแปลง" + "ยกเลิก" + "ลบ" พร้อมไอคอน
│   └── ListCompanies.php
├── Schemas/
│   └── CompanyForm.php           # Form with Sections, Callouts, Icons
└── Tables/
    └── CompaniesTable.php        # Table with Icons, Tooltips, Empty State
```

### Filament Components ที่ใช้ในโปรเจ็กต์

**Schema Components:**

- `Section` - จัดกลุ่มฟิลด์พร้อม icons, descriptions, collapsible
- `Callout` - แสดงคำเตือน/ข้อมูลสำคัญ (info, warning, danger, success)
- `Tabs` - แบ่งฟอร์มเป็น tabs
- `Wizard` - Multi-step forms พร้อม validation
- `Grid` / `Text` / `Placeholder` - layout และข้อมูล dynamic ใน modal/action
- `RepeatableEntry` - แสดงรายการสินค้าใน infolist แบบตาราง

**Form Fields:**

- `TextInput` - ช่องกรอกข้อความพร้อม validation, icons, prefixes
- `Select` - Dropdown พร้อม searchable, multiple
- `Toggle` - สวิตช์ on/off
- `FileUpload` - อัปโหลดไฟล์พร้อม image editor
- `DateTimePicker` - เลือกวันที่และเวลา
- `Repeater` - ฟิลด์ซ้ำได้ (สำหรับ items)

**Table Columns:**

- `TextColumn` - แสดงข้อความพร้อม icons, badges, tooltips
- `ImageColumn` - แสดงรูปภาพ (circular, square)
- `IconColumn` - แสดง icon ตามเงื่อนไข (boolean, status)

**Actions:**

- `CreateAction` - สร้างรายการใหม่
- `EditAction` - แก้ไขรายการ
- `DeleteAction` - ลบรายการ (พร้อม confirmation modal)
- `BulkAction` - ดำเนินการหลายรายการพร้อมกัน

---

## 🎯 ฟีเจอร์อัตโนมัติ (Automated Features)

### 1. การจัดการสต็อกและการจองอัตโนมัติ (Automated Stock Management)

ระบบใช้ **Laravel Observers** จัดการสต็อกอัตโนมัติ:

**เมื่อรับสินค้า (GoodsReceipt Confirmed):**

- สร้าง StockMovement (type: In)
- เพิ่มสต็อกในตาราง `stocks`
- รองรับการ sync กลับไปยัง `products.stock_quantity` ผ่าน observer/command
- บันทึก stock_before และ stock_after

**เมื่อเพิ่มสินค้าใน Draft Sale Order:**

- สร้าง `stock_reservations`
- ตรวจสอบ `available_stock` ก่อนเพิ่มหรือแก้ไข
- ป้องกัน Overselling ระหว่างหลาย draft orders

**เมื่อยืนยัน Sale Order (Draft -> Confirmed):**

- ตรวจสอบสต็อกเพียงพอ
- ปลดล็อก reservation ของเอกสารนั้น
- สร้าง StockMovement (type: Out)
- ตัดสต็อกสินค้าอัตโนมัติ
- บันทึก stock_before และ stock_after

**เมื่อยกเลิกเอกสาร:**

- ยกเลิกจาก `draft` -> ปลด reservation
- ยกเลิกจาก `confirmed` -> คืนสต็อกและลบ StockMovement ที่เกี่ยวข้อง

### 2. เลขที่เอกสารอัตโนมัติ (Automated Document Numbering)

ระบบใช้ `document_running_numbers` ร่วมกับ `DocumentObserver` ในการออกเลขที่เอกสาร:

- **PO:** PO2026-0001, PO2026-0002, ...
- **SO:** SO2026-0001, SO2026-0002, ...
- **GR:** GR2026-0001, GR2026-0002, ...
- **INV:** INV2026-0001, INV2026-0002, ...

**คุณสมบัติ:**

- รองรับ Multi-Company และ Multi-Branch
- ป้องกัน Race Condition ด้วย Database Lock
- Configurable format (prefix, date format, running length)

### 3. Tax Invoice Auto-Fill

- สร้างใบกำกับภาษีจาก Sale Order ที่ `confirmed` ได้โดยตรง
- pre-fill บริษัท, สาขา, ลูกค้า, ที่อยู่, ยอดเงิน และ payment status
- ป้องกันการสร้างใบกำกับภาษีซ้ำให้กับ Sale Order เดิม

### 4. การป้องกันข้อมูล (Data Protection)

**ProductObserver / StockObserver** ป้องกันและ sync ข้อมูล:

- ลบสินค้าที่มีสต็อกคงเหลือ
- ลบสินค้าที่มีประวัติการเคลื่อนไหวสต็อก
- รองรับการ sync ระหว่าง `products.stock_quantity` และ `stocks.quantity`

---

## 📊 โครงสร้างฐานข้อมูล (Database Schema)

### ตารางหลัก (Core Tables)

**องค์กร:**

- `companies` - บริษัท
- `branches` - สาขา
- `users` - ผู้ใช้งาน

**สินค้า:**

- `products` - สินค้า
- `categories` - หมวดหมู่
- `brands` - ยี่ห้อ
- `units` - หน่วยนับ
- `product_prices` - ราคาแบบ Tiered
- `product_images` - รูปภาพสินค้า

**คู่ค้า:**

- `customers` - ลูกค้า
- `suppliers` - ซัพพลายเออร์

**เอกสาร:**

- `purchase_orders` - ใบสั่งซื้อ
- `purchase_order_items` - รายการสั่งซื้อ
- `goods_receipts` - ใบรับสินค้า
- `goods_receipt_items` - รายการรับสินค้า
- `sale_orders` - ใบส่งสินค้า
- `sale_order_items` - รายการขาย
- `tax_invoices` - ใบกำกับภาษี

**คลังสินค้า:**

- `stocks` - สต็อกสินค้า
- `stock_movements` - การเคลื่อนไหวสต็อก
- `stock_reservations` - การจองสต็อกสำหรับ Draft Sale Order

**ระบบ:**

- `document_running_numbers` - เลขที่เอกสารอัตโนมัติ
- `notifications` - Database notifications
- `activity_log` - ประวัติการเปลี่ยนแปลงจาก Spatie Activity Log
- `jobs` / `cache` - ตารางระบบของ Laravel

---

## 🧪 การทดสอบ (Testing)

```bash
# รันทดสอบทั้งหมด
composer test

# หรือ
php artisan test

# รันเฉพาะ unit test ของ stock reservation
php artisan test --filter=StockReservationServiceTest
```

---

## 🔧 คำสั่งที่มีประโยชน์ (Useful Commands)

```bash
# รันเซิร์ฟเวอร์พร้อม Queue, Logs, Vite
composer dev

# ติดตั้งระบบใหม่ทั้งหมด
composer setup

# Clear cache
php artisan optimize:clear

# สร้าง Filament User
php artisan make:filament-user

# ดู logs แบบ real-time
php artisan pail

# ลบการจองสต็อกที่หมดอายุ
php artisan reservations:cleanup

# sync ตาราง stocks กับ products
php artisan stock:sync --force
```

---

## 📝 การพัฒนาเพิ่มเติม (Development Notes)

### การสร้าง Observer ใหม่

```bash
php artisan make:observer YourModelObserver --model=YourModel
```

จากนั้นลงทะเบียนใน `app/Providers/AppServiceProvider.php`:

```php
use App\Models\YourModel;
use App\Observers\YourModelObserver;

public function boot(): void
{
    YourModel::observe(YourModelObserver::class);
}
```

### การสร้าง Filament Resource ใหม่

```bash
php artisan make:filament-resource YourModel --generate
```

### การตั้งค่า Filament Panel

- Panel หลักถูกตั้งค่าใน `app/Providers/Filament/StorePanelProvider.php`
- URL หลักของระบบคือ `/store`
- Dashboard widgets และ theme ถูกลงทะเบียนที่ provider นี้

### Code Style

โปรเจ็กต์ใช้ Laravel Pint สำหรับ code formatting:

```bash
./vendor/bin/pint
```

---

## 🤝 การมีส่วนร่วม (Contributing)

หากพบปัญหาหรือต้องการเสนอแนะ:

1. สร้าง Issue ใน repository
2. Fork และสร้าง Pull Request
3. ติดต่อทีมพัฒนาโดยตรง

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 ทีมพัฒนา (Development Team)

**Enterprise Auto-Parts Inventory System Development Team**

หากพบปัญหาเกี่ยวกับ:

- ลอจิกการจัดการสต็อก (Stock Management)
- ระบบ Observers
- การสร้างเลขที่เอกสาร
- Filament UI Customization

กรุณาสร้าง Issue เพื่อแจ้งปัญหาครับ
