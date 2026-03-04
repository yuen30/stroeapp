# 📦 Enterprise Auto-Parts Inventory System

ระบบบริหารจัดการคลังสินค้าและจำหน่ายอะไหล่ยนต์อัจฉริยะ (Store App) - Powered by Filament v5

---

## 📌 บทนำ (Overview)

ระบบ **Enterprise Auto-Parts Inventory System (Store App)** คือแพลตฟอร์มบริหารจัดการคลังสินค้าสำหรับธุรกิจจัดจำหน่ายอะไหล่ยนต์ ที่ถูกออกแบบและพัฒนามาเพื่อรองรับการดำเนินธุรกิจขนาดกลางจนถึงระดับ **Enterprise** อย่างเต็มรูปแบบ

ระบบมาพร้อมกับโครงสร้างข้อมูลที่ซับซ้อนแต่ยืดหยุ่น เช่น **ระบบตั้งราคาแบบลดหลั่นตามประเภทลูกค้า (Tiered Pricing)**, **ระบบรันเลขเอกสารอัตโนมัติ**, **การรับสินค้าแบบย่อย (Goods Receipt - GR)** และ **การจำแนกเอกสารใบกำกับภาษี (Tax Invoice)** ทั้งหมดดำเนินการผ่านแดชบอร์ดล้ำสมัยที่จัดการง่าย ขับเคลื่อนด้วย **Laravel** และ **Filament PHP**

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
  - ขายสินค้า (SO) ตัดสต็อกและสร้าง StockMovement อัตโนมัติ
  - ยกเลิกเอกสาร คืนสต็อกอัตโนมัติ
  - ตรวจสอบสต็อกเพียงพอก่อนขายอัตโนมัติ
- **Stock Movement Tracking:** บันทึกการเคลื่อนไหวสต็อกทุกครั้งพร้อม stock_before และ stock_after เพื่อ audit trail
- **Comprehensive Relations:** ระบบจัดเก็บคู่ค้าซัพพลายเออร์ (Suppliers) และลูกค้า (Customers) ที่เชื่อมโยงไปหาเอกสารซื้อ-ขายได้อย่างง่ายดาย

### 📄 4. การจัดการเอกสาร (Document Lifecycles)

- **Automated Document Numbering:** ออกเลขที่เอกสารแบบอัตโนมัติผ่าน DocumentNumberService
  - รองรับ Multi-Company และ Multi-Branch
  - ป้องกัน Race Condition ด้วย Database Lock
  - รูปแบบ: `PO2026-0001`, `SO2026-0002`, `GR2026-0003`, `INV2026-0004`
- **Purchase Order (PO):** สร้างเอกสารสั่งซื้อไปยังซัพพลายเออร์พร้อมเลขที่เอกสารอัตโนมัติ
- **Goods Receipt (GR):** รองรับรูปแบบเมื่อโรงงานส่งสินค้าไม่ครบตาม PO (Partial Receive) ด้วยระบบการเปิดบิลรับสินค้า
- **Sale Order (SO):** ใบสั่งขายพร้อมระบบตัดสต็อกอัตโนมัติ
- **Tax Invoice (INV):** แยกเอกสารใบกำกับภาษีออกจาก Sale Order ทำให้การทำบัญชีคล่องตัว

---

## 🛠 เทคโนโลยีและการออกแบบระบบ (Tech Stack & Architecture)

### Backend Stack

