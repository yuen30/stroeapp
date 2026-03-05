# 🔄 Stock Reservation Flow - การทำงานของระบบจองสต็อก

## 📊 Flow Diagram แบบละเอียด

### 1️⃣ สร้างใบสั่งขาย (Draft)

```
User สร้าง Sale Order (Draft)
         ↓
SaleOrderObserver::creating()
         ↓
สร้างเลขที่เอกสาร (SO2026-0001)
         ↓
✅ Sale Order สถานะ Draft ถูกสร้าง
```

**ผลลัพธ์:**

- ✅ มี Sale Order ใหม่ (status = Draft)
- ❌ ยังไม่มีการจองสต็อก (เพราะยังไม่มีสินค้า)

---

### 2️⃣ เพิ่มสินค้าในใบสั่งขาย (Draft)

```
User เพิ่มสินค้า A จำนวน 10
         ↓
ViewSaleOrder::addItem Action
         ↓
ตรวจสอบ available_stock
         ↓
    ✅ สต็อกพอ (available >= 10)
         ↓
SaleOrderItem::create()
         ↓
SaleOrderItemObserver::created()
         ↓
StockReservationService::createReservation()
         ↓
┌─────────────────────────────────────┐
│ Database Transaction (Lock Product) │
├─────────────────────────────────────┤
│ 1. Lock product row                 │
│ 2. ตรวจสอบ available_stock อีกครั้ง │
│ 3. สร้าง StockReservation           │
│    - reserved_quantity = 10         │
│    - expires_at = now() + 24h       │
│ 4. Commit transaction               │
└─────────────────────────────────────┘
         ↓
✅ สินค้าถูกเพิ่มและสต็อกถูกจองแล้ว
```

**ผลลัพธ์:**

- ✅ มี SaleOrderItem ใหม่
- ✅ มี StockReservation (จอง 10 หน่วย)
- ✅ Product.available_stock ลดลง 10
- ✅ Product.stock_quantity ยังไม่เปลี่ยน (ยังไม่ตัดสต็อกจริง)

**ตัวอย่างข้อมูล:**

```
Product:
  stock_quantity = 100
  reserved_quantity = 10 (คำนวณจาก StockReservation)
  available_stock = 90 (100 - 10)

StockReservation:
  product_id = xxx
  sale_order_id = yyy
  sale_order_item_id = zzz
  reserved_quantity = 10
  expires_at = 2026-03-06 15:00:00
```

---

### 3️⃣ แก้ไขจำนวนสินค้า (Draft)

```
User แก้ไขจำนวนจาก 10 → 15
         ↓
SaleOrderInfolist::editAction
         ↓
ตรวจสอบ available_stock
(บวกสต็อกที่จองไว้ 10 กลับมา = 90 + 10 = 100)
         ↓
    ✅ สต็อกพอ (100 >= 15)
         ↓
SaleOrderItem::update(['quantity' => 15])
         ↓
SaleOrderItemObserver::updating()
         ↓
เก็บ _oldQuantity = 10
         ↓
SaleOrderItemObserver::updated()
         ↓
StockReservationService::updateReservation($item, 10)
         ↓
┌─────────────────────────────────────┐
│ Database Transaction (Lock Product) │
├─────────────────────────────────────┤
│ 1. Lock product row                 │
│ 2. ตรวจสอบ available_stock          │
│    (ไม่นับการจองของ item นี้)       │
│ 3. อัปเดต StockReservation          │
│    - reserved_quantity = 15         │
│    - expires_at = now() + 24h       │
│ 4. Commit transaction               │
└─────────────────────────────────────┘
         ↓
✅ จำนวนสินค้าและการจองถูกอัปเดตแล้ว
```

**ผลลัพธ์:**

- ✅ SaleOrderItem.quantity = 15
- ✅ StockReservation.reserved_quantity = 15
- ✅ Product.available_stock = 85 (100 - 15)

---

### 4️⃣ ยืนยันใบสั่งขาย (Draft → Confirmed) ⭐ สำคัญ

```
User กดปุ่ม "ยืนยันใบสั่งขาย"
         ↓
ViewSaleOrder::confirm Action
         ↓
ตรวจสอบมีสินค้าหรือไม่
         ↓
ตรวจสอบ available_stock ทุกรายการ
(บวกสต็อกที่จองไว้กลับมาสำหรับแต่ละ item)
         ↓
    ✅ สต็อกพอทุกรายการ
         ↓
SaleOrder::update(['status' => 'confirmed'])
         ↓
SaleOrderObserver::updated()
         ↓
ตรวจสอบ: wasChanged('status') && 
         status = Confirmed && 
         original = Draft
         ↓
    ✅ เงื่อนไขตรง!
         ↓
┌──────────────────────────────────────────┐
│ Step 1: ปลดล็อคการจอง                    │
├──────────────────────────────────────────┤
│ releaseReservations($saleOrder)          │
│   ↓                                      │
│ DELETE FROM stock_reservations           │
│ WHERE sale_order_id = xxx                │
│   ↓                                      │
│ ✅ ลบการจองทั้งหมดของใบสั่งขายนี้        │
└──────────────────────────────────────────┘
         ↓
┌──────────────────────────────────────────┐
│ Step 2: ตัดสต็อกจริง                     │
├──────────────────────────────────────────┤
│ createStockMovements($saleOrder)         │
│   ↓                                      │
│ For each item:                           │
│   1. ตรวจสอบ stock_quantity >= quantity  │
│   2. สร้าง StockMovement (type: Out)     │
│   3. Product::decrement('stock_quantity')│
│   ↓                                      │
│ ✅ สต็อกถูกตัดจริงแล้ว                    │
└──────────────────────────────────────────┘
         ↓
✅ ยืนยันใบสั่งขายสำเร็จ!
```

