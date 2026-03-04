<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบสั่งซื้อ {{ $purchaseOrder->order_number }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path('fonts/THSarabunNew Bold.ttf') }}") format('truetype');
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 16pt;
            line-height: 1.4;
            padding: 20px 30px;
        }

        /* Header Section */
        .header {
            text-align: left;
            margin-bottom: 5px;
        }
        .company-line {
            font-size: 16pt;
            font-weight: bold;
            border-bottom: 1px dotted #000;
            padding-bottom: 2px;
            margin-bottom: 8px;
        }
        .company-line span {
            margin-right: 50px;
        }
        .info-line {
            font-size: 14pt;
            border-bottom: 1px dotted #000;
            padding: 3px 0;
            margin-bottom: 5px;
        }
        .separator {
            border-bottom: 2px solid #000;
            margin: 15px 0;
        }

        /* Title */
        .doc-title {
            font-size: 18pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        /* Info Grid */
        .info-grid {
            font-size: 14pt;
            margin-bottom: 10px;
        }
        .info-row {
            margin-bottom: 5px;
            overflow: hidden;
        }
        .info-left {
            float: left;
            width: 48%;
        }
        .info-right {
            float: right;
            width: 48%;
        }
        .label {
            font-weight: bold;
        }
        .contact-label {
            font-weight: bold;
            margin-top: 5px;
            display: block;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 14pt;
        }
        table th {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            font-weight: bold;
            background: #fff;
        }
        table td {
            border: 1px solid #000;
            padding: 5px;
            min-height: 25px;
            vertical-align: top;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }

        /* Summary Row */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14pt;
            margin-top: -1px;
        }
        .summary-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .summary-label {
            width: 60%;
            text-align: center;
            font-weight: bold;
        }
        .summary-value {
            width: 40%;
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            overflow: hidden;
        }
        .signature-box {
            float: left;
            width: 48%;
            text-align: center;
            font-size: 14pt;
        }
        .signature-box.right {
            float: right;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px dotted #000;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-line">
            <span>บริษัท {{ $purchaseOrder->company->name }}</span>
            <span style="float: right;">จำกัด</span>
        </div>
        <div class="info-line">
            ที่อยู่ : {{ $purchaseOrder->company->address ?? '...................................................................................................................' }}
        </div>
        <div class="info-line">
            โทร : {{ $purchaseOrder->company->phone ?? '...................................................................................................................' }}
        </div>
    </div>

    <div class="separator"></div>

    <!-- Title -->
    <div class="doc-title">ใบสั่งซื้อ Purchase Order</div>

    <!-- Info Section -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-left">
                <span class="label">เรียน / Attn</span>
            </div>
            <div class="info-right">
                <span class="label">วันที่ / Date :</span> {{ $purchaseOrder->order_date->format('d/m/Y') }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-left">
                <span class="label">ที่อยู่ / Add</span>
            </div>
            <div class="info-right">
                <span class="label">เลขที่ใบสมุทรการ/No. :</span> {{ $purchaseOrder->order_number }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-left">
                <span class="label">โทร / Tel</span>
                <span style="margin-left: 100px;">Fax:</span>
            </div>
            <div class="info-right">
                <span class="label">เรื่องใบการจำระเงิน :</span> {{ $purchaseOrder->payment_terms ?? '-' }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-left">
                <span class="contact-label">ผู้ติดต่อ</span>
                {{ $purchaseOrder->supplier->name }}
                @if($purchaseOrder->supplier->address)
                <br>{{ $purchaseOrder->supplier->address }}
                @endif
                @if($purchaseOrder->supplier->phone)
                <br>โทร: {{ $purchaseOrder->supplier->phone }}
                @endif
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 45%;">รายการ</th>
                <th style="width: 12%;">หน่วยนับ</th>
                <th style="width: 12%;">จำนวน</th>
                <th style="width: 13%;">ราคาต่อหน่วย</th>
                <th style="width: 13%;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $item->product->name }}
                    @if($item->description)
                    <br><small>{{ $item->description }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item->product->unit->name }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
            @php
                $emptyRows = 10 - count($purchaseOrder->items);
            @endphp
            @for($i = 0; $i < $emptyRows; $i++)
            <tr>
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

    <!-- Summary -->
    <table class="summary-table">
        <tr>
            <td class="summary-label">จดสรายในวันที่</td>
            <td class="summary-label">หักกรณีผู้รับใบสั่งซื้อ</td>
            <td class="summary-label" rowspan="3" style="vertical-align: middle;">รวมราคาเงินค่า</td>
            <td class="summary-value" rowspan="3" style="vertical-align: middle; font-size: 18pt; font-weight: bold;">
                {{ number_format($purchaseOrder->subtotal, 2) }}
            </td>
        </tr>
        <tr>
            <td class="summary-label">วิธีจัดส่ง</td>
            <td class="summary-label">เรื่องใบการจัดส่ง</td>
        </tr>
        <tr>
            <td class="summary-label">จำนวนเงิน</td>
            <td class="summary-label">ภาษีมูลค่าเพิ่ม</td>
        </tr>
        <tr>
            <td class="summary-value">{{ number_format($purchaseOrder->subtotal, 2) }}</td>
            <td class="summary-value">{{ number_format($purchaseOrder->vat_amount, 2) }}</td>
            <td class="summary-label">จำนวนเงินรวมทั้งสิ้น</td>
            <td class="summary-value" style="font-size: 18pt; font-weight: bold;">
                {{ number_format($purchaseOrder->total_amount, 2) }}
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <div class="footer">
        <div class="signature-box">
            <div class="signature-line">
                ผู้สั่งซื้อ วันที่ ........./........./.........
            </div>
        </div>
        <div class="signature-box right">
            <div class="signature-line">
                ผู้อนุมัติ วันที่ ........./........./.........
            </div>
        </div>
    </div>

    @if($purchaseOrder->notes)
    <div style="margin-top: 20px; font-size: 12pt; border-top: 1px solid #ccc; padding-top: 10px;">
        <strong>หมายเหตุ:</strong> {{ $purchaseOrder->notes }}
    </div>
    @endif
</body>
</html>
