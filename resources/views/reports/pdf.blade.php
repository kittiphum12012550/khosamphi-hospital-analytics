<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานสถิติการรับบริการ - {{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Sarabun', 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0aa2a3;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
            color: #0f172a;
        }
        .header p {
            margin: 2px 0;
            color: #475569;
            font-size: 13px;
        }
        .meta-box {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #334155;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 12px;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-align: left;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .grand-total {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 13px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
            width: 250px;
        }
        .sig-line {
            margin-top: 50px;
            border-top: 1px dashed #64748b;
            padding-top: 5px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #0aa2a3; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            พิมพ์รายงาน (Print)
        </button>
    </div>

    <div class="header">
        <h2>{{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}</h2>
        <p>รายงานสถิติการเข้ารับบริการของผู้ป่วย (Hospital Visit Statistics Analytics)</p>
        <p>กลุ่มภารกิจด้านพัฒนาระบบบริการและสารสนเทศทางการแพทย์ จ.กำแพงเพชร</p>
    </div>

    <div class="meta-box">
        <div><strong>ประเภทรายงาน:</strong> {{ strtoupper($reportData['report_type']) }} REPORT</div>
        <div class="text-right"><strong>วันที่จัดทำ:</strong> {{ $reportData['generated_at'] }}</div>
    </div>

    <table>
        <thead>
            @if($reportData['report_type'] == 'monthly')
                <tr>
                    <th>ช่วงเวลา / เดือน</th>
                    <th class="text-center">จำนวนวันให้บริการ</th>
                    <th class="text-center">จำนวนแผนก</th>
                    <th class="text-right">จำนวนครั้งรวม (Visits)</th>
                    <th class="text-right">เฉลี่ยต่อวัน</th>
                </tr>
            @elseif($reportData['report_type'] == 'yearly')
                <tr>
                    <th>ปี</th>
                    <th class="text-center">จำนวนวันให้บริการ</th>
                    <th class="text-right">จำนวนครั้งรวม (Visits)</th>
                    <th class="text-right">เฉลี่ยต่อวัน</th>
                </tr>
            @elseif($reportData['report_type'] == 'department')
                <tr>
                    <th style="width: 100px;">รหัสแผนก</th>
                    <th>ชื่อแผนก</th>
                    <th class="text-center">จำนวนวันที่มีผู้รับบริการ</th>
                    <th class="text-right">จำนวนครั้งรวม (Visits)</th>
                </tr>
            @elseif($reportData['report_type'] == 'service_type')
                <tr>
                    <th style="width: 120px;">รหัสบริการ</th>
                    <th>ชื่อประเภทบริการ</th>
                    <th>หมวดหมู่</th>
                    <th class="text-right">จำนวนครั้งรวม (Visits)</th>
                </tr>
            @else
                <tr>
                    <th>วันที่ให้บริการ</th>
                    <th class="text-center">จำนวนแผนก</th>
                    <th class="text-center">จำนวนประเภทบริการ</th>
                    <th class="text-right">จำนวนครั้งรวม (Visits)</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($reportData['items'] as $item)
                @if($reportData['report_type'] == 'monthly')
                    <tr>
                        <td>{{ $item->period_label }}</td>
                        <td class="text-center">{{ $item->active_days }} วัน</td>
                        <td class="text-center">{{ $item->dept_count }}</td>
                        <td class="text-right"><strong>{{ number_format($item->total_visits) }}</strong></td>
                        <td class="text-right">{{ number_format($item->avg_daily, 1) }}</td>
                    </tr>
                @elseif($reportData['report_type'] == 'yearly')
                    <tr>
                        <td>{{ $item->period_label }}</td>
                        <td class="text-center">{{ $item->active_days }} วัน</td>
                        <td class="text-right"><strong>{{ number_format($item->total_visits) }}</strong></td>
                        <td class="text-right">{{ number_format($item->avg_daily, 1) }}</td>
                    </tr>
                @elseif($reportData['report_type'] == 'department')
                    <tr>
                        <td>{{ $item->dept_code }}</td>
                        <td>{{ $item->dept_name }}</td>
                        <td class="text-center">{{ $item->active_days }} วัน</td>
                        <td class="text-right"><strong>{{ number_format($item->total_visits) }}</strong></td>
                    </tr>
                @elseif($reportData['report_type'] == 'service_type')
                    <tr>
                        <td>{{ $item->st_code }}</td>
                        <td>{{ $item->st_name }}</td>
                        <td>{{ $item->st_category ?? '-' }}</td>
                        <td class="text-right"><strong>{{ number_format($item->total_visits) }}</strong></td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $item->period_label }}</td>
                        <td class="text-center">{{ $item->dept_count }}</td>
                        <td class="text-center">{{ $item->service_count }}</td>
                        <td class="text-right"><strong>{{ number_format($item->total_visits) }}</strong></td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="{{ in_array($reportData['report_type'], ['department', 'daily']) ? 3 : 2 }}" class="text-right">
                    ยอดรวมทั้งสิ้น (Grand Total):
                </td>
                <td class="text-right">
                    {{ number_format($reportData['grand_total']) }} ครั้ง
                </td>
                @if(in_array($reportData['report_type'], ['monthly', 'yearly']))
                    <td></td>
                @endif
            </tr>
        </tfoot>
    </table>

    <div style="font-size: 11px; color: #64748b; margin-top: 10px;">
        * หมายเหตุ: รายงานฉบับนี้นับจากจำนวนครั้งของการเข้ารับบริการ (Visits) ไม่ใช่จำนวนผู้ป่วยที่ไม่ซ้ำกัน และสร้างจากข้อมูลสังเคราะห์เพื่อการบริหารจัดการ
    </div>

    <table style="border: none; margin-top: 60px; width: 100%;">
        <tr style="border: none;">
            <td style="border: none; text-align: center; width: 50%;">
                ลงชื่อ ..............................................................<br>
                ( .............................................................. )<br>
                เจ้าหน้าที่เวชระเบียนและสถิติ
            </td>
            <td style="border: none; text-align: center; width: 50%;">
                ลงชื่อ ..............................................................<br>
                ( .............................................................. )<br>
                ผู้อำนวยการ{{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}
            </td>
        </tr>
    </table>
</body>
</html>

