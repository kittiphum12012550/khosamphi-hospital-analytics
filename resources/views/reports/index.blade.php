@extends('layouts.app')

@section('title', 'รายงานสถิติการรับบริการ')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i> ระบบรายงานสถิติ (Analytics Reports)
        </h4>
        <p class="text-muted small mb-0">สร้างและส่งออกรายงานสถิติการเข้ารับบริการตามเงื่อนไขที่กำหนด {{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.excel', request()->all()) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </a>
        <a href="{{ route('reports.print', request()->all()) }}" class="btn btn-outline-dark btn-sm" target="_blank">
            <i class="bi bi-printer me-1"></i> พิมพ์รายงาน
        </a>
    </div>
</div>

<!-- Report Filter Box -->
<div class="card filter-card p-3 mb-4">
    <form method="GET" action="{{ route('reports.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">ประเภทรายงาน (Report Type)</label>
                <select name="report_type" class="form-select form-select-sm fw-bold text-primary">
                    <option value="daily" {{ ($reportData['report_type'] == 'daily') ? 'selected' : '' }}>รายงานรายวัน (Daily)</option>
                    <option value="monthly" {{ ($reportData['report_type'] == 'monthly') ? 'selected' : '' }}>รายงานรายเดือน (Monthly)</option>
                    <option value="yearly" {{ ($reportData['report_type'] == 'yearly') ? 'selected' : '' }}>รายงานรายปี (Yearly)</option>
                    <option value="department" {{ ($reportData['report_type'] == 'department') ? 'selected' : '' }}>รายงานแยกตามแผนก (By Department)</option>
                    <option value="service_type" {{ ($reportData['report_type'] == 'service_type') ? 'selected' : '' }}>รายงานแยกตามประเภทบริการ (By Service)</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">วันที่เริ่มต้น</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] }}">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">วันที่สิ้นสุด</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] }}">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">แผนก</label>
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">-- ทุกแผนก --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ ($filters['department_id'] == $dept->id) ? 'selected' : '' }}>
                            {{ $dept->code }} - {{ $dept->name_th }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">ประเภทบริการ</label>
                <select name="service_type_id" class="form-select form-select-sm">
                    <option value="">-- ทุกประเภทบริการ --</option>
                    @foreach($serviceTypes as $service)
                        <option value="{{ $service->id }}" {{ ($filters['service_type_id'] == $service->id) ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Report Header Summary -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-8">
        <div class="card chart-card p-3 h-100 d-flex justify-content-center">
            <h5 class="fw-bold mb-1 text-dark">
                {{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}
            </h5>
            <div class="text-muted small">
                รายงาน: <strong class="text-primary">{{ strtoupper($reportData['report_type']) }} REPORT</strong> | สร้างเมื่อ: {{ $reportData['generated_at'] }} โดย {{ auth()->user()->name }}
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card stats-card p-3 h-100 bg-primary bg-opacity-10 border-primary border-opacity-25">
            <div class="stats-label text-primary fw-bold">ยอดการรับบริการรวมทั้งสิ้น (Grand Total)</div>
            <div class="stats-value text-primary">{{ number_format($reportData['grand_total']) }} ครั้ง</div>
            <small class="text-secondary">Visits สะสมตามเงื่อนไขที่เลือก</small>
        </div>
    </div>
</div>

<!-- Report Table -->
<div class="card chart-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table hospital-table table-hover align-middle mb-0">
                <thead>
                    @if($reportData['report_type'] == 'monthly')
                        <tr>
                            <th>ช่วงเวลา / เดือน</th>
                            <th class="text-center">จำนวนวันให้บริการ</th>
                            <th class="text-center">จำนวนแผนกที่ให้บริการ</th>
                            <th class="text-end">จำนวนครั้งรวม (Visits)</th>
                            <th class="text-end">เฉลี่ยต่อวัน</th>
                        </tr>
                    @elseif($reportData['report_type'] == 'yearly')
                        <tr>
                            <th>ปี พ.ศ. (ค.ศ.)</th>
                            <th class="text-center">จำนวนวันให้บริการ</th>
                            <th class="text-end">จำนวนครั้งรวม (Visits)</th>
                            <th class="text-end">เฉลี่ยต่อวัน</th>
                        </tr>
                    @elseif($reportData['report_type'] == 'department')
                        <tr>
                            <th style="width: 140px;">รหัสแผนก</th>
                            <th>ชื่อแผนก</th>
                            <th class="text-center">จำนวนวันที่มีผู้รับบริการ</th>
                            <th class="text-end">จำนวนครั้งรวม (Visits)</th>
                        </tr>
                    @elseif($reportData['report_type'] == 'service_type')
                        <tr>
                            <th style="width: 160px;">รหัสบริการ</th>
                            <th>ชื่อประเภทบริการ</th>
                            <th>หมวดหมู่</th>
                            <th class="text-end">จำนวนครั้งรวม (Visits)</th>
                        </tr>
                    @else
                        <tr>
                            <th>วันที่ให้บริการ</th>
                            <th class="text-center">จำนวนแผนก</th>
                            <th class="text-center">จำนวนบริการ</th>
                            <th class="text-end">จำนวนครั้งรวม (Visits)</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse($reportData['items'] as $item)
                        @if($reportData['report_type'] == 'monthly')
                            <tr>
                                <td class="fw-semibold">{{ $item->period_label }}</td>
                                <td class="text-center">{{ number_format($item->active_days) }} วัน</td>
                                <td class="text-center">{{ number_format($item->dept_count) }} แผนก</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($item->total_visits) }}</td>
                                <td class="text-end text-muted">{{ number_format($item->avg_daily, 1) }}</td>
                            </tr>
                        @elseif($reportData['report_type'] == 'yearly')
                            <tr>
                                <td class="fw-semibold">{{ $item->period_label }}</td>
                                <td class="text-center">{{ number_format($item->active_days) }} วัน</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($item->total_visits) }}</td>
                                <td class="text-end text-muted">{{ number_format($item->avg_daily, 1) }}</td>
                            </tr>
                        @elseif($reportData['report_type'] == 'department')
                            <tr>
                                <td><span class="badge bg-light text-primary border fw-bold">{{ $item->dept_code }}</span></td>
                                <td class="fw-semibold">{{ $item->dept_name }}</td>
                                <td class="text-center">{{ number_format($item->active_days) }} วัน</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($item->total_visits) }}</td>
                            </tr>
                        @elseif($reportData['report_type'] == 'service_type')
                            <tr>
                                <td><span class="badge bg-light text-info border fw-bold">{{ $item->st_code }}</span></td>
                                <td class="fw-semibold">{{ $item->st_name }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $item->st_category ?? '-' }}</span></td>
                                <td class="text-end fw-bold text-primary">{{ number_format($item->total_visits) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="fw-semibold">{{ $item->period_label }}</td>
                                <td class="text-center">{{ number_format($item->dept_count) }} แผนก</td>
                                <td class="text-center">{{ number_format($item->service_count) }} บริการ</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($item->total_visits) }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                ไม่พบข้อมูลรายงานตามเงื่อนไขที่เลือก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold fs-6">
                        <td colspan="{{ in_array($reportData['report_type'], ['department', 'daily']) ? 3 : 2 }}" class="text-end">
                            ยอดรวมทั้งสิ้น (Grand Total):
                        </td>
                        <td class="text-end text-success fs-5">
                            {{ number_format($reportData['grand_total']) }} ครั้ง
                        </td>
                        @if(in_array($reportData['report_type'], ['monthly', 'yearly']))
                            <td></td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