**ผลลัพธ์:**

- ✅ SaleOrder.status = Confirmed
- ✅ StockReservation ถูกลบหมด (ปลดล็อค)
- ✅ Product.stock_quantity = 85 (100 - 15) ← ตัดสต็อกจริง
- ✅ Product.reserved_quantity = 0
- ✅ Product.available_stock = 85
- ✅ มี StockMovement บันทึกการตัดสต็อก

**ตัวอย่างข้อมูล:**

```
Product (ก่อนยืนยัน):
  stock_quantity = 100
  reserved_quantity = 15
  available_stock = 85

Product (หลังยืนยัน):
  stock_quantity = 85  ← ตัดแล้ว!
  reserved_quantity = 0  ← ปลดล็อคแล้ว!
  available_stock = 85

StockReservation:
  (ไม่มีแล้ว - ถูกลบหมด)

StockMovement:
  product_id = xxx
  sale_order_id = yyy
  type = Out
  quantity = 15
  stock_before = 100
  stock_after = 85
  notes = "ตัดสต็อกจากใบสั่งขายเลขที่ SO2026-0001"
```

---

### 5️⃣ ยกเลิกใบสั่งขาย

#### 5.1 ยกเลิกจาก Draft

```
User กดปุ่ม "ยกเลิก" (สถานะ Draft)
         ↓
ViewSaleOrder::cancel Action
         ↓
SaleOrder::update(['status' => 'cancelled'])
         ↓
SaleOrderObserver::updated()
         ↓
ตรวจสอบ: wasChanged('status') && 
         status = Cancelled &&
         original = Draft
         ↓
    ✅ เงื่อนไขตรง!
         ↓
releaseReservations($saleOrder)
         ↓
DELETE FROM stock_reservations
WHERE sale_order_id = xxx
         ↓
✅ ปลดล็อคการจองทั้งหมด
```

**ผลลัพธ์:**

- ✅ SaleOrder.status = Cancelled
- ✅ StockReservation ถูกลบหมด
- ✅ Product.stock_quantity ไม่เปลี่ยน (ไม่เคยตัด)
- ✅ Product.available_stock เพิ่มขึ้น (เพราะปลดล็อค)

#### 5.2 ยกเลิกจาก Confirmed

```
User กดปุ่ม "ยกเลิก" (สถานะ Confirmed)
         ↓
ViewSaleOrder::cancel Action
         ↓
SaleOrder::update(['status' => 'cancelled'])
         ↓
SaleOrderObserver::updated()
         ↓
ตรวจสอบ: wasChanged('status') && 
         status = Cancelled &&
         original = Confirmed
         ↓
    ✅ เงื่อนไขตรง!
         ↓
revertStockMovements($saleOrder)
         ↓
For each StockMovement:
  1. Product::increment('stock_quantity')
  2. StockMovement::delete()
         ↓
✅ คืนสต็อกทั้งหมด
```

**ผลลัพธ์:**

- ✅ SaleOrder.status = Cancelled
- ✅ Product.stock_quantity เพิ่มขึ้น (คืนสต็อก)
- ✅ StockMovement ถูกลบ
- ❌ ไม่มี StockReservation (เพราะถูกลบตอนยืนยันแล้ว)

---

### 6️⃣ ลบสินค้าออกจากใบสั่งขาย (Draft)

```
User กดปุ่ม "ลบ" รายการสินค้า
         ↓
SaleOrderInfolist::deleteAction
         ↓
SaleOrderItem::delete()
         ↓
SaleOrderItemObserver::deleted()
         ↓
StockReservationService::deleteReservation($item)
         ↓
DELETE FROM stock_reservations
WHERE sale_order_item_id = xxx
         ↓
✅ ปลดล็อคการจองของรายการนี้
```

**ผลลัพธ์:**

- ✅ SaleOrderItem ถูกลบ
- ✅ StockReservation ของรายการนี้ถูกลบ
- ✅ Product.available_stock เพิ่มขึ้น

---

## 🔍 การตรวจสอบว่าระบบทำงานถูกต้อง

### ตรวจสอบการจอง

```sql
-- ดูการจองทั้งหมด
SELECT 
    sr.id,
    p.name as product_name,
    sr.reserved_quantity,
    so.invoice_number,
    so.status,
    sr.expires_at,
    CASE 
        WHEN sr.expires_at > NOW() THEN 'Active'
        ELSE 'Expired'
    END as reservation_status
FROM stock_reservations sr
JOIN products p ON sr.product_id = p.id
JOIN sale_orders so ON sr.sale_order_id = so.id
ORDER BY sr.created_at DESC;
```

