<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบแจ้งหนี้ - Invoice - {{ $saleOrder->invoice_number }}</title>
    <style>
        @font-face {
            font-family: 'TH Sarabun';
            src: url("{{ public_path('fonts/THSarabun.ttf') }}") format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'TH Sarabun';
            src: url("{{ public_path('fonts/THSarabun-Bold.ttf') }}") format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @page {
            size: A4;
            margin: 10mm 15mm;
        }

        body {
            font-family: 'TH Sarabun', sans-serif;
            font-size: 15px;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .container {
            width: 100%;
        }

        /* Utility Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 700; }
        .text-primary { color: #1e40af; }
        .text-white { color: #ffffff; }
        .bg-primary { background-color: #1e40af; }
        .bg-light { background-color: #f8fafc; }
        .bg-gray { background-color: #e2e8f0; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header Section */
        .header-table {
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-name {
            font-size: 26px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 2px;
        }
        .doc-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
        }
        
        .meta-box {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 4px;
            width: 250px;
            margin-left: auto;
        }
        .meta-box th, .meta-box td {
            padding: 4px 8px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 14px;
        }
        .meta-box tr:last-child th, .meta-box tr:last-child td {
            border-bottom: none;
        }

        /* Customer Section */
        .info-table {
            margin-top: 0px;
            margin-bottom: 15px;
            clear: both;
        }
        .info-box {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            border-radius: 4px;
            height: 100px;
        }
        .info-title {
            font-weight: 700;
            color: #1e40af;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
            margin-bottom: 4px;
            font-size: 15px;
        }

        /* Meta Row Pre-items */
        .meta-row-table {
            margin-bottom: 15px;
            border: 1px solid #1e40af;
        }
        .meta-row-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 4px;
            font-size: 14px;
            color: #475569;
        }
        .meta-row-table td {
            border: 1px solid #cbd5e1;
            padding: 4px;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
        }

        /* Items Section */
        .items-table {
            margin-bottom: 0;
            border: 1px solid #1e40af;
        }
        .items-table th {
            padding: 6px;
            font-size: 15px;
            border: 1px solid #1e40af;
        }
        .items-table td {
            padding: 5px 6px;
            border-left: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 14px;
            vertical-align: top;
            height: 20px;
        }
        .items-table tbody tr.last-item-row td {
            border-bottom: 1px solid #cbd5e1;
        }
        .items-table .striped {
            background-color: #f8fafc;
        }

        /* Summary Section */
        .summary-table {
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            border-top: none;
        }
        .summary-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
        }
        .summary-table td:last-child {
            border-right: none;
        }
        .text-box {
            background-color: #fce7f3; /* A very slight red tint for notice / or maybe just light gray */
            background-color: #f1f5f9;
            font-size: 14px;
        }
        .thai-text-amount {
            background-color: #f8fafc;
            text-align: center;
            font-weight: 700;
            font-size: 16px;
            padding: 8px !important;
        }
        .grand-total {
            font-size: 16px;
            font-weight: 700;
            background-color: #eff6ff;
            color: #1e40af;
        }

        /* Signature Section */
        .signature-table {
            margin-top: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        .signature-table td {
            padding: 10px;
            border-right: 1px solid #cbd5e1;
            vertical-align: bottom;
        }
        .signature-table td:last-child {
            border-right: none;
        }
        .signature-box {
            text-align: center;
            font-size: 14px;
        }
        .signature-line {
            border-bottom: 1px dashed #94a3b8;
            margin: 20px auto 5px auto;
            width: 80%;
        }

        @media print {
            body { background: white; }
            .bg-primary { background-color: #1e40af !important; color: white !important; }
            .bg-light { background-color: #f8fafc !important; }
            .bg-gray { background-color: #e2e8f0 !important; }
            .grand-total { background-color: #eff6ff !important; }
            .thai-text-amount { background-color: #f8fafc !important; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $companyAddress = trim(implode(' ', array_filter([
            $saleOrder->branch->address_0 ?? null,
            $saleOrder->branch->address_1 ?? null,
            $saleOrder->branch->amphoe ?? null,
            $saleOrder->branch->province ?? null,
            $saleOrder->branch->postal_code ?? null,
        ])));

        $customerAddress = trim(implode(' ', array_filter([
            $saleOrder->customer->address_0 ?? null,
            $saleOrder->customer->address_1 ?? null,
            $saleOrder->customer->amphoe ?? null,
            $saleOrder->customer->province ?? null,
            $saleOrder->customer->postal_code ?? null,
        ])));

        $shippingAddress = $saleOrder->shipping_address ?: $customerAddress;
        $referenceText = $saleOrder->reference_number ?: ($saleOrder->purchaseOrder?->order_number ?? '-');

        $subtotal = $saleOrder->subtotal ?? 0;
        $discount = $saleOrder->discount_amount ?? 0;
        $vat = $saleOrder->vat_amount ?? 0;
        $total = $saleOrder->total_amount ?? $subtotal;
    @endphp

    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="60%">
                    <div class="company-name">{{ $saleOrder->company->name ?? 'บริษัท คลาวด์ เทค จำกัด' }}</div>
                    <div>{!! nl2br(e($companyAddress ?: '-')) !!}</div>
                    <div style="margin-top: 4px;">โทรศัพท์: {{ $saleOrder->branch->tel ?? '-' }} | สำนักงานใหญ่</div>
                    <div>เลขประจำตัวผู้เสียภาษี: {{ $saleOrder->company->tax_id ?? '-' }}</div>
                </td>
                <td width="40%" class="text-right">
                    <div class="doc-title">ใบแจ้งหนี้<br><span style="font-size: 18px; color: #64748b;">INVOICE</span></div>
                    <div style="margin-top: 8px;">
                        <table class="meta-box">
                            <tr>
                                <th class="text-left bg-gray" width="45%">เลขที่ (No.)</th>
                                <td class="text-right font-bold">{{ $saleOrder->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th class="text-left bg-gray">วันที่ (Date)</th>
                                <td class="text-right">{{ $saleOrder->order_date?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-left bg-gray">ครบกำหนด (Due)</th>
                                <td class="text-right font-bold" style="color: #b91c1c;">{{ $saleOrder->due_date?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Info Boxes -->
        <table class="info-table">
            <tr>
                <td width="55%" style="padding-right: 8px;">
                    <div class="info-box">
                        <div class="info-title">ลูกค้า (Customer To)</div>
                        <div style="font-size: 14px; margin-bottom: 2px;">
                            <span class="font-bold">{{ $saleOrder->customer->code ?? '-' }}</span> |
                            <b>เลขผู้เสียภาษี:</b> {{ $saleOrder->customer->tax_id ?? '-' }}
                        </div>
                        <div class="font-bold">{{ $saleOrder->customer->name ?? '-' }}</div>
                        <div style="font-size: 14px;">{{ $customerAddress ?: '-' }}</div>
                    </div>
                </td>
                <td width="45%" style="padding-left: 8px;">
                    <div class="info-box">
                        <div class="info-title">สถานที่ส่งมอบ (Delivery To)</div>
                        <div style="font-size: 14px;">
                            @if($shippingAddress)
                                {!! nl2br(e($shippingAddress)) !!}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Meta Line -->
        <table class="meta-row-table">
            <thead>
                <tr>
                    <th width="15%" class="text-center">อ้างอิง P/O (Ref.)</th>
                    <th width="20%" class="text-center">วันที่จัดส่ง (Del. Date)</th>
                    <th width="25%" class="text-center">เงื่อนไขชำระเงิน (Terms)</th>
                    <th width="40%" class="text-center">พนักงานขาย (Sales)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $referenceText }}</td>
                    <td>{{ $saleOrder->order_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $saleOrder->paymentMethod->name ?? 'เครดิต' }}</td>
                    <td style="text-align: left; padding-left: 8px;">{{ $saleOrder->salesman?->name ?? $saleOrder->creator->name ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr class="bg-primary text-white">
                    <th width="8%" class="text-center">ลำดับ<br>Item</th>
                    <th width="10%" class="text-center">จำนวน<br>Qty</th>
                    <th width="45%" class="text-left">รายละเอียดสินค้า<br>Description</th>
                    <th width="15%" class="text-right">ราคา/หน่วย<br>Unit Price</th>
                    <th width="22%" class="text-right">จำนวนเงิน<br>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($saleOrder->items as $index => $item)
                <tr class="{{ $index % 2 == 1 ? 'striped' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ number_format($item->quantity) }}</td>
                    <td class="text-left">
                        <div class="font-bold">{{ $item->product->name ?? '-' }}</div>
                        @if($item->description)
                            <div style="font-size: 13px; color: #64748b;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-2">ไม่มีรายการสินค้า</td>
                </tr>
                @endforelse
                
                <!-- Fixed Empty Rows -->
                @php $totalRows = 8; @endphp
                @for($i = $saleOrder->items->count(); $i < $totalRows; $i++)
                <tr class="{{ $i % 2 == 1 ? 'striped' : '' }} {{ $i == $totalRows - 1 ? 'last-item-row' : '' }}">
                    <td style="color: transparent;">-</td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Summary -->
        <table class="summary-table">
            <tr>
                <td rowspan="4" width="60%" class="text-box" style="vertical-align: top;">
                    <div style="margin-bottom: 5px;">
                        ได้รับสินค้าตามรายการข้างต้นในสภาพเรียบร้อยแล้ว<br>
                        <span style="color: #64748b; font-size: 13px;">Received as per above in good order and condition.</span>
                    </div>
                    <div>
                        ผิด ตก ยกเว้น (E.& O.E.) / สินค้าซื้อแล้วไม่รับเปลี่ยนหรือคืน
                    </div>
                </td>
                <td width="22%" class="text-right font-bold bg-light">รวมเงิน (Sub Total)</td>
                <td width="18%" class="text-right">{{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right font-bold bg-light">ส่วนลด (Discount)</td>
                <td class="text-right">{{ number_format($discount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right font-bold bg-light">ภาษีมูลค่าเพิ่ม 7% (VAT)</td>
                <td class="text-right">{{ number_format($vat, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right grand-total">ยอดสุทธิ (Net Amount)</td>
                <td class="text-right grand-total">{{ number_format($total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="thai-text-amount border-bottom">
                    ( {{ \App\Helpers\ThaiNumberHelper::toThaiText($total) }} )
                </td>
            </tr>
        </table>

        <!-- Signatures -->
        <table class="signature-table">
            <tr>
                <td width="33%" class="signature-box" style="border-right-style: dashed;">
                    <div class="signature-line"></div>
                    <div>ผู้วางบิล / ผู้ส่งสินค้า (Delivered By)</div>
                    <div style="margin-top: 5px; color: #64748b;">วันที่ ....... / ....... / ...........</div>
                </td>
                <td width="33%" class="signature-box" style="border-right-style: dashed;">
                    <div class="signature-line"></div>
                    <div>ผู้รับสินค้า / ผู้รับบิล (Received By)</div>
                    <div style="margin-top: 5px; color: #64748b;">วันที่ ....... / ....... / ...........</div>
                </td>
                <td width="34%" class="signature-box">
                    <div style="margin-bottom: 8px;" class="font-bold text-primary">ในนาม {{ $saleOrder->company->name ?? 'บริษัทฯ' }}</div>
                    <div class="signature-line" style="margin-top: 10px;"></div>
                    <div>ผู้มีอำนาจลงนาม (Authorized Signature)</div>
                    <div style="margin-top: 5px; color: #64748b;">วันที่ ....... / ....... / ...........</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
