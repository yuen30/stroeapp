<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบสั่งขาย {{ $saleOrder->invoice_number }}</title>
    <style>
        /* ==========================================================
           1. Page Setup & Font Declarations (Task 1.1)
           Requirements: 9.1, 9.2, 10.3, 10.4
           ========================================================== */
        @page {
            size: A4;
            margin: 10mm;
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/thsarabunnew_normal_09b35a9bdc6f8bdd26aa7a3c7f12196e.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/thsarabunnew_bold_c1d4617ae513614ba8614f8f4e2b2d96.ttf') }}") format('truetype');
        }

        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 13pt;
            line-height: 1.1;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* ==========================================================
           2. Theme Colors & Utility Classes (Task 1.2)
           Primary border: #d32f2f | Highlight bg: #fce4ec
           Requirements: 9.4, 9.5, 10.1, 10.2, 10.5
           ========================================================== */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .clear { clear: both; }

        /* ==========================================================
           3. Page Container
           ========================================================== */
        .page-container {
            padding: 0;
        }

        /* ==========================================================
           4. Header Section — Company Info + Document Title
           ========================================================== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .logo-section {
            width: 55%;
            vertical-align: top;
        }

        .logo-text {
            color: #d32f2f;
            font-size: 22pt;
            font-weight: bold;
            margin: 0;
            display: inline-block;
        }

        .company-name {
            color: #333;
            font-size: 16pt;
            font-weight: bold;
            display: block;
            margin-top: -5px;
        }

        .company-details {
            font-size: 10pt;
            color: #555;
            line-height: 1.1;
        }

        .doc-title-section {
            width: 45%;
            text-align: right;
            vertical-align: top;
        }

        .doc-title-box {
            border: 2px solid #d32f2f;
            border-radius: 8px;
            padding: 6px 12px;
            display: inline-block;
            text-align: center;
            background-color: #fff;
        }

        .doc-title-main {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
        }

        .doc-title-sub {
            font-size: 9pt;
            color: #555;
        }

        /* ==========================================================
           5. Info Boxes — Customer + Document
           ========================================================== */
        .info-container {
            width: 100%;
            margin-bottom: 5px;
        }

        .customer-box {
            width: 63%;
            border: 1px solid #d32f2f;
            border-radius: 6px;
            padding: 6px;
            float: left;
            height: 90px;
        }

        .document-box {
            width: 33%;
            border: 1px solid #d32f2f;
            border-radius: 6px;
            padding: 6px;
            float: right;
            height: 90px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 90px;
            font-size: 10.5pt;
        }

        .value {
            display: inline-block;
            font-size: 10.5pt;
        }

        /* ==========================================================
           6. Items Table
           ========================================================== */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            border: 1px solid #d32f2f;
        }

        .items-table th {
            background-color: #fce4ec;
            border: 1px solid #d32f2f;
            padding: 3px;
            font-size: 10.5pt;
            font-weight: bold;
            color: #000;
        }

        .items-table td {
            border-left: 1px solid #d32f2f;
            border-right: 1px solid #d32f2f;
            padding: 1px 5px;
            font-size: 10.5pt;
            vertical-align: top;
        }

        .items-table tr.item-row {
            height: 22px;
        }

        .items-table tr.last-row td {
            border-bottom: 1px solid #d32f2f;
        }

        /* ==========================================================
           7. Footer — Remark + Summary Totals
           ========================================================== */
        .footer-container {
            width: 100%;
            margin-top: 0;
            border-left: 1px solid #d32f2f;
            border-right: 1px solid #d32f2f;
            border-bottom: 1px solid #d32f2f;
        }

        .remark-box {
            width: 58%;
            padding: 4px;
            font-size: 8.5pt;
            vertical-align: top;
            border-right: 1px solid #d32f2f;
            float: left;
        }

        .totals-box {
            width: 38%;
            vertical-align: top;
            float: right;
        }

        .total-row {
            width: 100%;
            border-bottom: 1px solid #d32f2f;
            padding: 2px 5px;
        }

        .total-row:last-child {
            border-bottom: none;
            background-color: #fce4ec;
        }

        .total-label {
            display: inline-block;
            width: 55%;
            font-size: 10.5pt;
        }

        .total-value {
            display: inline-block;
            width: 40%;
            text-align: right;
            font-size: 10.5pt;
            font-weight: bold;
        }

        /* ==========================================================
           8. Signature Boxes
           ========================================================== */
        .signatures-container {
            width: 100%;
            margin-top: 10px;
        }

        .sig-box {
            width: 31%;
            border: 1px solid #d32f2f;
            border-radius: 5px;
            height: 75px;
            display: inline-block;
            text-align: center;
            font-size: 9.5pt;
            padding-top: 4px;
        }

        .sig-box-auth {
            float: right;
            width: 35%;
        }

        .sig-line {
            border-bottom: 1px dotted #000;
            width: 80%;
            margin: 35px auto 5px;
        }

        /* ==========================================================
           9. Checkbox (DomPDF-compatible)
           ========================================================== */
        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            vertical-align: middle;
            margin-right: 2px;
            position: relative;
        }

        .checkbox.checked:after {
            content: 'X';
            position: absolute;
            top: -6px;
            left: 1px;
            font-size: 9pt;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    <span class="logo-text">{{ mb_substr($saleOrder->company->name, 0, 1) }}</span>
                    <span class="company-name">{{ $saleOrder->company->name }}</span>
                    <div class="company-details">
                        @if($saleOrder->branch->is_headquarter)
                            สำนักงานใหญ่
                        @else
                            {{ $saleOrder->branch->name }} ({{ $saleOrder->branch->code }})
                        @endif
                        <br>
                        {{ $saleOrder->company->address_0 }} {{ $saleOrder->company->amphoe }} {{ $saleOrder->company->province }} {{ $saleOrder->company->postal_code }}<br>
                        โทร: {{ $saleOrder->company->tel }} &nbsp; แฟกซ์: {{ $saleOrder->company->fax }}<br>
                        เลขประจำตัวผู้เสียภาษีอากร / TAX ID: {{ $saleOrder->company->tax_id }}
                    </div>
                </td>
                <td class="doc-title-section">
                    <div class="doc-title-box">
                        <div class="doc-title-main">ใบกำกับภาษี/ใบแจ้งหนี้/ใบส่งสินค้า (ต้นฉบับ)</div>
                        <div class="doc-title-sub">TAX INVOICE/INVOICE/DELIVERY ORDER (ORIGINAL)</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Info Boxes -->
        <div class="info-container">
            <div class="customer-box">
                <div><span class="label">ชื่อผู้ซื้อ :</span> <span class="value">{{ $saleOrder->customer->name }}</span></div>
                <div><span class="label">ที่อยู่ :</span> <span class="value">{{ $saleOrder->customer->address_0 }} {{ $saleOrder->customer->amphoe }} {{ $saleOrder->customer->province }} {{ $saleOrder->customer->postal_code }}</span></div>
                <div><span class="label">โทรศัพท์ :</span> <span class="value">{{ $saleOrder->customer->tel }}</span></div>
                <div style="margin-top: 3px;">
                    <span class="label">เลขผู้เสียภาษี :</span>
                    <span class="value">{{ $saleOrder->customer->tax_id }}</span>
                    <span style="margin-left: 12px;">
                        <span class="checkbox {{ $saleOrder->customer->is_head_office ? 'checked' : '' }}"></span> สำนักงานใหญ่
                        <span class="checkbox {{ !$saleOrder->customer->is_head_office ? 'checked' : '' }}" style="margin-left: 8px;"></span> สาขาที่ {{ $saleOrder->customer->branch_no }}
                    </span>
                </div>
            </div>
            <div class="document-box">
                <div><span class="label">เลขที่ No. :</span> <span class="value">{{ $saleOrder->invoice_number }}</span></div>
                <div><span class="label">วันที่ Date :</span> <span class="value">{{ $saleOrder->order_date->format('d/m/Y') }}</span></div>
                <div style="border-top: 1px solid #d32f2f; margin-top: 2px; padding-top: 2px;">
                    <div style="font-size: 8.5pt;">กำหนดชำระเงิน : {{ $saleOrder->term_of_payment }}</div>
                    <div style="font-size: 8.5pt;">วันที่ครบกำหนด : {{ $saleOrder->due_date?->format('d/m/Y') ?? '-' }}</div>
                    <div style="font-size: 8.5pt;">พนักงานขาย : {{ $saleOrder->creator->name ?? '-' }}</div>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">ลำดับ<br>Item</th>
                    <th style="width: 15%;">รหัสสินค้า<br>Product Code</th>
                    <th style="width: 40%;">รายละเอียดสินค้า<br>Description</th>
                    <th style="width: 10%;">จำนวน<br>Quantity</th>
                    <th style="width: 12%;">ราคาต่อหน่วย<br>Unit Price</th>
                    <th style="width: 8%;">ส่วนลด<br>Discount</th>
                    <th style="width: 10%;">จำนวนเงิน<br>Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $maxItems = 13; @endphp
                @foreach($saleOrder->items as $index => $item)
                <tr class="item-row {{ ($index == $maxItems - 1 || ($loop->last && count($saleOrder->items) >= $maxItems)) ? 'last-row' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->product->code }}</td>
                    <td>
                        {{ $item->product->name }}
                        @if($item->description)
                            <br><small style="font-size: 9pt; line-height: 1;">{{ $item->description }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity) }} {{ $item->product->unit->name }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $item->discount > 0 ? number_format($item->discount, 2) : '' }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach

                <!-- Fill empty rows to reach fixed table height -->
                @for($i = count($saleOrder->items); $i < $maxItems; $i++)
                <tr class="item-row {{ $i == $maxItems - 1 ? 'last-row' : '' }}">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-container">
            <div class="remark-box">
                <strong>หมายเหตุ / Remark</strong><br>
                1. ได้รับสินค้าเรียบร้อยครบถ้วนตามรายการและจำนวนที่ระบุในเอกสารแล้ว<br>
                2. ในกรณีที่มีการผิดนัดชำระเงินเมื่อถึงกำหนด ผู้ซื้อจำเป็นต้องจ่ายเบี้ยปรับ
                @if($saleOrder->notes)
                    <br>3. {{ Str::limit($saleOrder->notes, 150) }}
                @endif
            </div>
            <div class="totals-box">
                <div class="total-row">
                    <span class="total-label">ยอดรวม (Total)</span>
                    <span class="total-value">{{ number_format($saleOrder->subtotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">ภาษีมูลค่าเพิ่ม (VAT) 7%</span>
                    <span class="total-value">{{ number_format($saleOrder->vat_amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label" style="font-weight: bold;">รวมเงินทั้งสิ้น (Grand Total)</span>
                    <span class="total-value" style="font-size: 12pt;">{{ number_format($saleOrder->total_amount, 2) }}</span>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Signatures -->
        <div class="signatures-container">
            <div class="sig-box">
                ได้รับสินค้า / Received By
                <div class="sig-line"></div>
                วันที่/Date....../....../......
            </div>
            <div class="sig-box">
                ผู้ส่งสินค้า / Delivered By
                <div class="sig-line"></div>
                วันที่/Date....../....../......
            </div>
            <div class="sig-box sig-box-auth">
                ในนาม {{ $saleOrder->company->name }}
                <div class="sig-line" style="margin-top: 25px;"></div>
                ผู้มีอำนาจลงนาม / Authorized Signature
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
