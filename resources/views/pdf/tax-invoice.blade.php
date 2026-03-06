<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบกำกับภาษี {{ $taxInvoice->tax_invoice_number }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew Bold.ttf') }}") format('truetype');
        }
        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 16pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24pt;
            font-weight: bold;
            margin: 0;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-info {
            margin-bottom: 20px;
        }
        .customer-info {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            width: 50%;
            margin-left: auto;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 18pt;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>ใบกำกับภาษี / TAX INVOICE</h1>
        <div style="font-size: 12pt; color: #666;">ต้นฉบับ / Original</div>
    </div>

    <!-- Company Info -->
    <div class="company-info">
        <strong style="font-size: 20pt;">{{ $taxInvoice->company->name }}</strong><br>
        @if($taxInvoice->branch)
            สาขา: {{ $taxInvoice->branch->name }}<br>
        @endif
        {{ $taxInvoice->company->address }}<br>
        เลขประจำตัวผู้เสียภาษี: {{ $taxInvoice->company->tax_id }}<br>
        โทร: {{ $taxInvoice->company->phone }} | อีเมล: {{ $taxInvoice->company->email }}
    </div>

    <hr>

    <!-- Invoice Info -->
    <div class="invoice-info">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;">
                    <strong>เลขที่ใบกำกับภาษี:</strong> {{ $taxInvoice->tax_invoice_number }}
                </td>
                <td style="border: none; width: 50%;">
                    <strong>วันที่:</strong> {{ $taxInvoice->document_date->format('d/m/Y') }}
                </td>
            </tr>
            @if($taxInvoice->saleOrder)
            <tr style="border: none;">
                <td style="border: none;" colspan="2">
                    <strong>อ้างอิงใบสั่งขาย:</strong> {{ $taxInvoice->saleOrder->invoice_number }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Customer Info -->
    <div class="customer-info">
        <strong>ลูกค้า / Customer:</strong><br>
        <strong style="font-size: 18pt;">{{ $taxInvoice->customer_name }}</strong><br>
        @if($taxInvoice->customer_tax_id)
            เลขประจำตัวผู้เสียภาษี: {{ $taxInvoice->customer_tax_id }}<br>
        @endif
        ที่อยู่: {{ $taxInvoice->customer_address_line1 }}
        @if($taxInvoice->customer_address_line2)
            {{ $taxInvoice->customer_address_line2 }}
        @endif
        @if($taxInvoice->customer_amphoe)
            อ.{{ $taxInvoice->customer_amphoe }}
        @endif
        @if($taxInvoice->customer_province)
            จ.{{ $taxInvoice->customer_province }}
        @endif
        @if($taxInvoice->customer_postal_code)
            {{ $taxInvoice->customer_postal_code }}
        @endif
        <br>
        @if($taxInvoice->customer_is_head_office)
            <strong>สำนักงานใหญ่</strong>
        @else
            <strong>สาขา:</strong> {{ $taxInvoice->customer_branch_no ?? '-' }}
        @endif
    </div>

    <!-- Items Table -->
    @if($taxInvoice->saleOrder && $taxInvoice->saleOrder->items->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 45%;">รายการสินค้า</th>
                <th style="width: 10%;">จำนวน</th>
                <th style="width: 10%;">หน่วย</th>
                <th style="width: 15%;">ราคา/หน่วย</th>
                <th style="width: 15%;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taxInvoice->saleOrder->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ number_format($item->quantity, 0) }}</td>
                <td class="text-center">{{ $item->product->unit->name ?? 'หน่วย' }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Total Section -->
    <div class="total-section">
        <div class="total-row">
            <span>ยอดรวมก่อนหักส่วนลด:</span>
            <span>{{ number_format($taxInvoice->subtotal, 2) }} ฿</span>
        </div>
        @if($taxInvoice->discount_amount > 0)
        <div class="total-row">
            <span>ส่วนลด:</span>
            <span>{{ number_format($taxInvoice->discount_amount, 2) }} ฿</span>
        </div>
        @endif
        <div class="total-row">
            <span>ยอดหลังหักส่วนลด:</span>
            <span>{{ number_format($taxInvoice->subtotal - $taxInvoice->discount_amount, 2) }} ฿</span>
        </div>
        <div class="total-row">
            <span>ภาษีมูลค่าเพิ่ม {{ number_format($taxInvoice->vat_rate, 0) }}%:</span>
            <span>{{ number_format($taxInvoice->vat_amount, 2) }} ฿</span>
        </div>
        <div class="total-row grand-total">
            <span>จำนวนเงินรวมทั้งสิ้น:</span>
            <span>{{ number_format($taxInvoice->total_amount, 2) }} ฿</span>
        </div>
    </div>

    @if($taxInvoice->notes)
    <div style="margin-top: 20px;">
        <strong>หมายเหตุ:</strong> {{ $taxInvoice->notes }}
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                ผู้รับสินค้า/บริการ<br>
                วันที่: _______________
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                ผู้มีอำนาจลงนาม<br>
                {{ $taxInvoice->creator->name ?? '' }}
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 30px; font-size: 12pt; color: #666;">
        พิมพ์เมื่อ: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
