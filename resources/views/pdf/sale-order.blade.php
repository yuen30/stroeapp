<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบกำกับภาษี {{ $saleOrder->invoice_number }}</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap");

        * {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: "Sarabun", sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            background: #525659;
        }

        .page {
            width: 210mm;
            height: 297mm;
            padding: 10mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            position: relative;
            color: #000;
        }

        .header-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .logo-section {
            width: 10%;
            font-size: 32pt;
            font-weight: bold;
        }

        .company-section {
            width: 55%;
            font-size: 10pt;
            line-height: 1.2;
        }

        .company-name-th {
            font-size: 14pt;
            font-weight: bold;
        }

        .company-name-en {
            font-size: 10pt;
        }

        .title-section {
            width: 35%;
            text-align: right;
        }

        .cust-copy {
            font-size: 10pt;
            font-weight: bold;
        }

        .title-th {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 2px 0;
        }

        .title-en {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .multi-doc {
            color: red;
            font-size: 9pt;
            border: 1px solid red;
            display: inline-block;
            padding: 2px 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            margin-top: 10px;
            border: 1px solid #000;
        }

        .grid-box {
            padding: 5px;
            position: relative;
        }

        .border-right {
            border-right: 1px solid #000;
        }

        .row {
            display: flex;
            margin-bottom: 2px;
        }

        .label {
            min-width: 60px;
            font-weight: bold;
        }

        .label-long {
            min-width: 120px;
            font-weight: bold;
        }

        .branch-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 5px;
        }

        .check-box {
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10pt;
        }

        .table-container {
            margin-top: -1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
        }

        th {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10pt;
            background: #f2f2f2;
        }

        td {
            border-right: 1px solid #000;
            padding: 4px 8px;
            vertical-align: top;
            font-size: 10pt;
            min-height: 25px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .empty-rows {
            height: 280px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            border: 1px solid #000;
            border-top: none;
        }

        .remark {
            padding: 8px;
            font-size: 9pt;
            line-height: 1.4;
            border-right: 1px solid #000;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            border: none;
            border-bottom: 1px solid #000;
            padding: 5px;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            font-size: 12pt;
        }

        .sig-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            margin-top: 15px;
            text-align: center;
            font-size: 9pt;
        }

        .sig-box {
            border: 1px solid #000;
            margin-right: -1px;
            padding: 10px 5px;
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sig-line {
            border-bottom: 1px dotted #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header-main">
            <div class="logo-section">G</div>
            <div class="company-section">
                <div class="company-name-th">
                    {{ $saleOrder->company->name }}
                    @if($saleOrder->branch->is_headquarter)
                        (สำนักงานใหญ่)
                    @else
                        ({{ $saleOrder->branch->name }})
                    @endif
                </div>
                @if($saleOrder->company->name_en)
                <div class="company-name-en">{{ $saleOrder->company->name_en }}</div>
                @endif
                <div>
                    {{ $saleOrder->company->address_0 }}
                    @if($saleOrder->company->address_1){{ $saleOrder->company->address_1 }}@endif
                    {{ $saleOrder->company->amphoe }}
                    {{ $saleOrder->company->province }}
                    {{ $saleOrder->company->postal_code }}
                </div>
                <div>Tel. {{ $saleOrder->company->tel }}</div>
                <div>เลขประจำตัวผู้เสียภาษีอากร TAX ID. {{ $saleOrder->company->tax_id }}</div>
            </div>
            <div class="title-section">
                <div class="cust-copy">สำหรับลูกค้า / CUSTOMER</div>
                <div class="title-th">ใบกำกับภาษี/ใบแจ้งหนี้/ใบส่งสินค้า (สำเนา)</div>
                <div class="title-en">TAX INVOICE/INVOICE/DELIVERY ORDER (COPY)</div>
                <div class="multi-doc">เอกสารออกเป็นชุด (ไม่ใช่ใบกำกับภาษี)</div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="grid-box border-right">
                <div class="row">
                    <span class="label">ชื่อผู้ซื้อ:</span>
                    <span class="value">{{ $saleOrder->customer->name }}</span>
                </div>
                <div class="row">
                    <span class="label">ที่อยู่:</span>
                    <span class="value">{{ $saleOrder->customer->address_0 }} {{ $saleOrder->customer->amphoe }} {{ $saleOrder->customer->province }}</span>
                </div>
                <div class="row">
                    <span class="label-long">เลขประจำตัวผู้เสียภาษีอากร:</span>
                    <span class="value">{{ $saleOrder->customer->tax_id ?? '' }}</span>
                </div>
                <div class="branch-info">
                    <span class="check-box">@if($saleOrder->customer->is_head_office)✓@endif</span> สำนักงานใหญ่
                    <span class="check-box">@if(!$saleOrder->customer->is_head_office)✓@endif</span> สาขาที่ {{ $saleOrder->customer->branch_no ?? '...' }}
                </div>
            </div>
            <div class="grid-box">
                <div class="row">
                    <span class="label">เลขที่ No.:</span>
                    <span class="value">{{ $saleOrder->invoice_number }}</span>
                </div>
                <div class="row">
                    <span class="label">วันที่ Date:</span>
                    <span class="value">{{ $saleOrder->order_date->format('d/m/Y') }}</span>
                </div>
                <div class="row">
                    <span class="label-long">กำหนดชำระเงิน Due Date:</span>
                    <span class="value">{{ $saleOrder->due_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="row">
                    <span class="label">พนักงานขาย:</span>
                    <span class="value">{{ $saleOrder->creator->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="8%">ลำดับ<br>Item</th>
                        <th width="52%">รายละเอียดสินค้า / รหัสสินค้า<br>Description / Product Code</th>
                        <th width="10%">จำนวน<br>Quantity</th>
                        <th width="15%">ราคาต่อหน่วย<br>Unit Price</th>
                        <th width="15%">จำนวนเงิน<br>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxItems = 12; @endphp
                    @foreach($saleOrder->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            {{ $item->product->name }}
                            @if($item->product->code)
                                <br><small>({{ $item->product->code }})</small>
                            @endif
                            @if($item->description)
                                <br><small>{{ $item->description }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity) }} {{ $item->product->unit->name ?? '' }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach

                    @for($i = count($saleOrder->items); $i < $maxItems; $i++)
                    <tr class="empty-rows">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-grid">
            <div class="remark">
                <strong>หมายเหตุ / Remark:</strong>
                <br />
                1. ผู้ซื้อได้รับสินค้าเรียบร้อยครบถ้วนตามรายการและจำนวนที่ระบุในเอกสารแล้ว
                <br />
                2. กรณีผิดนัดชำระเงิน ผู้ซื้อจำเป็นต้องจ่ายเบี้ยปรับและบริษัทมีสิทธิ์ดำเนินการทางกฎหมาย
                <br />
                3. กรรมสิทธิ์ในสินค้าจะเป็นของ {{ $saleOrder->company->name }} จนกว่าจะชำระครบถ้วน
                @if($saleOrder->notes)
                    <br />
                    4. {{ $saleOrder->notes }}
                @endif
            </div>
            <div class="totals-area">
                <table class="totals-table">
                    <tr>
                        <td width="60%">ยอดรวม (Total)</td>
                        <td class="text-right">{{ number_format($saleOrder->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>ภาษีมูลค่าเพิ่ม (VAT {{ $saleOrder->customer->vat_rate ?? 7 }}%)</td>
                        <td class="text-right">{{ number_format($saleOrder->vat_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>รวมเงินทั้งสิ้น (Grand Total)</td>
                        <td class="text-right">{{ number_format($saleOrder->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signatures -->
        <div class="sig-container">
            <div class="sig-box">
                <span>ได้รับสินค้าตามรายการข้างต้นโดยถูกต้อง</span>
                <div>
                    <div class="sig-line"></div>
                    ผู้รับสินค้า / Received By
                </div>
            </div>
            <div class="sig-box">
                <span>&nbsp;</span>
                <div>
                    <div class="sig-line"></div>
                    ผู้ส่งสินค้า / Delivered By
                </div>
            </div>
            <div class="sig-box">
                <span>ในนาม {{ $saleOrder->company->name }}</span>
                <div>
                    <div class="sig-line"></div>
                    ผู้มีอำนาจลงนาม / Authorized Signature
                </div>
            </div>
        </div>
    </div>
</body>
</html>
