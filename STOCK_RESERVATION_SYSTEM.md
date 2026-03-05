# 🔒 ระบบจองสต็อก (Stock Reservation System)

## 📌 ภาพรวม

ระบบจองสต็อกถูกออกแบบมาเพื่อป้องกันปัญหาการขายสินค้าเกินสต็อกจริง โดยเฉพาะในกรณีที่มีหลายคนสร้างใบสั่งขาย (Draft) พร้อมกันในเวลาเดียวกัน

## 🎯 วัตถุประสงค์

1. **ป้องกัน Overselling**: ป้องกันการขายสินค้าเกินจำนวนที่มีในสต็อก
2. **จัดการ Concurrent Orders**: รองรับการสร้างใบสั่งขายหลายใบพร้อมกัน
3. **Temporary Hold**: จองสต็อกชั่วคราวสำหรับใบสั่งขายที่ยังเป็น Draft
4. **Auto Release**: ปลดล็อคการจองอัตโนมัติเมื่อหมดอายุหรือยกเลิก

## 🔄 การทำงานของระบบ

### 1. เมื่อเพิ่มสินค้าใน Draft Sale Order

```
User เพิ่มสินค้า → SaleOrderItem created
                 ↓
         SaleOrderItemObserver::created()
                 ↓
    StockReservationService::createReservation()
                 ↓
         ตรวจสอบ available_stock
                 ↓
    ✅ พอ → สร้าง StockReservation (จอง 24 ชม.)
    ❌ ไม่พอ → โยน InsufficientStockException
```

### 2. เมื่อแก้ไขจำนวนสินค้า

```
User แก้ไขจำนวน → SaleOrderItem updated
                  ↓
          SaleOrderItemObserver::updated()
                  ↓
     StockReservationService::updateReservation()
                  ↓
          ตรวจสอบ available_stock
                  ↓
     ✅ พอ → อัปเดต StockReservation
     ❌ ไม่พอ → โยน InsufficientStockException
```

### 3. เมื่อลบสินค้า

```
User ลบสินค้า → SaleOrderItem deleted
               ↓
       SaleOrderItemObserver::deleted()
               ↓
  StockReservationService::deleteReservation()
               ↓
       ลบ StockReservation (ปลดล็อค)
```

### 4. เมื่อยืนยันใบสั่งขาย (Draft → Confirmed)

```
User ยืนยัน → SaleOrder status = Confirmed
             ↓
     SaleOrderObserver::updated()
             ↓
     ปลดล็อคการจอง (releaseReservations)
             ↓
     ตัดสต็อกจริง (createStockMovements)
             ↓
     สร้าง StockMovement (type: Out)
```

### 5. เมื่อยกเลิกใบสั่งขาย

**จาก Draft:**

```
User ยกเลิก → SaleOrder status = Cancelled
             ↓
     SaleOrderObserver::updated()
             ↓
     ปลดล็อคการจอง (releaseReservations)
```

**จาก Confirmed:**

```
User ยกเลิก → SaleOrder status = Cancelled
             ↓
     SaleOrderObserver::updated()
             ↓
     คืนสต็อก (revertStockMovements)
             ↓
     ลบ StockMovement
```

## 📊 โครงสร้างข้อมูล

### StockReservation Model

```php
{
    'id': 'ulid',
    'product_id': 'ulid',
    'sale_order_id': 'ulid',
    'sale_order_item_id': 'ulid',
    'reserved_quantity': 'integer',
    'expires_at': 'timestamp',  // หมดอายุหลัง 24 ชม.
    'created_at': 'timestamp',
    'updated_at': 'timestamp'
}
```

### Product Accessors

```php
// สต็อกที่ถูกจอง (ยังไม่หมดอายุ)
$product->reserved_quantity

// สต็อกที่พร้อมใช้งาน = stock_quantity - reserved_quantity
$product->available_stock
```

## 🛡️ การป้องกัน Race Condition

ระบบใช้ Database Lock เพื่อป้องกัน Race Condition:

```php
$product = Product::where('id', $productId)
    ->lockForUpdate()  // Lock row จนกว่า transaction จะเสร็จ
    ->first();
```

**ตัวอย่างสถานการณ์:**

```
Time  | User A                    | User B
------|---------------------------|---------------------------
10:00 | เพิ่มสินค้า A จำนวน 10   |
      | → Lock product row        |
      | → ตรวจสอบ available: 10   |
      | → จอง 10                  |
      | → Unlock                  |
10:01 |                           | เพิ่มสินค้า A จำนวน 10
      |                           | → Lock product row (รอ A unlock)
      |                           | → ตรวจสอบ available: 0
      |                           | → ❌ สต็อกไม่พอ!
```

## ⏰ การหมดอายุของการจอง

- การจองมีอายุ **24 ชั่วโมง**
- หมดอายุอัตโนมัติเมื่อ `expires_at < now()`
- ลบการจองที่หมดอายุด้วย Command:

```bash
php artisan reservations:cleanup
```

**แนะนำ:** ตั้ง Cron Job ให้รันทุก 1 ชั่วโมง

```bash
# crontab -e
0 * * * * cd /path/to/project && php artisan reservations:cleanup
```

## 🔍 การตรวจสอบสต็อก

