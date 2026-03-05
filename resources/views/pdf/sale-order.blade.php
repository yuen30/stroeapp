<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบสั่งขาย {{ $saleOrder->invoice_number }}</title>
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
            margin: 20px;
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
        .document-info {
            margin-bottom: 20px;
        }
        .customer-info {
            margin-bottom: 20px;
            border: 1px solid #000;
            padding: 10px;
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
        .summary {
            float: right;
            width: 40%;
        }
        .summary table {
            margin-bottom: 0;
        }
        .summary table td {
            border: none;
            padding: 5px;
        }
        .total-row {
            font-weight: bold;
            font-size: 18pt;
        }
        .notes {
            clear: both;
            margin-top: 20px;
            padding-top: 20px;
        }
        .signature {
            margin-top: 40px;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ใบสั่งขาย / SALE ORDER</h1>
    </div>

    <div class="company-info">
        <strong>{{ $saleOrder->company->name }}</strong><br>
        สาขา: {{ $saleOrder->branch->name }}<br>
        @if($saleOrder->company->address)
            {{ $saleOrder->company->address }}<br>
        @endif
        @if($saleOrder->company->tel)
            โทร: {{ $saleOrder->company->tel }}
        @endif
    </div>

    <div class="document-info">
        <table style="border: none;">
            <tr>
                <td style="border: none; width: 50%;"><strong>เลขที่เอกสาร:</strong> {{ $saleOrder->invoice_number }}</td>
                <td style="border: none; width: 50%;"><strong>วันที่:</strong> {{ $saleOrder->order_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>พนักงานขาย:</strong> {{ $saleOrder->salesman->name ?? '-' }}</td>
                <td style="border: none;"><strong>วันครบกำหนด:</strong> {{ $saleOrder->due_date?->format('d/m/Y') ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="customer-info">
        <strong>ลูกค้า:</strong> {{ $saleOrder->customer->name }}<br>
        @if($saleOrder->customer->address_0)
            <strong>ที่อยู่:</strong> {{ $saleOrder->customer->address_0 }}<br>
        @endif
        @if($saleOrder->customer->tel)
            <strong>โทร:</strong> {{ $saleOrder->customer->tel }}
        @endif
        @if($saleOrder->customer->tax_id)
            <strong>เลขประจำตัวผู้เสียภาษี:</strong> {{ $saleOrder->customer->tax_id }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 40%;">รายการสินค้า</th>
                <th style="width: 10%;">จำนวน</th>
                <th style="width: 10%;">หน่วย</th>
                <th style="width: 15%;">ราคา/หน่วย</th>
                <th style="width: 10%;">ส่วนลด</th>
                <th style="width: 15%;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @foreach($saleOrder->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $item->product->name }}
                    @if($item->description)
                        <br><small>{{ $item->description }}</small>
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->quantity) }}</td>
                <td class="text-center">{{ $item->product->unit->name }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>มูลค่าสินค้า:</td>
                <td class="text-right">{{ number_format($saleOrder->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>ส่วนลดท้ายบิล:</td>
                <td class="text-right">{{ number_format($saleOrder->discount_amount, 2) }}</td>
            </tr>
            <tr>
                <td>ภาษีมูลค่าเพิ่ม 7%:</td>
                <td class="text-right">{{ number_format($saleOrder->vat_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>ยอดสุทธิ:</td>
                <td class="text-right">{{ number_format($saleOrder->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($saleOrder->notes)
    <div class="notes">
        <strong>หมายเหตุ:</strong><br>
        {{ $saleOrder->notes }}
    </div>
    @endif

    <div class="signature">
        <div class="signature-box">
            ผู้สั่งซื้อ: _______________________<br>
            วันที่: _______________________
        </div>
        <div class="signature-box" style="float: right;">
            ผู้อนุมัติ: _______________________<br>
            วันที่: _______________________
        </div>
    </div>
</body>
</html>
