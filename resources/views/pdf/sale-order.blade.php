<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบส่งสินค้า - Sale Order - {{ $saleOrder->invoice_number }}</title>
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
            margin: 97mm 15mm 15mm 15mm; /* ปรับมาที่ 97mm ตามที่แจ้งครับ */
        }

        header {
            position: fixed;
            top: -87mm; /* ขยับหัวกระดาษลงมาตามสัดส่วน */
            left: 0;
            right: 0;
            height: 80mm;
        }

        footer {
            position: fixed;
            bottom: -5mm;
            left: 0;
            right: 0;
            height: 10mm;
            text-align: right;
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
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: 700;
        }

        .text-primary {
            color: #1e40af;
        }

        .text-white {
            color: #ffffff;
        }

        .bg-primary {
            background-color: #1e40af;
        }

        .bg-light {
            background-color: #f8fafc;
        }

        .border-bottom {
            border-bottom: 1px solid #cbd5e1;
        }

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

        .meta-box th,
        .meta-box td {
            padding: 4px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 15px;
        }

        .meta-box tr:last-child th,
        .meta-box tr:last-child td {
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
            height: 110px; /* ใช้ height คงที่เพื่อให้กรอบเท่ากันเสมอ */
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
            margin-bottom: 15px;
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

        thead {
            display: table-header-group; /* ทำให้หัวตารางพิมพ์ซ้ำทุกหน้า */
        }

        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #cbd5e1;
        }

        .items-table .striped {
            background-color: #f8fafc;
        }

        /* Summary Section */
        .summary-table {
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
        }

        .text-box {
            background-color: #f8fafc;
        }

        .grand-total {
            font-size: 18px;
            font-weight: 700;
            background-color: #eff6ff;
            color: #1e40af;
        }

        /* Signature Section */
        .signature-table {
            margin-top: 15px;
            /* ลดระยะห่างเพื่อให้พอดีหน้า */
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            padding: 0 20px;
        }

        .signature-line {
            border-bottom: 1px dashed #94a3b8;
            margin: 25px auto 5px auto;
            /* ลดระยะเว้นวรรคเหนือเส้นบรรทัดเซ็น */
            width: 80%;
        }

        @media print {
            body {
                background: white;
            }

            .bg-primary {
                background-color: #1e40af !important;
                color: white !important;
            }

            .bg-light {
                background-color: #f8fafc !important;
            }

            .grand-total {
                background-color: #eff6ff !important;
            }

            th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
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

        // Define missing variables that were in the summary
        $subtotal = $saleOrder->subtotal ?? 0;
        $discount_amount = $saleOrder->discount_amount ?? 0;
        $vat_amount = $saleOrder->vat_amount ?? 0;
        $total_amount = $saleOrder->total_amount ?? 0;

        // ตรรกะการจัดหน้า:
        // 1. หน้าที่มีรายการอย่างเดียว (ไม่รวมสรุป/ลายเซ็น) จุได้ประมาณ 18 รายการ
        // 2. หน้าที่มีสรุปและลายเซ็นด้วย จะเหลือพื้นที่ใส่รายการสินค้าได้ประมาณ 10 รายการ
        $maxItemsPerPage = 17;
        $maxItemsWithSignatures = 8;

        $currentItemsCount = $saleOrder->items->count();
        $itemsOnLastPage = $currentItemsCount % $maxItemsPerPage;

        // คำนวณหาจำนวนแถวว่างที่ต้องเติม เพื่อให้ผลรวม (รายการจริง + รายการว่าง) = 8 แถวเสมอในหน้าสุดท้าย
        if ($itemsOnLastPage > $maxItemsWithSignatures) {
            // กรณีที่รายการในหน้าปัจจุบันเกิน 8 -> เติมให้เต็มหน้าปัจจุบัน แล้วไปเติมแผ่นใหม่ให้ได้ 8 แถว
            $emptyRowsNeeded = ($maxItemsPerPage - $itemsOnLastPage) + $maxItemsWithSignatures;
        } else {
            // กรณีที่รายการในหน้าปัจจุบันยังไม่เกิน 8 -> เติมให้ครบ 8 แถวพอดี
            $emptyRowsNeeded = $maxItemsWithSignatures - $itemsOnLastPage;
        }

    @endphp

    <header>
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="60%">
                    <div class="company-name">{{ $saleOrder->company->name ?? 'บริษัท คลาวด์ เทค จำกัด' }}</div>
                    <div style="font-size: 14px; line-height: 1.1;">{!! nl2br(e($companyAddress ?: '-')) !!}</div>
                    <div style="margin-top: 4px; font-size: 14px;">โทรศัพท์: {{ $saleOrder->branch->tel ?? '-' }} | เลขประจำตัวผู้เสียภาษี: {{ $saleOrder->company->tax_id ?? '-' }}</div>
                </td>
                <td width="40%" class="text-right">
                    <div class="doc-title">ใบส่งสินค้า<br><span style="font-size: 20px;">Delivery Order</span></div>
                    <div style="margin-top: 5px;">
                        <table class="meta-box">
                            <tr>
                                <th class="text-left" width="40%" style="background-color: #e2e8f0; color: #1e293b;">
                                    เลขที่ (No.)</th>
                                <td class="text-right font-bold">{{ $saleOrder->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th class="text-left" style="background-color: #e2e8f0; color: #1e293b;">วันที่ (Date)
                                </th>
                                <td class="text-right">{{ $saleOrder->order_date?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-left" style="background-color: #e2e8f0; color: #1e293b;">พนักงานขาย</th>
                                <td class="text-right">{{ $saleOrder->creator->name ?? '-' }}</td>
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
                        <div class="info-title">รายละเอียดลูกค้า (Customer)</div>
                        <div class="font-bold" style="font-size: 15px; margin-bottom: 2px;">{{ $saleOrder->customer->name ?? '-' }}</div>
                        <div style="font-size: 14px; line-height: 1.2;">
                            {{ $customerAddress ?: '-' }}<br>
                            <b>เลขประจำตัวผู้เสียภาษี :</b> {{ $saleOrder->customer->tax_id ?? '-' }}
                            @if($saleOrder->customer->is_head_office)
                                <span style="margin-left: 5px;">(สำนักงานใหญ่)</span>
                            @else
                                <span style="margin-left: 5px;">(สาขา: {{ $saleOrder->customer->branch_no }})</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td width="45%" style="padding-left: 10px;">
                    <div class="info-box">
                        <div class="info-title">การชำระเงินและหมายเหตุ (Terms & Notes)</div>
                        <table style="width: 100%; font-size: 15px;">
                            <tr>
                                <td width="35%" class="font-bold py-1">เงื่อนไขชำระเงิน:</td>
                                <td>{{ $saleOrder->paymentMethod->name ?? 'เครดิต' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold py-1">ครบกำหนด:</td>
                                <td>{{ $saleOrder->due_date?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold py-1" style="vertical-align: top;">หมายเหตุ:</td>
                                <td style="line-height: 1.1; max-height: 35px; overflow: hidden;">{{ $saleOrder->notes ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <div class="container">

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr class="bg-primary text-white">
                    <th width="5%" class="text-center">ลำดับ<br>Item</th>
                    <th width="12%" class="text-center">รหัสสินค้า<br>Code</th>
                    <th width="33%" class="text-left">รายละเอียดสินค้า<br>Description</th>
                    <th width="8%" class="text-center">จำนวน<br>Qty</th>
                    <th width="8%" class="text-center">หน่วย<br>Unit</th>
                    <th width="10%" class="text-right">ราคา/หน่วย<br>Unit Price</th>
                    <th width="12%" class="text-right">ส่วนลด/หน่วย<br>Discount</th>
                    <th width="12%" class="text-right">จำนวนเงิน<br>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($saleOrder->items as $index => $item)
                    <tr class="{{ $index % 2 == 1 ? 'striped' : '' }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $item->product->code ?? $item->product->sku ?? '-' }}</td>
                        <td class="text-left">
                            <div class="font-bold">{{ $item->product->name ?? '-' }}</div>
                        </td>
                        <td class="text-center font-bold">{{ number_format($item->quantity) }}</td>
                        <td class="text-center">{{ $item->product->unit->name ?? 'ชิ้น' }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right" style="color: #b91c1c;">
                            {{ ($item->discount ?? 0) > 0 ? number_format($item->discount, 2) : '-' }}</td>
                        <td class="text-right font-bold">
                            {{ number_format($item->total_price ?? (($item->unit_price - ($item->discount ?? 0)) * $item->quantity), 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-2">ไม่มีรายการสินค้า</td>
                    </tr>
                @endforelse

                <!-- Fill empty rows to push summary/signatures to the bottom -->
                @for($i = 0; $i < $emptyRowsNeeded; $i++)
                    <tr class="{{ ($currentItemsCount + $i) % 2 == 1 ? 'striped' : '' }}">
                        <td style="color: transparent;">-</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div style="page-break-inside: avoid;">
            <!-- Summary -->
            <table class="summary-table">
                <tr>
                    <td rowspan="4" width="60%" class="text-box" style="vertical-align: top;">
                        <br>
                        <div style="text-align: center; font-size: 18px; color: #1e40af; margin-top: 10px;">
                            <b>จำนวนเงินตัวอักษร:</b><br>
                            ( {{ \App\Helpers\ThaiNumberHelper::toThaiText($total_amount) }} )
                        </div>
                    </td>
                    <td width="25%" class="text-right font-bold bg-light">รวมเงิน (Sub Total)</td>
                    <td width="15%" class="text-right">{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right font-bold bg-light">ส่วนลด (Discount)</td>
                    <td class="text-right">{{ number_format($discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right font-bold bg-light">ภาษีมูลค่าเพิ่ม 7% (VAT)</td>
                    <td class="text-right">{{ number_format($vat_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right grand-total">ยอดเงินสุทธิ (Net Total)</td>
                    <td class="text-right grand-total">{{ number_format($total_amount, 2) }}</td>
                </tr>
            </table>

            <!-- Signatures -->
            <table class="signature-table">
                <tr>
                    <td width="40%" class="signature-box">
                        <div class="signature-line"></div>
                        <div>(.......................................................)</div>
                        <div class="font-bold" style="margin-top: 5px;">ผู้รับสินค้า</div>
                        <div>Customer Authorized Signature</div>
                        <div style="margin-top: 5px;">วันที่ ....... / ....... / ...........</div>
                    </td>
                    <td width="20%"></td>
                    <td width="40%" class="signature-box">
                        <div class="signature-line"></div>
                        <div>(.......................................................)</div>
                        <div class="font-bold" style="margin-top: 5px;">ผู้ส่งสินค้า</div>
                        <div>Authorized Signature</div>
                        <div style="margin-top: 5px;">วันที่ ....... / ....... / ...........</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <footer>
        <div class="page-number text-primary font-bold"></div>
    </footer>
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("TH Sarabun", "bold");
            $pdf->page_text(480, 810, "หน้า {PAGE_NUM} / {PAGE_COUNT}", $font, 13, array(0.12, 0.25, 0.69));
        }
    </script>
</body>

</html>
