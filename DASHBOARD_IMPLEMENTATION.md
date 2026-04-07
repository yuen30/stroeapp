# 📊 Dashboard Implementation

## ภาพรวม

สร้าง Dashboard แบบครบวงจรสำหรับระบบ Enterprise Auto-Parts Inventory System พร้อมสถิติ, กราฟ, และตารางข้อมูลสำคัญ

## ✨ Widgets ที่สร้าง (9 Widgets)

### 1. StatsOverviewWidget - สถิติภาพรวม (6 Cards)

**ตำแหน่ง:** บรรทัดบนสุด  
**ไฟล์:** `app/Filament/Widgets/StatsOverviewWidget.php`

**Cards:**

- **ยอดขายวันนี้**
    - แสดงยอดขายวันนี้และเดือนนี้
    - มี mini chart 7 วันย้อนหลัง
    - แสดงเปอร์เซ็นต์เปลี่ยนแปลงเทียบเดือนที่แล้ว
- **สินค้าใกล้หมด**
    - นับสินค้าที่สต็อก < 10 ชิ้น
    - แสดงจำนวนสินค้าหมดสต็อก
    - สีเตือน: เหลือง (> 10), เขียว (≤ 10)

- **สต็อกที่ถูกจอง**
    - นับสต็อกที่ถูกจองจาก Draft Sale Orders
    - แสดงเป็นหน่วย
    - สีฟ้า (info)

- **ใบสั่งซื้อรอดำเนินการ**
    - นับ PO ที่เป็น Draft หรือ Confirmed
    - สีเตือน: เหลือง (> 5), เขียว (≤ 5)

- **ยอดค้างชำระ**
    - รวมยอดค้างชำระจาก Sale Orders
    - สีเตือน: แดง (> 100k), เหลือง (≤ 100k)

- **ลูกค้าใหม่**
    - นับลูกค้าใหม่เดือนนี้
    - สีเขียว (success)

### 2. SalesChartWidget - กราฟยอดขาย

**ตำแหน่ง:** แถวที่ 2 (full width)  
**ไฟล์:** `app/Filament/Widgets/SalesChartWidget.php`

**คุณสมบัติ:**

- Line Chart แสดงยอดขาย 30 วันย้อนหลัง
- แกน X: วันที่ (dd MMM)
- แกน Y: ยอดเงิน (฿)
- สีฟ้า พร้อม gradient fill

### 3. TopProductsChartWidget - สินค้าขายดี

**ตำแหน่ง:** แถวที่ 3 (ซ้าย)  
**ไฟล์:** `app/Filament/Widgets/TopProductsChartWidget.php`

**คุณสมบัติ:**

- Horizontal Bar Chart
- แสดง Top 10 สินค้าขายดีเดือนนี้
- เรียงตามจำนวนที่ขาย
- สีสันสดใส 10 สี

### 4. StockStatusChartWidget - สต็อกตามสถานะ

**ตำแหน่ง:** แถวที่ 3 (ขวา)  
**ไฟล์:** `app/Filament/Widgets/StockStatusChartWidget.php`

**คุณสมบัติ:**

- Pie Chart
- แบ่งเป็น 3 กลุ่ม:
    - ปกติ (≥10) - สีเขียว
    - ใกล้หมด (<10) - สีเหลือง
    - หมดสต็อก (0) - สีแดง

### 5. PaymentStatusChartWidget - สถานะการชำระเงิน

**ตำแหน่ง:** แถวที่ 4 (ซ้าย)  
**ไฟล์:** `app/Filament/Widgets/PaymentStatusChartWidget.php`

**คุณสมบัติ:**

- Donut Chart
- แสดงมูลค่าตามสถานะ:
    - ชำระแล้ว - สีเขียว
    - ชำระบางส่วน - สีเหลือง
    - ค้างชำระ - สีแดง

### 6. RecentSaleOrdersWidget - ใบส่งสินค้าล่าสุด

**ตำแหน่ง:** แถวที่ 5 (full width)  
**ไฟล์:** `app/Filament/Widgets/RecentSaleOrdersWidget.php`

**คุณสมบัติ:**

- Table แสดง 10 รายการล่าสุด
- Columns: เลขที่, ลูกค้า, ยอดรวม, สถานะ, การชำระเงิน, วันที่สร้าง
- Action: ปุ่ม "ดู" ไปหน้า View Sale Order
- แสดงเวลาแบบ relative (e.g., "2 hours ago")

### 7. LowStockProductsWidget - สินค้าใกล้หมด

**ตำแหน่ง:** แถวที่ 6 (ซ้าย)  
**ไฟล์:** `app/Filament/Widgets/LowStockProductsWidget.php`

**คุณสมบัติ:**

- Table แสดง 10 รายการที่สต็อกต่ำสุด
- Columns: ชื่อสินค้า, สต็อก, ถูกจอง, พร้อมใช้
- Badge สี:
    - แดง: สต็อก 0 หรือ < 5
    - เหลือง: สต็อก < 10
    - เขียว: สต็อก ≥ 10
- Action: ปุ่ม "ดู" ไปหน้า View Product

