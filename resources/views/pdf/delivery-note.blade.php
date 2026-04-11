<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบส่งของ - Delivery Note - {{ $saleOrder->invoice_number }}</title>
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
            font-size: 16px;
            color: #1e293b;
            line-height: 1.2;
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
            font-size: 28px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 4px;
        }
        .doc-title {
            font-size: 32px;
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
            border-bottom: 1px solid #e2e8f0;
            font-size: 15px;
        }
        .meta-box tr:last-child th, .meta-box tr:last-child td {
            border-bottom: none;
        }

        /* Customer Section */
        .info-table {
            margin-top: 0px;
            margin-bottom: 20px;
            clear: both;
        }
        .info-box {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            border-radius: 4px;
            height: 120px; /* ใช้ height คงที่เพื่อให้กรอบเท่ากันเสมอ */
        }
        .info-title {
            font-weight: 700;
            color: #1e40af;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 6px;
            font-size: 16px;
        }

        /* Items Section */
        .items-table {
            margin-bottom: 30px;
        }
        .items-table th {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #1e40af;
        }
        .items-table td {
            padding: 6px 8px;
            border-left: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 15px;
            vertical-align: top;
        }
        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #cbd5e1;
        }
        .items-table .striped {
            background-color: #f8fafc;
        }

        /* Footer Section */
        .footer-table {
            width: 100%;
            margin-top: 20px;
        }
        .remarks-box {
            font-size: 14px;
            color: #475569;
            padding: 10px;
            border: 1px dashed #cbd5e1;
            background-color: #f8fafc;
        }

        /* Signature Section */
        .signature-table {
            margin-top: 15px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            padding: 0 10px;
        }
        .signature-line {
            border-bottom: 1px dashed #94a3b8;
            margin: 25px auto 5px auto;
            width: 80%;
        }

        @media print {
            body { background: white; }
            .bg-primary { background-color: #1e40af !important; color: white !important; }
            .bg-light { background-color: #f8fafc !important; }
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
    @endphp

    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="60%">
                    <div class="company-name">{{ $saleOrder->company->name ?? 'บริษัท คลาวด์ เทค จำกัด' }}</div>
                    <div>{!! nl2br(e($companyAddress ?: '-')) !!}</div>
                    <div style="margin-top: 4px;">โทรศัพท์: {{ $saleOrder->branch->tel ?? '-' }}</div>
                    <div>เลขประจำตัวผู้เสียภาษี: {{ $saleOrder->company->tax_id ?? '-' }}</div>
                </td>
                <td width="40%" class="text-right">
                    <div class="doc-title">ใบส่งของ<br><span style="font-size: 20px;">DELIVERY NOTE</span></div>
                    <div style="margin-top: 10px;">
                        <table class="meta-box">
                            <tr>
                                <th class="text-left" width="40%" style="background-color: #e2e8f0; color: #1e293b;">เลขที่ (No.)</th>
                                <td class="text-right font-bold">{{ $saleOrder->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th class="text-left" style="background-color: #e2e8f0; color: #1e293b;">วันที่ (Date)</th>
                                <td class="text-right">{{ $saleOrder->order_date?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-left" style="background-color: #e2e8f0; color: #1e293b;">อ้างอิง PO</th>
                                <td class="text-right">{{ $referenceText }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Info Boxes -->
        <table class="info-table">
            <tr>
                <td width="55%" style="padding-right: 10px;">
                    <div class="info-box">
                        <div class="info-title">ส่งถึง (Customer)</div>
                        <div class="font-bold pb-1">{{ $saleOrder->customer->name ?? '-' }}</div>
                        <div style="font-size: 14px; line-height: 1.2;">
                            ผู้ติดต่อ: {{ $saleOrder->contact_person ?? '-' }}<br>
                            เบอร์โทร: {{ $saleOrder->contact_phone ?? ($saleOrder->customer->tel ?? '-') }}<br>
                            เลขประจำตัวผู้เสียภาษี : {{ $saleOrder->customer->tax_id ?? '-' }}
                        </div>
                    </div>
                </td>
                <td width="45%" style="padding-left: 10px;">
                    <div class="info-box">
                        <div class="info-title">สถานที่จัดส่ง (Shipping Address)</div>
                        <div style="font-size: 15px;">
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

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr class="bg-primary text-white">
                    <th width="8%" class="text-center">ลำดับ<br>No</th>
                    <th width="15%" class="text-center">รหัสสินค้า<br>Code</th>
                    <th width="42%" class="text-left">รายละเอียดสินค้า<br>Description</th>
                    <th width="10%" class="text-center">จำนวน<br>Qty</th>
                    <th width="10%" class="text-center">หน่วย<br>Unit</th>
                    <th width="15%" class="text-center">หมายเหตุ<br>Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($saleOrder->items as $index => $item)
                <tr class="{{ $index % 2 == 1 ? 'striped' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->product->code ?? $item->product->sku ?? '-' }}</td>
                    <td class="text-left">
                        <div class="font-bold">{{ $item->product->name ?? '-' }}</div>
                        @if($item->description)
                            <div style="font-size: 13px; color: #64748b;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ number_format($item->quantity) }}</td>
                    <td class="text-center">{{ $item->product->unit->name ?? 'ชิ้น' }}</td>
                    <td class="text-center" style="color: #64748b; font-size: 13px;">{{ $item->notes ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-2">ไม่มีรายการสินค้า</td>
                </tr>
                @endforelse

                <!-- Fill empty rows if needed to make it look uniform -->
                @for($i = $saleOrder->items->count(); $i < 6; $i++)
                <tr class="{{ $i % 2 == 1 ? 'striped' : '' }}">
                    <td style="color: transparent;">-</td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Footer / Remarks -->
        <table class="footer-table">
            <tr>
                <td class="remarks-box">
                    <div class="font-bold" style="color: #1e293b;">หมายเหตุ (Remarks):</div>
                    <div style="margin-top: 4px;">1. กรุณาตรวจสอบความถูกต้องของสินค้าและจำนวนก่อนเซ็นรับ</div>
                    <div>2. หากสินค้ามีตำหนิหรือไม่ตรงตามรายการ กรุณาแจ้งกลับภายใน 7 วันทำการ</div>
                    @if(filled($saleOrder->notes))
                        <div style="margin-top: 4px;">{!! nl2br(e($saleOrder->notes)) !!}</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Signatures -->
        <table class="signature-table">
            <tr>
                <td width="30%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>(.......................................................)</div>
                    <div class="font-bold" style="margin-top: 5px;">ผู้จัดเตรียมสินค้า</div>
                    <div>Prepared by</div>
                    <div style="margin-top: 5px; color: #64748b;">วันที่ ....... / ....... / ...........</div>
                </td>
                <td width="5%"></td>
                <td width="30%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>(.......................................................)</div>
                    <div class="font-bold" style="margin-top: 5px;">ผู้ส่งสินค้า</div>
                    <div>Delivered by</div>
                    <div style="margin-top: 5px; color: #64748b;">วันที่ ....... / ....... / ...........</div>
                </td>
                <td width="5%"></td>
                <td width="30%" class="signature-box">
                    <div style="font-size: 13px; text-align: center; margin-bottom: 25px;">ได้รับสินค้าตามรายการข้างต้นครบถ้วนและอยู่ในสภาพเรียบร้อยสมบูรณ์</div>
                    <div class="signature-line" style="margin-top: 0;"></div>
                    <div>(.......................................................)</div>
                    <div class="font-bold" style="margin-top: 5px;">ผู้รับสินค้า</div>
                    <div>Received by</div>
                    <div style="margin-top: 5px; color: #64748b;">วันที่ ....... / ....... / ...........</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