- **Framework:** [Laravel 12.x](https://laravel.com/) (PHP 8.2+)
- **Admin Panel:** [Filament PHP v5.3](https://filamentphp.com/) - Server-Driven UI Framework
- **UI Theme:** [Filament Shadcn Theme](https://github.com/openplain/filament-shadcn-theme)
- **Avatar Generator:** [Filament DiceBear](https://github.com/leek/filament-dicebear)

### Filament Architecture

Filament เป็น **Server-Driven UI (SDUI) Framework** ที่ใช้ Livewire, Alpine.js และ Tailwind CSS:

- **Resources:** CRUD interfaces สำหรับ Eloquent models
- **Schemas:** Component-based UI builders (Forms, Tables, Infolists)
- **Actions:** Encapsulated button + modal + logic
- **Notifications:** Flash, Database, และ Broadcast notifications
- **Widgets:** Dashboard components สำหรับแสดงข้อมูลสถิติ

### Database Architecture

- **Database:** PostgreSQL (รองรับ MySQL/SQLite)
- **Primary Keys:** ULID (ความปลอดภัยสูง, ซ่อนโครงสร้าง DB, URL-safe)
- **Soft Deletes:** ทุกตารางสำคัญใช้ soft delete ป้องกันการลบข้อมูลผิดพลาด
- **Relationships:** Foreign Key Constraints ครบถ้วน

### Design Patterns & Best Practices

- **Observer Pattern:** ใช้ Laravel Observers สำหรับ business logic อัตโนมัติ
  - GoodsReceiptObserver - จัดการสต็อกเมื่อรับสินค้า
  - SaleOrderObserver - ตัดสต็อกเมื่อขาย
  - PurchaseOrderObserver - สร้างเลขที่เอกสาร PO
  - TaxInvoiceObserver - สร้างเลขที่ใบกำกับภาษี
  - ProductObserver - ป้องกันการลบสินค้าที่มีสต็อก
- **Service Layer:** DocumentNumberService สำหรับจัดการเลขที่เอกสารแบบรวมศูนย์
- **Modular Architecture:** แยก Form/Table Schema ออกจาก Resource เพื่อความเป็นระเบียบ
  - ทุก Resource มี Pages, Schemas, และ Tables แยกกัน
  - Component Classes สำหรับ reusable components
  - Action Classes สำหรับ complex actions
- **Enum Classes:** ใช้ PHP Enums สำหรับค่าคงที่ (OrderStatus, StockMovementType, DocumentType, PaymentStatus)
- **Authorization:** Model Policies สำหรับ access control ทุก Resource

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

เข้าใช้งานที่: `http://localhost:8000/admin`

---

## 📁 โครงสร้างโปรเจ็กต์ (Project Structure)

### โครงสร้างหลัก

```text
storeapp/
├── app/
│   ├── Enums/                    # PHP Enums (OrderStatus, StockMovementType, etc.)
│   ├── Filament/
│   │   ├── Pages/                # Custom Filament Pages
│   │   └── Resources/            # Filament CRUD Resources (14 Resources)
│   │       ├── Branches/
│   │       ├── Brands/
│   │       ├── Categories/
│   │       ├── Companies/
│   │       ├── Customers/
│   │       ├── GoodsReceipts/
│   │       ├── Products/
│   │       ├── PurchaseOrders/
│   │       ├── SaleOrders/
│   │       ├── Stocks/
│   │       ├── StockMovements/
│   │       ├── Suppliers/
│   │       ├── TaxInvoices/
│   │       └── Units/
│   ├── Models/                   # Eloquent Models (20+ models)
│   ├── Observers/                # Laravel Observers (5 observers)
│   │   ├── GoodsReceiptObserver.php
│   │   ├── SaleOrderObserver.php
│   │   ├── PurchaseOrderObserver.php
│   │   ├── TaxInvoiceObserver.php
│   │   └── ProductObserver.php
│   ├── Policies/                 # Authorization Policies
│   └── Services/                 # Service Classes
│       └── DocumentNumberService.php
├── database/
│   ├── migrations/               # 21 migration files
│   └── seeders/
├── docs/                         # Filament v5 Documentation
│   ├── docs-actions/             # Actions documentation
│   ├── docs-forms/               # Form fields documentation
│   ├── docs-schemas/             # Schema components documentation
│   ├── docs-tables/              # Table columns/filters documentation
│   └── ...
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
│   └── List{Entity}.php          # List page with table
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

### 1. การจัดการสต็อกอัตโนมัติ (Automated Stock Management)

ระบบใช้ **Laravel Observers** จัดการสต็อกอัตโนมัติ:

**เมื่อรับสินค้า (GoodsReceipt Confirmed):**

- สร้าง StockMovement (type: In)
- เพิ่มสต็อกสินค้าอัตโนมัติ
- บันทึก stock_before และ stock_after

**เมื่อขายสินค้า (SaleOrder Confirmed):**

- ตรวจสอบสต็อกเพียงพอ
- สร้าง StockMovement (type: Out)
- ตัดสต็อกสินค้าอัตโนมัติ
- บันทึก stock_before และ stock_after

**เมื่อยกเลิกเอกสาร:**

- คืนสต็อกอัตโนมัติ
- ลบ StockMovement ที่เกี่ยวข้อง

### 2. เลขที่เอกสารอัตโนมัติ (Automated Document Numbering)

ระบบใช้ **DocumentNumberService** สร้างเลขที่เอกสารอัตโนมัติ:

- **PO:** PO2026-0001, PO2026-0002, ...
- **SO:** SO2026-0001, SO2026-0002, ...
- **GR:** GR2026-0001, GR2026-0002, ...
- **INV:** INV2026-0001, INV2026-0002, ...

**คุณสมบัติ:**

- รองรับ Multi-Company และ Multi-Branch
- ป้องกัน Race Condition ด้วย Database Lock
- Configurable format (prefix, date format, running length)

### 3. การป้องกันข้อมูล (Data Protection)

**ProductObserver** ป้องกัน:

- ลบสินค้าที่มีสต็อกคงเหลือ
- ลบสินค้าที่มีประวัติการเคลื่อนไหวสต็อก

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
- `sale_orders` - ใบสั่งขาย
- `sale_order_items` - รายการขาย
- `tax_invoices` - ใบกำกับภาษี

**คลังสินค้า:**

- `stocks` - สต็อกสินค้า
- `stock_movements` - การเคลื่อนไหวสต็อก

**ระบบ:**

- `document_running_numbers` - เลขที่เอกสารอัตโนมัติ

---

## 🧪 การทดสอบ (Testing)

```bash
# รันทดสอบทั้งหมด
composer test

# หรือ
php artisan test
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
