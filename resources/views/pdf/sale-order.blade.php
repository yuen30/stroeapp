<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Print Layout - {{ $saleOrder->invoice_number }}</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap");

        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            font-family: "Sarabun", sans-serif;
            font-size: 11pt; /* ปรับขนาดฟอนต์ให้เข้ากับช่อง */
            margin: 0;
            padding: 0;
            background: #f0f0f0; /* สีพื้นหลังสำหรับดูในจอ */
        }

        .print-area {
            width: 210mm;
            height: 297mm;
            position: relative;
            background: white;
            margin: 0 auto;
            overflow: hidden;
        }

        /* สำหรับซ่อนพื้นหลังตอนสั่งพิมพ์จริง */
        @media print {
            body { background: none; }
            .print-area { margin: 0; box-shadow: none; }
        }

        .field {
            position: absolute;
            display: block;
            line-height: 1;
        }

        /* === ส่วนหัวและข้อมูลลูกค้า (Header) === */
        .pos-invoice-no   { top: 48mm;  left: 165mm; font-weight: bold; } [cite: 18]
        .pos-invoice-date { top: 54mm;  left: 165mm; } [cite: 19]
        .pos-salesman     { top: 78mm;  left: 165mm; } [cite: 33]

        .pos-cust-name    { top: 62mm;  left: 35mm; } [cite: 10]
        .pos-cust-address { top: 68mm;  left: 35mm; width: 100mm; line-height: 1.2; } [cite: 12]
        .pos-cust-taxid   { top: 78mm;  left: 65mm; letter-spacing: 2px; } [cite: 15]

        /* ติ๊กถูกช่อง สำนักงานใหญ่ / สาขา */
        .pos-check-hq     { top: 84mm;  left: 33mm; font-size: 14pt; } [cite: 27]
        .pos-check-branch { top: 84mm;  left: 55mm; font-size: 14pt; } [cite: 31]
        .pos-branch-no    { top: 84mm;  left: 75mm; }

        /* === ส่วนรายการสินค้า (Items) === */
        /* วนลูปรายการสินค้า โดยกำหนดระยะบรรทัด (Line Height) ให้ตรงกับช่อง */
        .items-container {
            position: absolute;
            top: 105mm; /* จุดเริ่มบรรทัดแรก */
            left: 0;
            width: 100%;
        }

        .item-row {
            position: relative;
            height: 8.5mm; /* ระยะห่างระหว่างบรรทัด (สำคัญมาก: ต้องวัดให้ตรงกับกระดาษจริง) */
            display: flex;
            align-items: flex-start;
        }

        .col-idx   { position: absolute; left: 15mm; width: 10mm; text-align: center; }
        .col-desc  { position: absolute; left: 30mm; width: 90mm; }
        .col-qty   { position: absolute; left: 125mm; width: 15mm; text-align: center; }
        .col-price { position: absolute; left: 145mm; width: 25mm; text-align: right; }
        .col-amt   { position: absolute; left: 175mm; width: 25mm; text-align: right; }

        /* === ส่วนสรุปยอดเงิน (Footer) === */
        .pos-total-text { top: 228mm; left: 175mm; text-align: right; width: 25mm; } [cite: 34]
        .pos-vat-text   { top: 236mm; left: 175mm; text-align: right; width: 25mm; } [cite: 34]
        .pos-grand-total{ top: 248mm; left: 175mm; text-align: right; width: 25mm; font-weight: bold; font-size: 12pt; } [cite: 37, 38]

    </style>
</head>
<body>
    <div class="print-area">
        <span class="field pos-invoice-no">{{ $saleOrder->invoice_number }}</span>
        <span class="field pos-invoice-date">{{ $saleOrder->order_date->format('d/m/Y') }}</span>
        <span class="field pos-salesman">{{ $saleOrder->creator->name }}</span>

        <span class="field pos-cust-name">{{ $saleOrder->customer->name }}</span>
        <span class="field pos-cust-address">
            {{ $saleOrder->customer->address_0 }} {{ $saleOrder->customer->province }}
        </span>
        <span class="field pos-cust-taxid">{{ $saleOrder->customer->tax_id }}</span>

        @if($saleOrder->customer->is_head_office)
            <span class="field pos-check-hq">✔</span>
        @else
            <span class="field pos-check-branch">✔</span>
            <span class="field pos-branch-no">{{ $saleOrder->customer->branch_no }}</span>
        @endif

        <div class="items-container">
            @foreach($saleOrder->items as $index => $item)
            <div class="item-row">
                <span class="col-idx">{{ $index + 1 }}</span>
                <span class="col-desc">
                    {{ $item->product->name }}
                    @if($item->product->code) ({{ $item->product->code }}) @endif
                </span>
                <span class="col-qty">{{ number_format($item->quantity) }}</span>
                <span class="col-price">{{ number_format($item->unit_price, 2) }}</span>
                <span class="col-amt">{{ number_format($item->total_price, 2) }}</span>
            </div>
            @endforeach
        </div>

        <span class="field pos-total-text">{{ number_format($saleOrder->subtotal, 2) }}</span>
        <span class="field pos-vat-text">{{ number_format($saleOrder->vat_amount, 2) }}</span>
        <span class="field pos-grand-total">{{ number_format($saleOrder->total_amount, 2) }}</span>
    </div>
</body>
</html>
