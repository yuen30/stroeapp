<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบรับสินค้า - {{ $goodsReceipt->receipt_number }}</title>
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
        .document-info table {
            width: 100%;
        }
        .document-info td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .items-table td.number {
            text-align: center;
        }
        .items-table td.right {
            text-align: right;
        }
        .notes {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
        }
        .signature {
            margin-top: 40px;
        }
        .signature table {
            width: 100%;
        }
        .signature td {
            text-align: center;
            padding: 10px;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 200px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ใบรับสินค้า</h1>
        <div>(GOODS RECEIPT)</div>
    </div>

    <div class="company-info">
        <strong>{{ $goodsReceipt->company->name }}</strong><br>
        @if($goodsReceipt->branch)
            สาขา: {{ $goodsReceipt->branch->name }}<br>
        @endif
    </div>

    <div class="document-info">
        <table>
            <tr>
                <td width="50%">
                    <strong>เลขที่ใบรับสินค้า:</strong> {{ $goodsReceipt->receipt_number }}<br>
                    <strong>วันที่รับสินค้า:</strong> {{ $goodsReceipt->document_date->format('d/m/Y') }}<br>
                    @if($goodsReceipt->supplier_delivery_no)
                        <strong>เลขที่ใบส่งของผู้จำหน่าย:</strong> {{ $goodsReceipt->supplier_delivery_no }}<br>
                    @endif
                </td>
                <td width="50%">
                    <strong>ใบสั่งซื้ออ้างอิง:</strong> {{ $goodsReceipt->purchaseOrder->order_number ?? '-' }}<br>
                    <strong>ผู้จำหน่าย:</strong> {{ $goodsReceipt->supplier->name }}<br>
                    <strong>ผู้สร้าง:</strong> {{ $goodsReceipt->creator->name }}<br>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">ลำดับ</th>
                <th width="35%">รายการสินค้า</th>
                <th width="30%">รายละเอียด</th>
                <th width="15%">จำนวนที่รับ</th>
                <th width="15%">หน่วย</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goodsReceipt->items as $index => $item)
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->description ?? '-' }}</td>
                <td class="right">{{ number_format($item->quantity) }}</td>
                <td class="number">{{ $item->product->unit->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($goodsReceipt->notes)
    <div class="notes">
        <strong>หมายเหตุ:</strong><br>
        {{ $goodsReceipt->notes }}
    </div>
    @endif

    <div class="signature">
        <table>
            <tr>
                <td width="33%">
                    ผู้รับสินค้า<br><br><br>
                    <span class="dotted-line"></span><br>
                    วันที่ <span class="dotted-line"></span>
                </td>
                <td width="33%">
                    ผู้ตรวจสอบ<br><br><br>
                    <span class="dotted-line"></span><br>
                    วันที่ <span class="dotted-line"></span>
                </td>
                <td width="33%">
                    ผู้อนุมัติ<br><br><br>
                    <span class="dotted-line"></span><br>
                    วันที่ <span class="dotted-line"></span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
