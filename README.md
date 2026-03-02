<div align="center">
    <h1>📦 Enterprise Auto-Parts Inventory System</h1>
    <p>ระบบบริหารจัดการคลังสินค้าและจำหน่ายอะไหล่ยนต์อัจฉริยะ (Store App)</p>
</div>

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

### 🏭 3. ระบบจัดการคลังและพันธมิตร (Inventory & Entities)

- **Multi-Branch & Company:** รองรับการขยายสาขาในองค์กรรูปแบบ Multi-Tenant / Branch
- **Stock Movements ควบคุมสต็อกเสถียรภาพสูง:** ผูกการเคลื่อนไหวของสินค้ากับการรับของ `GoodsReceipt` และการออกเอกสาร `SaleOrder` เสมอ
- **Comprehensive Relations:** ระบบจัดเก็บคู่ค้าซัพพลายเออร์ (Suppliers) และลูกค้า (Customers) ที่เชื่อมโยงไปหาเอกสารซื้อ-ขายได้อย่างง่ายดาย

### 📄 4. การจัดการเอกสาร (Document Lifecycles)

- **Document Running Number:** ออกเลขที่เอกสารแบบอัตโนมัติที่มีโครงสร้างมาตรฐาน เช่น `PO2026-0001` , `IN2026-0002`
- **Purchase Order (PO):** สร้างเอกสารสั่งซื้อไปยังซัพพลายเออร์
- **Goods Receipt (GR):** รองรับรูปแบบเมื่อโรงงานส่งสินค้าไม่ครบตาม PO (Partial Receive) ด้วยระบบการเปิดบิลรับสินค้า
- **Sale Order (SO) & Tax Invoice (INV):** มีการแยกเอกสารใบสั่งขายกับใบกำกับภาษีออกจากกัน ทำให้การทำบัญชีคล่องตัวและหลีกเลี่ยงข้อกำหนดทางกฎหมายที่ซับซ้อนเมื่อมีการส่งของไม่ตรงบิล

---

## 🛠 เทคโนโลยีและการออกแบบระบบ (Tech Stack & Architecture)

- **Backend Framework:** [Laravel 11.x](https://laravel.com/)
- **Admin Panel Framework:** [Filament PHP v3](https://filamentphp.com/)
- **Database Architecture:** PostgreSQL
    - 🔑 **ULID Primary Keys:** ประสิทธิภาพความปลอดภัยขั้นสูง (ไม่มีการใช้ auto increment id ธรรมดา) ช่วยปกปิดโครงสร้างฐานข้อมูล และปลอดภัยต่อ URL
    - 🗑 **Soft Deletes:** ฐานข้อมูลใช้การทำเครื่องหมายลบข้อมูล (Trashed) แทนที่จะลบข้อมูลทิ้งจริง ครอบคลุมผู้ใช้, ทีม, ข้อมูลสินค้า และเอกสารสำคัญทุกตาราง ลดโอกาสเกิดความเสียหายเมื่อผู้ใช้งานลบข้อมูลผิดพลาด
- **Separation of Concerns ปรับโครงสร้าง Code ตาม Best Practices:** แผงควบคุมของแดชบอร์ดจะถูกปรับโครงสร้างและแยกแยะ ไฟล์ Form UI ของ Resource (เช่น `Schemas/UserForm.php`) ออกจากกัน เพื่อลดทอนปริมาณโค้ดที่ล้นหลามและทำให้โค้ดเป็นระเบียบ ทำให้อ่าน บำรุงรักษาในระยะยาว และแก้ไขได้ง่าย

---

## 🚀 การติดตั้งโปรเจ็กต์ (Installation)

1. **โคลนโปรเจ็กต์ (Clone Repo):**

```bash
git clone {repository-url} storeapp
cd storeapp
```

2. **ติดตั้ง Dependencies:**

```bash
composer install
npm install && npm run build
```

3. **ตั้งค่า Environment:**
   คัดลอกไฟล์ตั้งค่าระบบและสร้าง Application Key

```bash
cp .env.example .env
php artisan key:generate
```

หลังจากตั้งค่าฐานข้อมูลใน `.env` เช่น `DB_CONNECTION=pgsql` เป็นที่เรียบร้อย

4. **เริ่มการสร้างฐานข้อมูล (Migrate):**

```bash
php artisan migrate --seed
```

_(หมายเหตุ: โครงสร้างไฟล์ Migration ได้ถูกจัดเรียงตามลำดับความยึดโยง Relationship (Dependencies) ไว้อย่างถูกต้องแล้วเพื่อป้องกันปัญหาคีย์รอง (Foreign Key Constraint))_

5. **รัน Local Server:**
   เปิดใช้งานเซิร์ฟเวอร์เพื่อให้ทดสอบระบบได้ผ่านเบราว์เซอร์

```bash
composer dev
# หรือ artisan serve
```

---

## 📚 โครงสร้างไฟล์อ้างอิงของ Filament Resources (UI Folder Structure)

ทางทีมได้ทดลองนำ **Filament Form Classes / Table Classes Separation Pattern** มาใช้
ตัวอย่างการวางโครงสร้างคลาส `UserResource`:

```
app/Filament/Resources/
└── Users
    ├── UserResource.php       # คลาสหลักสำหรับจัดการ Routing/Meta
    ├── Pages
    │   ├── CreateUser.php
    │   ├── EditUser.php
    │   └── ListUsers.php
    └── Schemas
        └── UserForm.php       # คลาสอิสระที่รวม Config ของแบบฟอร์ม (TextInputs, Selects ต่างๆ)
```

อ้างอิงจากแบบแผนในเอกสาร: `docs/03-resources/13-code-quality-tips.md`

---

## 👨‍💻 ข้อมูลการติดต่อ

**ทีมพัฒนาและดูแลระบบ Store App**
หากพบปัญหาเรื่องลอจิกของการจัดการสต็อกรับเข้า (Goods Receipt) หรือบัคเกี่ยวกับการแสดงผล Custom Filament UI กรุณาตั้ง Issue เพื่อส่งเข้าตรวจสอบครับ.