### 8. PendingPurchaseOrdersWidget - ใบสั่งซื้อรอดำเนินการ

**ตำแหน่ง:** แถวที่ 6 (ขวา)  
**ไฟล์:** `app/Filament/Widgets/PendingPurchaseOrdersWidget.php`

**คุณสมบัติ:**

- Table แสดง 10 รายการที่รอดำเนินการ
- Filter: Draft และ Confirmed
- Columns: เลขที่, ซัพพลายเออร์, ยอดรวม, สถานะ, วันที่
- Action: ปุ่ม "ดู" ไปหน้า View Purchase Order

### 9. OutstandingPaymentsWidget - ลูกค้าค้างชำระ

**ตำแหน่ง:** แถวที่ 7 (full width)  
**ไฟล์:** `app/Filament/Widgets/OutstandingPaymentsWidget.php`

**คุณสมบัติ:**

- Table แสดง 10 รายการที่ค้างชำระ
- เรียงตามวันที่เก่าสุด (ค้างนานสุด)
- Columns: เลขที่, ลูกค้า, ยอดค้าง, สถานะ, ค้างมาแล้ว, จำนวนวัน
- Badge จำนวนวัน:
    - แดง: > 30 วัน
    - เหลือง: > 15 วัน
    - ฟ้า: ≤ 15 วัน
- Action: ปุ่ม "ดู" ไปหน้า View Sale Order

## 📁 โครงสร้างไฟล์

```
app/Filament/Widgets/
├── StatsOverviewWidget.php           # สถิติภาพรวม 6 cards
├── SalesChartWidget.php              # กราฟยอดขาย 30 วัน
├── TopProductsChartWidget.php        # สินค้าขายดี Top 10
├── StockStatusChartWidget.php        # Pie Chart สต็อก
├── PaymentStatusChartWidget.php      # Donut Chart การชำระเงิน
├── RecentSaleOrdersWidget.php        # ตารางใบส่งสินค้าล่าสุด
├── LowStockProductsWidget.php        # ตารางสินค้าใกล้หมด
├── PendingPurchaseOrdersWidget.php   # ตารางใบสั่งซื้อรอดำเนินการ
└── OutstandingPaymentsWidget.php     # ตารางลูกค้าค้างชำระ
```

## 🎨 Layout Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│  📊 Dashboard                                               │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌────┐│
│  │ยอดขาย│  │สินค้า│  │สต็อก │  │ใบสั่ง│  │ยอดค้าง│  │ลูกค้า││
│  │วันนี้ │  │ใกล้หมด│  │ถูกจอง│  │ซื้อ  │  │ชำระ  │  │ใหม่ ││
│  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘  └────┘│
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ 📈 ยอดขาย 30 วันย้อนหลัง (Line Chart)              │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌────────────────────────┐  ┌────────────────────────┐   │
│  │ 🏆 สินค้าขายดี Top 10 │  │ 📦 สต็อกตามสถานะ      │   │
│  │ (Bar Chart)            │  │ (Pie Chart)            │   │
│  └────────────────────────┘  └────────────────────────┘   │
│                                                              │
│  ┌────────────────────────┐  ┌────────────────────────┐   │
│  │ 💰 สถานะการชำระเงิน   │  │                        │   │
│  │ (Donut Chart)          │  │                        │   │
│  └────────────────────────┘  └────────────────────────┘   │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ 📋 ใบส่งสินค้าล่าสุด (Table - 10 รายการ)            │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌────────────────────────┐  ┌────────────────────────┐   │
│  │ ⚠️ สินค้าใกล้หมด      │  │ 📝 ใบสั่งซื้อรอดำเนิน│   │
│  │ (Table - 10 รายการ)   │  │ (Table - 10 รายการ)   │   │
│  └────────────────────────┘  └────────────────────────┘   │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ 💸 ลูกค้าค้างชำระ (Table - 10 รายการ)             │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## 🔧 การติดตั้ง

### 1. ลงทะเบียน Widgets

Widgets ถูกลงทะเบียนใน `app/Providers/Filament/StorePanelProvider.php`:

```php
->widgets([
    // Dashboard Widgets
    \App\Filament\Widgets\StatsOverviewWidget::class,
    \App\Filament\Widgets\SalesChartWidget::class,
    \App\Filament\Widgets\TopProductsChartWidget::class,
    \App\Filament\Widgets\StockStatusChartWidget::class,
    \App\Filament\Widgets\PaymentStatusChartWidget::class,
    \App\Filament\Widgets\RecentSaleOrdersWidget::class,
    \App\Filament\Widgets\LowStockProductsWidget::php,
    \App\Filament\Widgets\PendingPurchaseOrdersWidget::class,
    \App\Filament\Widgets\OutstandingPaymentsWidget::class,
])
```

### 2. Widget Sorting

Widgets ถูกเรียงลำดับด้วย `$sort` property:

- Sort 1: StatsOverviewWidget
- Sort 2: SalesChartWidget
- Sort 3: TopProductsChartWidget
- Sort 4: StockStatusChartWidget
- Sort 5: PaymentStatusChartWidget
- Sort 6: RecentSaleOrdersWidget
- Sort 7: LowStockProductsWidget
- Sort 8: PendingPurchaseOrdersWidget
- Sort 9: OutstandingPaymentsWidget