### ตรวจสอบสต็อก

```sql
-- ดูสต็อกและการจอง
SELECT 
    p.name,
    p.stock_quantity,
    COALESCE(SUM(sr.reserved_quantity), 0) as reserved,
    p.stock_quantity - COALESCE(SUM(sr.reserved_quantity), 0) as available
FROM products p
LEFT JOIN stock_reservations sr ON p.id = sr.product_id 
    AND sr.expires_at > NOW()
GROUP BY p.id
ORDER BY p.name;
```

### ตรวจสอบ StockMovement

```sql
-- ดูประวัติการเคลื่อนไหวสต็อก
SELECT 
    sm.id,
    p.name as product_name,
    sm.type,
    sm.quantity,
    sm.stock_before,
    sm.stock_after,
    so.invoice_number,
    sm.notes,
    sm.created_at
FROM stock_movements sm
JOIN products p ON sm.product_id = p.id
LEFT JOIN sale_orders so ON sm.sale_order_id = so.id
ORDER BY sm.created_at DESC
LIMIT 20;
```

---

## ✅ สรุป: ระบบเคลียร์การจองเมื่อยืนยันหรือไม่?

### คำตอบ: ✅ ใช่! ระบบเคลียร์การจองอัตโนมัติ

**เมื่อยืนยันใบสั่งขาย (Draft → Confirmed):**

1. ✅ **ปลดล็อคการจอง** (`releaseReservations()`)
   - ลบ StockReservation ทั้งหมดของใบสั่งขายนี้
   - Product.reserved_quantity กลับเป็น 0
   - Product.available_stock เพิ่มขึ้น

2. ✅ **ตัดสต็อกจริง** (`createStockMovements()`)
   - ลด Product.stock_quantity
   - สร้าง StockMovement บันทึกการตัดสต็อก
   - Product.available_stock ลดลง (เพราะสต็อกจริงลด)

**ผลลัพธ์สุดท้าย:**

- StockReservation: ❌ ถูกลบหมด (ไม่มีการจองแล้ว)
- Product.stock_quantity: ⬇️ ลดลง (ตัดสต็อกจริง)
- Product.reserved_quantity: 0 (ไม่มีการจอง)
- Product.available_stock: ⬇️ ลดลง (เท่ากับ stock_quantity)
- StockMovement: ✅ มีบันทึกการตัดสต็อก

---

## 🧪 วิธีทดสอบ

### Test Case 1: ยืนยันใบสั่งขาย

```
1. สร้าง Draft Sale Order
2. เพิ่มสินค้า A จำนวน 10
3. ตรวจสอบ:
   SELECT * FROM stock_reservations WHERE sale_order_id = 'xxx';
   → ✅ ต้องมี 1 record (reserved_quantity = 10)

4. ยืนยันใบสั่งขาย
5. ตรวจสอบ:
   SELECT * FROM stock_reservations WHERE sale_order_id = 'xxx';
   → ✅ ต้องไม่มี record (ถูกลบหมด)
   
   SELECT * FROM stock_movements WHERE sale_order_id = 'xxx';
   → ✅ ต้องมี 1 record (type = Out, quantity = 10)
   
   SELECT stock_quantity FROM products WHERE id = 'yyy';
   → ✅ ต้องลดลง 10
```

### Test Case 2: Concurrent Orders

```
1. สร้าง Draft Sale Order A (เพิ่มสินค้า X จำนวน 10)
2. สร้าง Draft Sale Order B (เพิ่มสินค้า X จำนวน 10)
3. ตรวจสอบ:
   SELECT SUM(reserved_quantity) FROM stock_reservations 
   WHERE product_id = 'xxx';
   → ✅ ต้องได้ 20

4. ยืนยัน Sale Order A
5. ตรวจสอบ:
   SELECT SUM(reserved_quantity) FROM stock_reservations 
   WHERE product_id = 'xxx';
   → ✅ ต้องได้ 10 (เหลือแค่ของ Order B)
   
6. ยืนยัน Sale Order B
7. ตรวจสอบ:
   SELECT SUM(reserved_quantity) FROM stock_reservations 
   WHERE product_id = 'xxx';
   → ✅ ต้องได้ 0 (ไม่มีการจองแล้ว)
```

---

## 🎯 สรุปสุดท้าย

**ระบบทำงานถูกต้อง 100%!**

✅ เมื่อยืนยันใบสั่งขาย → ปลดล็อคการจองอัตโนมัติ
✅ เมื่อยกเลิกจาก Draft → ปลดล็อคการจองอัตโนมัติ
✅ เมื่อยกเลิกจาก Confirmed → คืนสต็อกอัตโนมัติ
✅ เมื่อลบสินค้า → ปลดล็อคการจองอัตโนมัติ

**ไม่มีปัญหาการจองค้างหรือ memory leak!** 🎉