### ❌ วิธีเดิม (ไม่ปลอดภัย)

```php
if ($product->stock_quantity < $requestedQuantity) {
    // ไม่ได้คำนึงถึงสต็อกที่ถูกจองแล้ว!
}
```

### ✅ วิธีใหม่ (ปลอดภัย)

```php
$availableStock = $product->available_stock;

// ถ้าแก้ไขรายการที่มีอยู่ ต้องบวกสต็อกที่จองไว้กลับมา
if ($existingItem) {
    $reservation = StockReservation::where('sale_order_item_id', $existingItem->id)
        ->where('expires_at', '>', now())
        ->first();
    if ($reservation) {
        $availableStock += $reservation->reserved_quantity;
    }
}

if ($availableStock < $requestedQuantity) {
    throw new InsufficientStockException();
}
```

## 📝 ตัวอย่างการใช้งาน

### ตรวจสอบสต็อกพร้อมใช้งาน

```php
$product = Product::find($productId);

echo "สต็อกทั้งหมด: {$product->stock_quantity}\n";
echo "ถูกจอง: {$product->reserved_quantity}\n";
echo "พร้อมใช้: {$product->available_stock}\n";
```

### สร้างการจองด้วยตนเอง

```php
use App\Services\StockReservationService;

$service = app(StockReservationService::class);

try {
    $reservation = $service->createReservation($saleOrderItem);
    echo "จองสำเร็จ: {$reservation->reserved_quantity} หน่วย\n";
} catch (InsufficientStockException $e) {
    echo "สต็อกไม่พอ: {$e->getMessage()}\n";
}
```

### ปลดล็อคการจองทั้งหมดของใบสั่งขาย

```php
$service->releaseReservations($saleOrder);
```

### ลบการจองที่หมดอายุ

```php
$count = $service->cleanupExpiredReservations();
echo "ลบการจองที่หมดอายุ: {$count} รายการ\n";
```

## 🧪 การทดสอบ

ระบบมี Unit Tests ครบถ้วนใน `tests/Unit/StockReservationServiceTest.php`:

```bash
php artisan test --filter=StockReservationServiceTest
```

**Test Cases:**

- ✅ สร้างการจองสำเร็จ
- ✅ สร้างการจองล้มเหลวเมื่อสต็อกไม่พอ
- ✅ อัปเดตการจองเมื่อเพิ่มจำนวน
- ✅ อัปเดตการจองเมื่อลดจำนวน
- ✅ อัปเดตล้มเหลวเมื่อสต็อกไม่พอ
- ✅ ลบการจอง
- ✅ ปลดล็อคการจองทั้งหมด
- ✅ คำนวณ available stock ถูกต้อง
- ✅ ลบการจองที่หมดอายุ
- ✅ ป้องกัน Race Condition ด้วย Lock

## 🚨 ข้อควรระวัง

1. **ต้องใช้ available_stock เสมอ** เมื่อตรวจสอบสต็อก ไม่ใช่ stock_quantity
2. **Transaction Timeout**: การ Lock อาจทำให้ transaction ช้าลง ควรทำให้เร็วที่สุด
3. **Expired Reservations**: ต้องตั้ง Cron Job ลบการจองที่หมดอายุ
4. **แก้ไขรายการที่มีอยู่**: ต้องบวกสต็อกที่จองไว้กลับมาก่อนตรวจสอบ

## 📈 Performance Considerations

- **Indexes**: มี index บน `product_id`, `expires_at`, `sale_order_id`
- **Query Optimization**: ใช้ `sum()` แทน `get()->sum()` เพื่อคำนวณใน Database
- **Caching**: พิจารณา cache `reserved_quantity` ถ้ามีการเรียกบ่อย

## 🔧 Maintenance

### ตรวจสอบการจองที่ค้างอยู่

```sql
SELECT 
    p.name,
    sr.reserved_quantity,
    sr.expires_at,
    so.invoice_number
FROM stock_reservations sr
JOIN products p ON sr.product_id = p.id
JOIN sale_orders so ON sr.sale_order_id = so.id
WHERE sr.expires_at > NOW()
ORDER BY sr.expires_at;
```

### ตรวจสอบสินค้าที่ถูกจองมาก

```sql
SELECT 
    p.name,
    p.stock_quantity,
    SUM(sr.reserved_quantity) as total_reserved,
    p.stock_quantity - SUM(sr.reserved_quantity) as available
FROM products p
LEFT JOIN stock_reservations sr ON p.id = sr.product_id 
    AND sr.expires_at > NOW()
GROUP BY p.id
HAVING total_reserved > 0
ORDER BY total_reserved DESC;
```

## 📚 เอกสารเพิ่มเติม

- [StockReservationService.php](app/Services/StockReservationService.php)
- [SaleOrderItemObserver.php](app/Observers/SaleOrderItemObserver.php)
- [SaleOrderObserver.php](app/Observers/SaleOrderObserver.php)
- [Product Model](app/Models/Product.php)
- [StockReservation Model](app/Models/StockReservation.php)

---

**หมายเหตุ:** ระบบนี้ใช้งานได้แล้วและถูก integrate เข้ากับ SaleOrder Resource ทั้งหมด
