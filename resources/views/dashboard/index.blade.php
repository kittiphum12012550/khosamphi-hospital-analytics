@extends('layouts.app')

@section('title', 'แดชบอร์ดสถิติการรับบริการ')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i> แดชบอร์ดวิเคราะห์สถิติการเข้ารับบริการ
        </h3>
        <p class="text-muted small mb-0">
            แสดงสถิติภาพรวมและแนวโน้มการให้บริการผู้ป่วย (Visits) {{ config('hospital.name_th', 'โรงพยาบาลโกสัมพีนคร') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer me-1"></i> รายงานสถิติ
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('visit-statistics.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มข้อมูลสถิติ
            </a>
            <a href="{{ route('imports.index') }}" class="btn btn-primary btn-sm" style="background-color: #0aa2a3; border-color: #0aa2a3;">
                <i class="bi bi-cloud-arrow-up me-1"></i> นำเข้าไฟล์
            </a>
        @endif
    </div>
</div>

<!-- Advanced Filter Card -->
<div class="card filter-card p-3 mb-4">
    <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">วันที่เริ่มต้น</label>
                <input type="date" name="start_date" id="filterStartDate" class="form-control form-control-sm" value="{{ $filters['start_date'] }}">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">วันที่สิ้นสุด</label>
                <input type="date" name="end_date" id="filterEndDate" class="form-control form-control-sm" value="{{ $filters['end_date'] }}">
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">แผนก</label>
                <select name="department_id" id="filterDept" class="form-select form-select-sm">
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
                <select name="service_type_id" id="filterService" class="form-select form-select-sm">
                    <option value="">-- ทุกบริการ --</option>
                    @foreach($serviceTypes as $service)
                        <option value="{{ $service->id }}" {{ ($filters['service_type_id'] == $service->id) ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-3 col-md-1">
                <label class="form-label small fw-semibold text-secondary mb-1">ปี</label>
                <select name="year" id="filterYear" class="form-select form-select-sm">
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ ($filters['year'] == $yr) ? 'selected' : '' }}>
                            {{ $yr + 543 }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-3 col-md-1">
                <label class="form-label small fw-semibold text-secondary mb-1">เดือน</label>
                <select name="month" id="filterMonth" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    @php
                        $months = [
                            1=>'ม.ค.', 2=>'ก.พ.', 3=>'มี.ค.', 4=>'เม.ย.', 5=>'พ.ค.', 6=>'มิ.ย.',
                            7=>'ก.ค.', 8=>'ส.ค.', 9=>'ก.ย.', 10=>'ต.ค.', 11=>'พ.ย.', 12=>'ธ.ค.'
                        ];
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ ($filters['month'] == $num) ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel-fill me-1"></i> กรองข้อมูล
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Summary KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Total Visits -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-label">การรับบริการทั้งหมด (Visits)</div>
                    <div class="stats-value text-primary" id="kpiTotalVisits">
                        {{ number_format($summary['total_visits']) }}
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-calendar-check me-1"></i> ตามเงื่อนไขตัวกรอง
                    </div>
                </div>
                <div class="stats-icon-bg bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Today Visits -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-label">การรับบริการวันนี้ (Today)</div>
                    <div class="stats-value text-success" id="kpiTodayVisits">
                        {{ number_format($summary['today_visits']) }}
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-clock-history me-1"></i> {{ \Carbon\Carbon::today()->locale('th')->isoFormat('D MMM YYYY') }}
                    </div>
                </div>
                <div class="stats-icon-bg bg-success bg-opacity-10 text-success">
                    <i class="bi bi-calendar-heart"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- This Month Visits -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-label">การรับบริการเดือนนี้ (This Month)</div>
                    <div class="stats-value text-info" id="kpiMonthVisits">
                        {{ number_format($summary['this_month_visits']) }}
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-graph-up me-1"></i> เฉลี่ย {{ number_format($summary['avg_daily_visits'], 1) }} ครั้ง/วัน
                    </div>
                </div>
                <div class="stats-icon-bg bg-info bg-opacity-10 text-info">
                    <i class="bi bi-activity"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Department -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-label">แผนกที่มีผู้รับบริการสูงสุด</div>
                    <div class="stats-value text-warning fs-5 text-truncate" style="max-width: 170px;" id="kpiTopDeptName" title="{{ $summary['top_department_name'] }}">
                        {{ $summary['top_department_name'] }}
                    </div>
                    <div class="text-muted small mt-1">
                        <span class="fw-bold text-dark" id="kpiTopDeptCount">{{ number_format($summary['top_department_count']) }}</span> ครั้ง
                    </div>
                </div>
                <div class="stats-icon-bg bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-trophy-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1: Daily Trend & Hourly Peak Visits -->
<div class="row g-3 mb-4">
    <!-- Daily Trend Line Chart -->
    <div class="col-12 col-lg-8">
        <div class="card chart-card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-graph-up text-primary"></i>
                    <span>แนวโน้มการเข้ารับบริการรายวัน (Daily Trend)</span>
                </div>
                <span class="badge bg-light text-secondary border">จำนวนครั้ง (Visits)</span>
            </div>
            <div class="card-body">
                <div style="height: 310px;">
                    <canvas id="dailyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Distribution Bar Chart -->
    <div class="col-12 col-lg-4">
        <div class="card chart-card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock text-danger"></i>
                    <span>ช่วงเวลาหนาแน่น (Peak Hours 0-23 น.)</span>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 310px;">
                    <canvas id="hourlyDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2: Department Comparison & Service Type Distribution -->
<div class="row g-3 mb-4">
    <!-- Department Breakdown Bar Chart -->
    <div class="col-12 col-lg-7">
        <div class="card chart-card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-success"></i>
                    <span>เปรียบเทียบจำนวนครั้งแยกตามแผนก (By Department)</span>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 290px;">
                    <canvas id="departmentBreakdownChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Type Distribution Doughnut Chart -->
    <div class="col-12 col-lg-5">
        <div class="card chart-card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart-fill text-info"></i>
                    <span>สัดส่วนตามประเภทบริการ (Service Types)</span>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 290px;">
                    <canvas id="serviceDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Accounts by Department -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    <span>จำนวนบัญชีผู้ใช้แยกตามแผนก (Users by Department)</span>
                </div>
            </div>
            <div class="card-body">
                @if(count($usersByDepartment['labels']) > 0)
                    <div style="height: 290px;">
                        <canvas id="usersByDepartmentChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">ยังไม่มีบัญชีผู้ใช้งานในระบบ</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 3: Monthly Comparison -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar3-range text-primary"></i>
                    <span>เปรียบเทียบสถิติการรับบริการรายเดือน ปีปัจจุบัน vs ปีก่อนหน้า (Monthly Comparison)</span>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 280px;">
                    <canvas id="monthlyComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Visits Table Card -->
<div class="card chart-card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-table text-secondary"></i>
            <span>รายการบันทึกสถิติล่าสุด (Recent Visit Logs)</span>
        </div>
        <a href="{{ route('visit-statistics.index') }}" class="btn btn-sm btn-link text-decoration-none">
            ดูทั้งหมด <i class="bi bi-chevron-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table hospital-table table-hover mb-0">
                <thead>
                    <tr>
                        <th>วันที่ให้บริการ</th>
                        <th>ช่วงเวลา</th>
                        <th>แผนก</th>
                        <th>ประเภทบริการ</th>
                        <th class="text-end">จำนวนครั้ง (Visits)</th>
                        <th>แหล่งข้อมูล</th>
                        <th>ผู้บันทึก</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentVisits as $visit)
                        <tr>
                            <td>{{ $visit->service_date->locale('th')->isoFormat('D MMM YYYY') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $visit->formatted_hour }}</span></td>
                            <td class="fw-semibold text-primary">{{ $visit->department?->code }} - {{ $visit->department?->name_th }}</td>
                            <td>{{ $visit->serviceType?->name }}</td>
                            <td class="text-end fw-bold fs-6 text-dark">{{ number_format($visit->visit_count) }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $visit->source_label }}</span></td>
                            <td class="small text-muted">{{ $visit->creator?->name ?? 'ระบบ' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                ไม่พบข้อมูลสถิติการเข้ารับบริการตามเงื่อนไขที่เลือก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Initial Render with server data
        const dailyTrend = @json($dailyTrend);
        HospitalCharts.initDailyTrend('dailyTrendChart', dailyTrend.labels, dailyTrend.data);

        const deptBreakdown = @json($departmentBreakdown);
        HospitalCharts.initDepartmentBar('departmentBreakdownChart', deptBreakdown.labels, deptBreakdown.data);

        const usersByDepartment = @json($usersByDepartment);
        HospitalCharts.initUsersByDepartment('usersByDepartmentChart', usersByDepartment.labels, usersByDepartment.data);

        const serviceDist = @json($serviceDistribution);
        HospitalCharts.initServiceTypeDoughnut('serviceDistributionChart', serviceDist.labels, serviceDist.data);

        const monthlyComp = @json($monthlyComparison);
        HospitalCharts.initMonthlyComparison(
            'monthlyComparisonChart',
            monthlyComp.labels,
            monthlyComp.current_year,
            monthlyComp.current_data,
            monthlyComp.previous_year,
            monthlyComp.previous_data
        );

        const hourlyDist = @json($hourlyDistribution);
        HospitalCharts.initHourlyDistribution('hourlyDistributionChart', hourlyDist.labels, hourlyDist.data);
    });
</script>
@endpush