### 3. Column Span

- `'full'`: ใช้ความกว้างเต็ม (SalesChartWidget, RecentSaleOrdersWidget, OutstandingPaymentsWidget)
- `1`: ใช้ครึ่งความกว้าง (TopProductsChartWidget, StockStatusChartWidget, etc.)

## 📊 ข้อมูลที่แสดง

### สถิติที่คำนวณ

1. **ยอดขาย**
    - วันนี้: `SaleOrder::whereDate('created_at', today())->sum('total_amount')`
    - เดือนนี้: `whereMonth()->whereYear()->sum('total_amount')`
    - เปอร์เซ็นต์เปลี่ยนแปลง: `(thisMonth - lastMonth) / lastMonth * 100`

2. **สต็อก**
    - ใกล้หมด: `Product::where('stock_quantity', '<', 10)->count()`
    - หมดสต็อก: `Product::where('stock_quantity', 0)->count()`
    - ถูกจอง: `StockReservation::where('expires_at', '>', now())->sum('reserved_quantity')`

3. **ใบสั่งซื้อ**
    - รอดำเนินการ: `PurchaseOrder::whereIn('status', [Draft, Confirmed])->count()`

4. **การเงิน**
    - ค้างชำระ: `SaleOrder::whereIn('payment_status', [Unpaid, Partial])->sum('total_amount')`

5. **ลูกค้า**
    - ใหม่เดือนนี้: `Customer::whereMonth('created_at', now()->month)->count()`

## 🎯 คุณสมบัติพิเศษ

### 1. Real-time Data

- ข้อมูลดึงจาก Database แบบ real-time
- ไม่มี caching (เหมาะสำหรับข้อมูลที่เปลี่ยนแปลงบ่อย)

### 2. Interactive Charts

- Hover แสดงรายละเอียด
- Legend คลิกได้
- Responsive design

### 3. Quick Actions

- ปุ่ม "ดู" ในทุก Table Widget
- Link ไปหน้า View ของ Resource ที่เกี่ยวข้อง

### 4. Color Coding

- เขียว: ปกติ/ดี
- เหลือง: เตือน/ระวัง
- แดง: อันตราย/ด่วน
- ฟ้า: ข้อมูล

### 5. Badge & Icons

- ใช้ Heroicons
- Badge แสดงสถานะ
- Tooltip แสดงข้อมูลเพิ่มเติม

## 🔍 การใช้งาน

### เข้าถึง Dashboard

```
URL: /store
หรือ: /store/dashboard
```

### Refresh Data

- Dashboard จะ refresh อัตโนมัติเมื่อโหลดหน้าใหม่
- ไม่มี auto-refresh (ต้อง refresh browser)

### Filter & Search

- Table Widgets รองรับ search
- ไม่มี filter (แสดงข้อมูลตามเงื่อนไขที่กำหนด)

## 🚀 Performance

### Optimization

1. **Limit Results**: ทุก Table Widget จำกัดที่ 10 รายการ
2. **Eager Loading**: ใช้ `with()` โหลด relationships
3. **Indexed Columns**: ใช้ columns ที่มี index (created_at, status, etc.)
4. **Pagination**: ปิด pagination (`paginated(false)`)

### Query Optimization

```php
// ✅ ดี - ใช้ sum() ใน Database
SaleOrder::sum('total_amount')

// ❌ ไม่ดี - ดึงข้อมูลทั้งหมดมาคำนวณ
SaleOrder::get()->sum('total_amount')
```

## 📝 Customization

### เปลี่ยนจำนวนรายการ

```php
// ใน Widget
->limit(10)  // เปลี่ยนเป็น 20
```

### เปลี่ยนช่วงเวลา

```php
// ยอดขาย 60 วัน
for ($i = 59; $i >= 0; $i--) {
    $date = now()->subDays($i);
    // ...
}
```

### เพิ่ม Filter

```php
->filters([
    SelectFilter::make('status')
        ->options(OrderStatus::class),
])
```

### เปลี่ยนสี

```php
->color(fn($state) => match (true) {
    $state > 100 => 'danger',
    $state > 50 => 'warning',
    default => 'success',
})
```

## 🎉 สรุป

Dashboard ที่สร้างขึ้นมีความสมบูรณ์และพร้อมใช้งาน:

✅ 9 Widgets ครบถ้วน  
✅ สถิติสำคัญครบทุกด้าน  
✅ กราฟสวยงามและ interactive  
✅ ตารางข้อมูลพร้อม quick actions  
✅ Color coding ชัดเจน  
✅ Responsive design  
✅ Performance optimized

**Dashboard พร้อมใช้งานแล้ว!** 🚀

## 📚 เอกสารเพิ่มเติม

- [Filament Widgets Documentation](https://filamentphp.com/docs/3.x/widgets)
- [Chart.js Documentation](https://www.chartjs.org/docs/)
- [README.md](README.md) - เอกสารโปรเจคหลัก
